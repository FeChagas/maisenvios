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

    public function run() {
        //before each run, we warm up the logs just in case
        LogController::warmUp();
        //get the next shop to run
        //this search grabs the log and check the most recent log of each shop
        //and get the older from this group
        $shops = $this->shopRepo->findNextToRun();
        // $shops = $this->shopRepo->findOneBy(['id' => 12]);
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
            $orderQuery = ['since_criado' => $since, 'situacao_id' => 4, 'limit' => 20];
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

    private function integrateConvertize($shop) {
        if (!$shop->getCustomerToken()) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus('Shop não possui chaves cadastradas');
            $this->sgpLogRepo->create($log);
        } else {
            $sgpClient = new Sgp($shop->getSysKey());
            $convertizeClient = new Convertize($shop->getAccount(), $shop->getCustomerToken());
            $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
            foreach ($shippings as $shipping) {
                $orders = [];
                do {
                    $orderQuery = ['status'=> 'FAT', 'shipping_type' => $shipping->getName()];
                    $result = $convertizeClient->listOrders($orderQuery);
                    array_push($orders, ...$result->results);
                    //Só ativar o loop caso seja realmente necessario
                } while (1 > 1);

                if (count($orders) > 0) {
                    foreach ($orders as $order) {
                        $logHistory = $this->sgpLogRepo->findOneBy(['shopId' => $shop->getId(), 'orderId' => $order->id, 'status_processamento' => 1]);
                        //Verifica se já existe um envio bem sucedido, caso sim, aborta
                        if (!$logHistory) {
                            $sgpObj = SgpPrePost::createFromConvertize($order, $shipping);
                            $json = SgpPrePost::generatePayload([$sgpObj]);
                            $result = $sgpClient->createPrePost($json);
                            //Define o código de rastreio e status do pedido
                            foreach ($result->retorno->objetos as $objeto) {
                                $payloadStatus = ["status" => "ETP"];
                                $payloadTracker = [
                                    "code" => $objeto->objeto,
                                    "status" => "ETP"
                                ];
                                $convertizeClient->setOrderTracker($order->id, $payloadTracker);
                                $convertizeClient->setOrderStatus($order->id, $payloadStatus);
                            }
                            $log = SgpLog::createFromSgpResponse($shop->getId(), $order->id, $result);
                            $this->sgpLogRepo->create($log);
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
        }
    }

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
                        foreach ($shippings as $shipping) {
                            $isInvalidShipping = true;
                            $fullOrder = $vtexClient->getOrder($order->getOrderId());
                            if (strcmp($fullOrder->shippingData->logisticsInfo[0]->deliveryCompany, $shipping->getName()) === 0) {
                                $isInvalidShipping = false;
                                $sgpObj = SgpPrePost::createFromVtex($fullOrder, $shipping);
                                $json = SgpPrePost::generatePayload([$sgpObj]);
                                $result = $sgpClient->createPrePost($json);
                                if ($result->retorno->status_processamento == 1) {
                                    $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 1]);
                                }
                                $log = SgpLog::createFromSgpResponse($shop->getId(), $order->getOrderId(), $result);
                                $this->sgpLogRepo->create($log);
                            }

                            if (!$isInvalidShipping) {
                                $this->orderRepo->update(['orderId' => $order->getOrderId()], ['integrated' => 2]);
                                $log = new SgpLog();
                                $log->setOrderId($order->getOrderId());
                                $log->setShopId( $shop->getId() );
                                $log->setStatus("A transportadora inválida.");
                                $log->setObjetos(json_encode($fullOrder));
                                $this->sgpLogRepo->create($log);
                            }
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