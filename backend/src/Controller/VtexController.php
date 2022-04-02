<?php
namespace Maisenvios\Middleware\Controller;

use Curl\Curl;
use Maisenvios\Middleware\Model\Shop;
use Maisenvios\Middleware\Model\Order;
use Maisenvios\Middleware\Model\Shipping;
use Maisenvios\Middleware\Model\SgpPrePost;
use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Repository\OrderRepository;
use Maisenvios\Middleware\Repository\ShippingRepository;
use Maisenvios\Middleware\Client\Sgp;
class VtexController {
    
    private $shop;
    private $orderRepo;
    private $sgpLogRepo;
    private $vtexClient;
    private $sgpClient;
    private $shippingRepo;
    private $shippings = [];

    public function __construct(Shop $shop)
    {
        $this->sgpLogRepo = new SgpLogRepository();
        if (strcmp($shop->getEcommerce(), 'VTEX') !== 0) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus("Algo deu errado, uma loja não VTEX está tentando acessar seus controladores.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Trying to use an non-VTEX shop on VtexController", 1);            
        }
        
        if ($shop->getAccount() !== null && $shop->getCustomerKey() !== null && $shop->getCustomerToken() !== null) {
            $this->vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
        } else {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus("As informações de Account, CustomerKey, CustomerToken não estão disponiveis e são necessárias.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Shop must have account, key and token", 1);            
        }

