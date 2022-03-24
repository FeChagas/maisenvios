<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\Order;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Model\SgpPrePost;
use Maisenvios\Middleware\Repository\OrderRepository;
use Maisenvios\Middleware\Repository\ShopRepository;
use Maisenvios\Middleware\Repository\ShopMetaRepository;
use Maisenvios\Middleware\Repository\ShippingRepository;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Client\Convertize;
use Maisenvios\Middleware\Client\Lojaintegrada;
use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Client\Sgp;
use Maisenvios\Middleware\Controller\LogController;
use Maisenvios\Middleware\Service\VtexService;

class IntegrationController {

    private $shopRepo;
    private $shopMetaRepo;
    private $shippingRepo;
    private $sgpLogRepo;
    private $orderRepo;

    public function __construct()
    {
        $this->shopRepo = new ShopRepository();
        $this->shopMetaRepo = new ShopMetaRepository();
        $this->shippingRepo = new ShippingRepository();
        $this->sgpLogRepo = new SgpLogRepository();
        $this->orderRepo = new OrderRepository();
    }

    public function run(int $shopId = 0) {
        //before each run, we warm up the logs just in case
        LogController::warmUp();
        //get the next shop to run
        //this search grabs the log and check the most recent log of each shop
        //and get the older from this group
        // $shops = ($shopId === 0) ? $this->shopRepo->findNextToRun() : $this->shopRepo->findOneBy(['id' => $shopId]);
        $shops = ($shopId === 0) ? $this->shopRepo->findAll(['active' => 1], 0, ['lastRunAt' => 'ASC']) : $this->shopRepo->findOneBy(['id' => $shopId]);
        foreach ($shops as $shop) {            
            switch ($shop->getEcommerce()) {
                case 'lojaintegrada':
                    $this->integrateLojaIntegrada($shop);
                    break;
                
                case 'Convertize':
                    $this->integrateConvertize($shop);
                    break;
                
                case 'VTEX':
                    $this->integrateVtex($shop);
                    break;
                default:
                    $log = new SgpLog();
                    $log->setShopId( $shop->getId() );
                    $log->setStatus("Integração com {$shop->getEcommerce()} não está preparada");
                    $this->sgpLogRepo->create($log);
                    break;
            }
            
            $this->shopRepo->update(['id' => $shop->getId()], ['lastRunAt' => (new \DateTime())->format('Y-m-d H:i:s') ]);
        }
    }

