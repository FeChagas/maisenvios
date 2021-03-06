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
                        $log->setStatus("O cliente {$shop->getName()} n??o definiu uma plataforma de integra????o");
                        $this->sgpLogRepo->create($log);
                        break;
                    }
                    break;

                default:
                    $log = new SgpLog();
                    $log->setShopId( $shop->getId() );
                    $log->setStatus("Integra????o com {$shop->getEcommerce()} n??o est?? preparada");
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
            $log->setStatus('Shop n??o possui chaves cadastradas');
            $this->sgpLogRepo->create($log);
        } else {
            //instanciate a new client
            $sgpClient = new Sgp($shop->getSysKey());
            $lojaIntegradaClient = new Lojaintegrada( $shop->getCustomerKey() , $shop->getCustomerToken() );
            //grab orders to integrate
            $since = date("Y-m-d H:i:s", strtotime("yesterday"));
            $orderQuery = ['since_criado' => $since, 'situacao_id' => 4, 'limit' => 20]; // Situa????o do pedido ID 4: Pedido Pago (padr??o do cliente Germany)
            $ordersResponse = $lojaIntegradaClient->listOrders( $orderQuery );
            if ($ordersResponse->objects) {
                foreach ($ordersResponse->objects as $order) {
                    $orderInDB = $this->orderRepo->findOneBy(['storeId' => $shop->getId(), 'orderId' => $order->numero]);
                    if (count($orderInDB) <= 0) {
                        $fullOrder = $lojaIntegradaClient->getOrder($order->numero);
                        $shipping = $this->shippingRepo->findOneBy([ 'idShop' => $shop->getId(), 'name' => $fullOrder->envios[0]->forma_envio->nome ]);
                        if(count($shipping) > 0) {
                            $orderObj = Order::createFromLojaIntegrada($fullOrder, $shop->getId() ,$shipping[0]->getCorreios());
                            $this->orderRepo->create($orderObj);                            
                        }
                    }
                }
            }

            $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 0]);
            foreach ($orders as $order) {
                $fullOrder = $lojaIntegradaClient->getOrder($order->getOrderId());
                $sgpObj = SgpPrePost::createFromLojaintegrada($fullOrder, $order->getService());
                $json = SgpPrePost::generatePayload([$sgpObj]);
                $result = $sgpClient->createPrePost($json);
                foreach ($result->retorno->objetos as $objeto) {
                    //send the tracking code back and update the order status
                    $lojaIntegradaClient->addShippingCode($fullOrder->envios[0]->id, $objeto->objeto);
                    //por solicita????o do cliente Germany n??o ser??o atualizado os status dos pedidos integrados
                    // $lojaIntegradaClient->updateOrderStatus($order->getOrderId(), 'pedido_enviado');
                    $this->orderRepo->update( ['orderId' => $order->getOrderId(), 'storeId' => $shop->getId()] , ['integrated' => 1, 'tracking' => $objeto->objeto] );
                }
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
            $log->setStatus('Shop n??o possui chaves cadastradas');
            $this->sgpLogRepo->create($log);
        } else {
            $sgpClient = new Sgp($shop->getSysKey());
            $convertizeClient = new Convertize($shop->getAccount(), $shop->getCustomerToken());

            //Get all active shippings of this shop
            $shippings = $this->shippingRepo->findAll(['idShop' => $shop->getId(), 'active' => 1]);
            foreach ($shippings as $shipping) {
                foreach (['FAT', 'ETP'] as $status) {
                    $orderQuery = ['status'=> $status, 'shipping_type' => $shipping->getName(), 'page' => 1];
                    $hasNext = true;
                    while ($hasNext) {
                        $result = $convertizeClient->listOrders($orderQuery);
                        $hasNext = false;
                        foreach ($result->results as $order) {
                            if (count($order->trackers) === 0) {
                                $orderInDB = $this->orderRepo->findOneBy( ['orderId' => $order->id, 'storeId' => $shop->getId()] );
                                if ( count($orderInDB) == 0 ) {
                                    $order = (new Order())->createFromConvertize($order, $shop->getId(), $shipping->getCorreios());
                                    $this->orderRepo->create($order);
                                }
                            }
                        }
                        if( $result->next != null && $orderQuery['page'] < 10) {
                            $hasNext = true;
                            $orderQuery['page'] = $orderQuery['page']+1;
                        }
                    }
                }
            }
            
            $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 0], 1, ['updatedAt' => 'DESC']);
            $orders_to_unlock = [];
            while (count($orders) > 0) {
                foreach ($orders as $key => $order) {
                    //lock the order so other instances of this script wont get the same order
                    $this->orderRepo->update( ['orderId' => $order->getOrderId()] , ['integrated' => 'locked'] );
                    //save it to unlock later if needed
                    array_push( $orders_to_unlock, $order->getOrderId() );
                    //get the full order data from convertize
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
                    unset($orders[$key]);
                }
                $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 0], 1, ['updatedAt' => 'DESC']);
            }

            //unlock remain orders
            $orders = $this->orderRepo->findAll(['storeId' => $shop->getId(), 'integrated' => 'locked']);
            foreach ($orders as $order) {
                if (in_array($order->getOrderId(), $orders_to_unlock)) {
                    $this->orderRepo->update( ['orderId' => $order->getOrderId()] , ['integrated' => 0] );
                }
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
            $log->setStatus("As informa????es de Account, CustomerKey, CustomerToken n??o est??o disponiveis e s??o necess??rias.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Shop must have account, key and token", 1);            
        } else {
            $vtexController = new VtexController($shop, $integrates_to);
            //Grabs the shop meta
            $shopMetas = $this->shopMetaRepo->findAll(['shopId' => $shop->getId()]);
            $steps = [];
            $order_status = [];
            $endpoint_to_call = '';
            foreach ($shopMetas as $meta) {
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
                $orderQuery = ['storeId' => $shop->getId(), 'integrated' => 0];
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
                            $log->setStatus("Integra????o com {$integrates_to} na etapa sgp_pre_post da VTEX n??o est?? preparada");
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