        $this->shop = $shop;
        $this->orderRepo = new OrderRepository();
        $this->shippingRepo = new ShippingRepository();
        $this->sgpClient = new Sgp($this->shop->getSysKey());
    }
    /**
     * This function is responsible to get all the data from orders when VTEX calls from Order Feed
     * 
     * @return void
     */
    public function processFeed() {
        //array with vtex order handlers 
        $toCommit = [];
        //list of orders received from VTEX
        $orders = $this->vtexClient->getFeed();
        foreach ($orders as $order) {
            //get all data needed from vtex
            $fullOrder = $this->vtexClient->getOrder($order->orderId);
            //get the shipping used from vtex order
            $orderShipping = $fullOrder->shippingData->logisticsInfo[0]->deliveryCompany;
            //check if it is as valid shipping
            if ( $this->hasValidShipping( $orderShipping ) ) {
                //grap the order from db 
                $orderInDB = $this->orderRepo->findOneBy(['orderId' => $order->orderId, 'storeId' => $this->shop->getId() ]);
                //if it don't exist create it
                if (!$orderInDB) {
                    //grap all data of the shipping
                    $shipping = $this->shippingRepo->findOneBy(['idShop' => $this->shop->getId(), 'name' => $orderShipping]);
                    //generate an obj to persist in db
                    $orderObj = (new Order())->createFromVtex($fullOrder, $this->shop->getId(), $shipping[0]->getCorreios());
                    //create it
                    $created = $this->orderRepo->create($orderObj);
                    if ($created) {
                        array_push($toCommit, $order->handle);
                        $log = new SgpLog();
                        $log->setShopId( $this->shop->getId() );
                        $log->setOrderId($order->orderId);
                        $log->setStatus("Pedido recebido do Feed");
                        $this->sgpLogRepo->create($log);
                    } else {
                        $log = new SgpLog();
                        $log->setShopId( $this->shop->getId() );
                        $log->setOrderId($order->orderId);
                        $log->setStatus("Falha ao gravar o pedido no banco de dados");
                        $this->sgpLogRepo->create($log);
                    }
                    //if it exists then update it
                } else {
                    //grap all data of the shipping
                    $shipping = $this->shippingRepo->findOneBy(['idShop' => $this->shop->getId(), 'name' => $orderShipping]);
                    //initiate the sgp client
                    $sgpClient = new Sgp($this->shop->getSysKey());
                    $args = [ $fullOrder->packageAttachment->packages[0]->invoiceNumber ];
                    //search into sgp for an existing post
                    $result = $sgpClient->getByInvoiceNumbers( $args );
                    //if it exists then update the db
                    if ($result->retorno->status_processamento == 1) {
                        $updateOrderArgs = [
                            'integrated' => 1,
                            'invoiceNumber' => isset($fullOrder->packageAttachment->packages[0]->invoiceNumber) ? $fullOrder->packageAttachment->packages[0]->invoiceNumber : null,
                            'tracking' => isset($result->retorno->objetos[0]->objeto) ? $result->retorno->objetos[0]->objeto : null
                        ];
                        $updated = $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                        if ($updated) {
                            array_push($toCommit, $order->handle);
                            $log = new SgpLog();
                            $log->setShopId( $this->shop->getId() );
                            $log->setOrderId($order->orderId);
                            $log->setStatus("Pedido atualizado através do Feed");
                            $this->sgpLogRepo->create($log);
                        } else {
                            $log = new SgpLog();
                            $log->setShopId( $this->shop->getId() );
                            $log->setOrderId($order->orderId);
                            $log->setStatus("Falha ao gravar o pedido no banco de dados");
                            $this->sgpLogRepo->create($log);
                        }
                    }
                    array_push($toCommit, $order->handle);
                }
            } else {
                array_push($toCommit, $order->handle);
            }
        }

        if (! empty($toCommit)) {
            $result = $this->vtexClient->commit($toCommit);
            $log = new SgpLog();
            $log->setShopId( $this->shop->getId() );
            $log->setStatus("Itens do Feed commitados");
            $log->setObjetos(json_encode($result));
            $this->sgpLogRepo->create($log);
        }

        return;
    }

    /**
     * Check if the passed shipping exists among the Shop's Shippings
     */
    public function hasValidShipping(string $orderShipping) {
        if (empty($this->shippings)) {
            $shippings = $this->shippingRepo->findAll(['idShop' => $this->shop->getId(), 'active' => 1]);
            foreach ($shippings as $shipping) {
                array_push($this->shippings, $shipping->getName());
            }
        }
        return in_array($orderShipping, $this->shippings);
    }

    /**
     * Creates the prepost in SGPs API
     * @param array[Order] $orders
     */
    public function createSgpPrePost($orders) {
        $shippings = $this->shippingRepo->findAll(['idShop' => $this->shop->getId(), 'active' => 1]);
        foreach ($orders as $order) {
            $isInvalidShipping = true;
            foreach ($shippings as $shipping) {
                $fullOrder = $this->vtexClient->getOrder($order->getOrderId());
                if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                    $isInvalidShipping = false;
                    $sgpObj = SgpPrePost::createFromVtex($fullOrder, $shipping);
                    $json = SgpPrePost::generatePayload([$sgpObj]);
                    $result = $this->sgpClient->createPrePost($json);
                    if ($result->retorno->status_processamento == 1) {
                        $updateOrderArgs = [
                            'service' => $shipping->getCorreios(),
                            'integrated' => 1,
                            'invoiceNumber' => isset($fullOrder->packageAttachment->packages[0]->invoiceNumber) ? $fullOrder->packageAttachment->packages[0]->invoiceNumber : null,
                            'tracking' => isset($result->retorno->objetos[0]->objeto) ? $result->retorno->objetos[0]->objeto : null
                        ];
                        $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                        $log = SgpLog::createFromSgpResponse($this->shop->getId(), $order->getOrderId(), $result);
                        $this->sgpLogRepo->create($log);
                    }
                }                            
            }
            if ($isInvalidShipping) {
                $isInvalidShipping = true;
                $log = new SgpLog();
                $log->setOrderId($order->getOrderId());
                $log->setShopId( $this->shop->getId() );
                $log->setStatus("Transportadora inválida.");
                $log->setObjetos(json_encode($fullOrder));
                $this->sgpLogRepo->create($log);
                $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 'vtex_invalid_shipping_type']);
            }
        }
        return;
    }

    /**
     * Check for existing preposts in SGP and updates the Order object
     * @param array[Order] $orders
     */
    public function checkForExistingSgpPrePost($orders) {
        $shippings = $this->shippingRepo->findAll(['idShop' => $this->shop->getId(), 'active' => 1]);
        foreach ($orders as $order) {
            $isInvalidShipping = true;
            foreach ($shippings as $shipping) {
                $fullOrder = $this->vtexClient->getOrder($order->getOrderId());
                if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                    $isInvalidShipping = false;
                    if (isset( $fullOrder->packageAttachment->packages[0]->invoiceNumber ) && ! is_null( $fullOrder->packageAttachment->packages[0]->invoiceNumber )) {
                        $args = [ $fullOrder->packageAttachment->packages[0]->invoiceNumber ];
                        $result = $this->sgpClient->getByInvoiceNumbers( $args );
                        if ($result->retorno->status_processamento == 1) {
                            $updateOrderArgs = [
                                'integrated' => 1,
                                'invoiceNumber' => isset($fullOrder->packageAttachment->packages[0]->invoiceNumber) ? $fullOrder->packageAttachment->packages[0]->invoiceNumber : null,
                                'tracking' => isset($result->retorno->objetos[0]->objeto) ? $result->retorno->objetos[0]->objeto : null
                            ];
                            $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                            $log = SgpLog::createFromSgpResponse($this->shop->getId(), $order->getOrderId(), $result);
                            $this->sgpLogRepo->create($log);
                        }
                    }
                }                            
            }
            if ($isInvalidShipping) {
                $isInvalidShipping = true;
                $log = new SgpLog();
                $log->setOrderId($order->getOrderId());
                $log->setShopId( $this->shop->getId() );
                $log->setStatus("Transportadora inválida.");
                $log->setObjetos(json_encode($fullOrder));
                $this->sgpLogRepo->create($log);
                $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 'vtex_invalid_shipping_type']);
            }
        }
        return;
    }

    /**
     * Send the tracking information back to VTEX
     * @param array[Order] $orders
     */
    public function updateVtexTrackingInformation($orders) {
        foreach ($orders as $order) {
            if($order->getTracking() !== null && $order->getInvoiceNumber() !== null) {
                //Retrieve the full order from VTEX
                $fullOrder = $this->vtexClient->getOrder($order->getOrderId());

                foreach ($fullOrder->packageAttachment->packages as $package) {
                    if ($package->invoiceNumber == $order->getInvoiceNumber()) {
                        // Prepare payload to create the event
                        $updateOrderTrackingArgs = [
                            "orderId" => $order->getOrderId(),
                            "invoiceNumber" => $order->getInvoiceNumber(),
                            "isDelivered" => false,
                            "events" => [
                                "description" => "Entregue a transportadora",
                                "date" => date('Y-m-d')
                            ]
                        ];
                        
                        //Prepares the payload to actually send the tracking code
                        $sendInvoiceInformationItems = [];
                        foreach ($package->items as $item) {
                            array_push( $sendInvoiceInformationItems, [
                                    "id" => $fullOrder->items[ $item->itemIndex ]->id,
                                    "price" => $item->price,
                                    "quantity" => $item->quantity
                                ]
                            );
                        }
                        
                        $sendInvoiceInformationArgs = [
                            "type" => "Output",
                            "trackingNumber" => $order->getTracking(),
                            "issuanceDate" => $package->issuanceDate,
                            "invoiceNumber" => $package->invoiceNumber,
                            "invoiceValue" => $package->invoiceValue,
                            "items" => $sendInvoiceInformationItems
                        ];
                        
                        //send both the event and tracking code
                        $sendInvoiceInformationResult = $this->vtexClient->sendInvoiceInformation($order->getOrderId(), $sendInvoiceInformationArgs);
                        $updateOrderTrackingResult = $this->vtexClient->updateOrderTracking( $order->getOrderId(), $order->getInvoiceNumber(), $updateOrderTrackingArgs);
                        if (isset($updateOrderTrackingResult->receipt) && isset($sendInvoiceInformationResult->receipt)) {
                            $updateOrderArgs = [
                                'integrated' => 'vtex_tracking_update',
                            ];
                            $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                            $log = new SgpLog();
                            $log->setShopId( $this->shop->getId() );
                            $log->setStatus( "Código de rastreio enviado" );
                            $log->setObjetos( json_encode( [$updateOrderTrackingResult, $sendInvoiceInformationResult] ) );
                            $this->sgpLogRepo->create($log);
                        }
                    }
                }
            }
        }
        return;
    }

    /**
     * Calls custom endpoint sending the Order information
     */
    public function callEndpoint(Order $order, string $endpoint_to_call) {
        $client = new Curl();
        $payload = [
            "origin" => $order->getOrigin(),
            "orderId" => $order->getOrderId(),
            "service" => $order->getService(),
            "invoiceNumber" => $order->getInvoiceNumber(),
            "tracking" => $order->getTracking(),
            "createdAt" => $order->getCreatedAt(),
            "updatedAt" => $order->getUpdatedAt()
        ];                    
        $client->post($endpoint_to_call, $payload);
        if ( !$client->error ) {
            $updateArgs = [
                'integrated' => 'vtex_callback_success'
            ];
            $this->orderRepo->update(['id' => $order->getId()], $updateArgs);
        }
        return;
    }
}