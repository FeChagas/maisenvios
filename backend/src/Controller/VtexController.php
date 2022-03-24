<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\Shop;
use Maisenvios\Middleware\Model\Order;
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
    private $shippingRepo;

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
    }

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
    }

    public function hasValidShipping($orderShipping) {
        if (!$this->shippings || empty($this->shippings)) {
            $shippings = $this->shippingRepo->findAll(['idShop' => $this->shop->getId(), 'active' => 1]);
            foreach ($shippings as $shipping) {
                array_push($this->shippings, $shipping->getName());
            }
        }
        return in_array($orderShipping, $this->shippings);
    }
}