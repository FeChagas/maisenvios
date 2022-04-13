<?php
namespace Maisenvios\Middleware\Controller;

use Curl\Curl;
use Maisenvios\Middleware\Model\Order;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Model\SgpPrePost;
use Maisenvios\Middleware\Model\Shop;
use Maisenvios\Middleware\Repository\OrderRepository;
use Maisenvios\Middleware\Repository\ShopRepository;
use Maisenvios\Middleware\Repository\ShopMetaRepository;
use Maisenvios\Middleware\Repository\ShippingRepository;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Client\Convertize;
use Maisenvios\Middleware\Client\Lojaintegrada;
use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Client\Sgp;
use Maisenvios\Middleware\Client\MaisEnvios;
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
                    $integratesTo = $this->shopMetaRepo->findOneBy( ['name' => 'integrates_to', 'shopId' => $shop->getId()] );
                    if ( !! $integratesTo[0]->getValue() ) {
                        $this->integrateVtex($shop, $integratesTo[0]->getValue());
                    }  else {
                        $log = new SgpLog();
                        $log->setShopId( $shop->getId() );
                        $log->setStatus("O cliente {$shop->getName()} não definiu uma plataforma de integração");
                        $this->sgpLogRepo->create($log);
                        break;
                    }
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
    private function integrateLojaIntegrada(Shop $shop) {
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
    private function integrateConvertize(Shop $shop) {
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
    private function integrateVtex(Shop $shop, string $integrates_to) { 
        if ($shop->getAccount() === null || $shop->getCustomerKey() === null || $shop->getCustomerToken() === null) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus("As informações de Account, CustomerKey, CustomerToken não estão disponiveis e são necessárias.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Shop must have account, key and token", 1);            
        } else {
            $vtexController = new VtexController($shop, $integrates_to);
            //Grabs the shop meta
            $shopMetas = $this->shopMetaRepo->findAll(['shopId' => $shop->getId()]);
            $steps = [];
            $order_status = [];
            $endpoint_to_call = '';
            foreach ($shopMetas as $key => $meta) {
                switch ($meta->getName()) {
                    case 'vtex_integration_step':
                        $steps = maybe_unserialize( $meta->getValue() );
                        break;
                        
                    case 'vtex_order_status':
                        $order_status = maybe_unserialize( $meta->getValue() );
                        break;

                    case 'vtex_endpoint_to_call':
                        $endpoint_to_call = filter_var($meta->getValue(), FILTER_VALIDATE_URL);
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
                // $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
                $orderQuery = ['orderId' => 'SSD-1223012857211-01'];
                $orders = $this->orderRepo->findAll($orderQuery);            
                if (count($orders) > 0) {
                    switch ($integrates_to) {
                        case 'SGP':
                            $vtexController->createSgpPrePost($orders);
                            break;
                        
                        case 'MaisEnvios':
                            $vtexController->createMaisEnviosPrePost($orders);
                            break;
                        default:
                            $log = new SgpLog();
                            $log->setShopId( $shop->getId() );
                            $log->setStatus("Integração com {$integrates_to} na etapa sgp_pre_post da VTEX não está preparada");
                            $this->sgpLogRepo->create($log);
                            break;
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
                 * are already created in SGP through other means, like manually, for example;
                 */
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
                $orders = $this->orderRepo->findAll($orderQuery);
                if (count($orders) > 0) {
                    $vtexController->checkForExistingSgpPrePost($orders);
                }
            }

            if (in_array('vtex_tracking_update', $steps)) {
                //Grabs orders that already have its tracking code
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 1];
                $orders = $this->orderRepo->findAll($orderQuery);            
                if (count($orders) > 0) {
                    $vtexController->updateVtexTrackingInformation($orders);
                }  
            }

            if (in_array('vtex_call_endpoint', $steps) && !empty($endpoint_to_call)) {
                $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 'vtex_tracking_update' ]);
                foreach ($orders as $order) {
                    $vtexController->callEndpoint($order, $endpoint_to_call);                    
                }
            }
        }
        return;
    }
}