    /**
     * Run the Loja Integrada integration workflow
     */
    private function integrateLojaIntegrada($shop) {
        //Check if the shop has keys
        if (!$shop->getCustomerKey() || !$shop->getCustomerToken()) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus('Shop não possui chaves cadastradas');
            $this->sgpLogRepo->create($log);
        } else {
            //instanciate a new client
            $sgpClient = new Sgp($shop->getSysKey());
            $lojaIntegradaClient = new Lojaintegrada( $shop->getCustomerKey() , $shop->getCustomerToken() );
            //grab orders to integrate
            $since = date("Y-m-d H:i:s", strtotime("yesterday"));
            $orderQuery = ['since_criado' => $since, 'situacao_id' => 9, 'limit' => 20]; // Situação do pedido ID 9: Pedido Efetuado (padrão)
            $ordersResponse = $lojaIntegradaClient->listOrders( $orderQuery );
            if ($ordersResponse->objects) {
                foreach ($ordersResponse->objects as $order) {
                    $logHistory = $this->sgpLogRepo->findOneBy(['shopId' => $shop->getId(), 'orderId' => $order->numero, 'status_processamento' => 1]);
                    if (!$logHistory) {
                        $fullOrder = $lojaIntegradaClient->getOrder($order->numero);
                        $shipping = $this->shippingRepo->findOneBy([ 'idShop' => $shop->getId(), 'name' => $fullOrder->envios[0]->forma_envio->nome ]);
                        if(count($shipping) > 0) {
                            $sgpObj = SgpPrePost::createFromLojaintegrada($fullOrder, $shipping[0]);
                            $json = SgpPrePost::generatePayload([$sgpObj]);
                            $result = $sgpClient->createPrePost($json);
                            $log = SgpLog::createFromSgpResponse($shop->getId(), $fullOrder->numero, $result);
                            $this->sgpLogRepo->create($log);
                        } else {
                            $log = new SgpLog();
                            $log->setShopId( $shop->getId() );
                            $log->setOrderId($fullOrder->numero);
                            $log->setStatus("Forma de envio {$fullOrder->envios[0]->forma_envio->nome} não encontrada");
                            $this->sgpLogRepo->create($log);
                        }
                    }
                }
            } else {
                $log = new SgpLog();
                $log->setShopId( $shop->getId() );
                $log->setStatus( "nenhum pedido encontrado" );
                $log->setObjetos( json_encode( $orderQuery ) );
                $this->sgpLogRepo->create($log);
            }
        }
        return;
    }

    /**
     * Run the Convertize integration workflow
     */
    private function integrateConvertize($shop) {
        //Check if the shop has the needed informations to run
        if (!$shop->getCustomerToken()) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus('Shop não possui chaves cadastradas');
            $this->sgpLogRepo->create($log);
        } else {
            $sgpClient = new Sgp($shop->getSysKey());
            $convertizeClient = new Convertize($shop->getAccount(), $shop->getCustomerToken());

            //Get all active shippings of this shop
            $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
            foreach ($shippings as $shipping) {
                $orderQuery = ['status'=> 'FAT', 'shipping_type' => $shipping->getName()];
                $result = $convertizeClient->listOrders($orderQuery);
                foreach ($result->results as $order) {
                    $orderInDB = $this->orderRepo->findOneBy( ['orderId' => $order->id, 'storeId' => $shop->getId()] );
                    if ( count($orderInDB) == 0 ) {
                        $order = (new Order())->createFromConvertize($order, $shop->getId(), $shipping->getCorreios());
                        $this->orderRepo->create($order);
                    }
                }
            }

            $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 0]);
            foreach ($orders as $order) {
                $fullOrder = $convertizeClient->getOrder($order->getOrderId());
                //create the sgp object
                $sgpObj = SgpPrePost::createFromConvertize($fullOrder, $order->getService());
                //create the payload
                $json = SgpPrePost::generatePayload([$sgpObj]);
                //post it
                $result = $sgpClient->createPrePost($json);
                //Send the tracking code back
                foreach ($result->retorno->objetos as $objeto) {
                    $payloadStatus = ["status" => "ETP"];
                    $payloadTracker = [
                        "code" => $objeto->objeto,
                        "status" => "ETP"
                    ];
                    //send the tracking code back and update the order status
                    $convertizeClient->setOrderTracker($order->getOrderId(), $payloadTracker);
                    $convertizeClient->setOrderStatus($order->getOrderId(), $payloadStatus);
                    $this->orderRepo->update( ['orderId' => $order->getOrderId()] , ['integrated' => 1, 'tracking' => $objeto->objeto] );
                }
                //create the log
                $log = SgpLog::createFromSgpResponse($shop->getId(), $order->id, $result);
                $this->sgpLogRepo->create($log);
            }                       
        }
    }

    /**
     * Run the VTEX integration workflow
     */
    private function integrateVtex($shop) { 
        if ($shop->getAccount() === null || $shop->getCustomerKey() === null || $shop->getCustomerToken() === null) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus("As informações de Account, CustomerKey, CustomerToken não estão disponiveis e são necessárias.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Shop must have account, key and token", 1);            
        } else {
            //Grabs the shop meta
            $shopMetas = $this->shopMetaRepo->findAll(['shopId' => $shop->getId()]);
            $steps = [];
            $order_status = [];
            foreach ($shopMetas as $key => $meta) {
                switch ($meta->getName()) {
                    case 'vtex_integration_step':
                        $steps = maybe_unserialize( $meta->getValue() );
                        break;
                        
                    case 'vtex_order_status':
                        $order_status = maybe_unserialize( $meta->getValue() );
                        break;
                    default:
                    # code...
                    break;
                }
            }
            
            if (in_array('vtex_order_feed', $steps)) {
                (new VtexService($shop))->validateOrderFeedAndHook($order_status);                
            }
            
            if (in_array('sgp_pre_post', $steps)) {
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
                $orders = $this->orderRepo->findAll($orderQuery);            
                if (count($orders) > 0) {
                    $vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
                    $sgpClient = new Sgp($shop->getSysKey());
                    $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
                    foreach ($orders as $order) {
                        $isInvalidShipping = true;
                        foreach ($shippings as $shipping) {
                            $fullOrder = $vtexClient->getOrder($order->getOrderId());
                            if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                                $isInvalidShipping = false;
                                $sgpObj = SgpPrePost::createFromVtex($fullOrder, $shipping);
                                $json = SgpPrePost::generatePayload([$sgpObj]);
                                $result = $sgpClient->createPrePost($json);
                                if ($result->retorno->status_processamento == 1) {
                                    $updateOrderArgs = [
                                        'service' => $shipping->getCorreios(),
                                        'integrated' => 1,
                                        'invoiceNumber' => isset($fullOrder->packageAttachment->packages[0]->invoiceNumber) ? $fullOrder->packageAttachment->packages[0]->invoiceNumber : null,
                                        'tracking' => isset($result->retorno->objetos[0]->objeto) ? $result->retorno->objetos[0]->objeto : null
                                    ];
                                    $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                                    $log = SgpLog::createFromSgpResponse($shop->getId(), $order->getOrderId(), $result);
                                    $this->sgpLogRepo->create($log);
                                }
                            }                            
                        }
                        if ($isInvalidShipping) {
                            $isInvalidShipping = true;
                            $log = new SgpLog();
                            $log->setOrderId($order->getOrderId());
                            $log->setShopId( $shop->getId() );
                            $log->setStatus("Transportadora inválida.");
                            $log->setObjetos(json_encode($fullOrder));
                            $this->sgpLogRepo->create($log);
                            $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 'vtex_invalid_shipping_type']);
                        }
                    }
                } else {
                    $log = new SgpLog();
                    $log->setShopId( $shop->getId() );
                    $log->setStatus( "nenhum pedido encontrado" );
                    $log->setObjetos( json_encode( $orderQuery ) );
                    $this->sgpLogRepo->create($log);
                }                
            } else {
                /**
                 * IMPORTANT: When the sgp_pre_post is not set we'll check if the orders that would be processed in this step
                 * are already created in SGP through other means, like manual, for example;
                 */
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
                $orders = $this->orderRepo->findAll($orderQuery);            
                if (count($orders) > 0) {
                    $vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
                    $sgpClient = new Sgp($shop->getSysKey());
                    $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
                    foreach ($orders as $order) {
                        $isInvalidShipping = true;
                        foreach ($shippings as $shipping) {
                            $fullOrder = $vtexClient->getOrder($order->getOrderId());
                            if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                                $isInvalidShipping = false;
                                if (isset( $fullOrder->packageAttachment->packages[0]->invoiceNumber ) && ! is_null( $fullOrder->packageAttachment->packages[0]->invoiceNumber )) {
                                    $args = [ $fullOrder->packageAttachment->packages[0]->invoiceNumber ];
                                    $result = $sgpClient->getByInvoiceNumbers( $args );
                                    if ($result->retorno->status_processamento == 1) {
                                        $updateOrderArgs = [
                                            'integrated' => 1,
                                            'invoiceNumber' => isset($fullOrder->packageAttachment->packages[0]->invoiceNumber) ? $fullOrder->packageAttachment->packages[0]->invoiceNumber : null,
                                            'tracking' => isset($result->retorno->objetos[0]->objeto) ? $result->retorno->objetos[0]->objeto : null
                                        ];
                                        $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                                        $log = SgpLog::createFromSgpResponse($shop->getId(), $order->getOrderId(), $result);
                                        $this->sgpLogRepo->create($log);
                                    }
                                }
                            }                            
                        }
                        if ($isInvalidShipping) {
                            $isInvalidShipping = true;
                            $log = new SgpLog();
                            $log->setOrderId($order->getOrderId());
                            $log->setShopId( $shop->getId() );
                            $log->setStatus("Transportadora inválida.");
                            $log->setObjetos(json_encode($fullOrder));
                            $this->sgpLogRepo->create($log);
                            $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 'vtex_invalid_shipping_type']);
                        }
                    }
                }
            }

            if (in_array('vtex_tracking_update', $steps)) {
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 1];
                $orders = $this->orderRepo->findAll($orderQuery);            
                if (count($orders) > 0) {
                    $vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
                    foreach ($orders as $order) {
                        if($order->getTracking() !== null && $order->getInvoiceNumber() !== null) {
                            $args = [
                                "orderId" => $order->getOrderId(),
                                "invoiceNumber" => $order->getInvoiceNumber(),
                                "isDelivered" => false,
                                "events" => []
                            ];
                            $result = $vtexClient->updateOrderTracking( $order->getOrderId(), $order->getInvoiceNumber(), $args);
                            if (isset($result->receipt)) {
                                $updateOrderArgs = [
                                    'integrated' => 'vtex_tracking_update',
                                ];
                                $this->orderRepo->update(['orderId' => $order->getOrderId()], $updateOrderArgs);
                                $log = new SgpLog();
                                $log->setShopId( $shop->getId() );
                                $log->setStatus( "Código de rastreio enviado" );
                                $log->setObjetos( json_encode( $result ) );
                                $this->sgpLogRepo->create($log);
                            }
                        }
                    }
                }  
            }            
        }

        return;
    }
}