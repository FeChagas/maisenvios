<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Model\SgpPrePost;
use Maisenvios\Middleware\Repository\OrderRepository;
use Maisenvios\Middleware\Repository\ShopRepository;
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
    private $shippingRepo;
    private $sgpLogRepo;
    private $orderRepo;

    public function __construct()
    {
        $this->shopRepo = new ShopRepository();
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
        $shops = ($shopId === 0) ? $this->shopRepo->findNextToRun() : $this->shopRepo->findOneBy(['id' => $shopId]);
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
                $orders = [];
                //Should loop through pages, but the cron runs fast enough so its not need for now
                do {
                    $orderQuery = ['status'=> 'FAT', 'shipping_type' => $shipping->getName()];
                    $result = $convertizeClient->listOrders($orderQuery);
                    array_push($orders, ...$result->results);
                    //Só ativar o loop caso seja realmente necessario
                } while (1 > 1);

                if (count($orders) > 0) {
                    //if at least one order was returned we finally do something
                    foreach ($orders as $order) {
                        //check if it has been sucessfully integrated before
                        $logHistory = $this->sgpLogRepo->findOneBy(['shopId' => $shop->getId(), 'orderId' => $order->id, 'status_processamento' => 1]);
                        if (!$logHistory) {
                            //create the sgp object
                            $sgpObj = SgpPrePost::createFromConvertize($order, $shipping);
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
                                $convertizeClient->setOrderTracker($order->id, $payloadTracker);
                                $convertizeClient->setOrderStatus($order->id, $payloadStatus);
                            }
                            //create the log
                            $log = SgpLog::createFromSgpResponse($shop->getId(), $order->id, $result);
                            $this->sgpLogRepo->create($log);
                        }
                    }
                } else {
                    //if no orders has returned, record it in the log
                    $log = new SgpLog();
                    $log->setShopId( $shop->getId() );
                    $log->setStatus( "nenhum pedido encontrado" );
                    $log->setObjetos( json_encode( $orderQuery ) );
                    $this->sgpLogRepo->create($log);
                }
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
            (new VtexService($shop))->validateOrderFeedAndHook();
            $vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
            $sgpClient = new Sgp($shop->getSysKey());
            $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
            $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
            $orders = $this->orderRepo->findAll($orderQuery);            
            if (count($orders) > 0) {
                foreach ($orders as $order) {
                    $logHistory = $this->sgpLogRepo->findOneBy(['shopId' => $shop->getId(), 'orderId' => $order->getOrderId(), 'status_processamento' => 1]);
                    if (!$logHistory) {
                        $isInvalidShipping = true;
                        foreach ($shippings as $shipping) {
                            $fullOrder = $vtexClient->getOrder($order->getOrderId());
                            if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                                $isInvalidShipping = false;
                                $sgpObj = SgpPrePost::createFromVtex($fullOrder, $shipping);
                                $json = SgpPrePost::generatePayload([$sgpObj]);
                                $result = $sgpClient->createPrePost($json);
                                $log = SgpLog::createFromSgpResponse($shop->getId(), $order->getOrderId(), $result);
                                $this->sgpLogRepo->create($log);
                                if ($result->retorno->status_processamento == 1) {
                                    $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 1]);
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
                            $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 2]);
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
}