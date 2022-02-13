<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\SgpPrePost;
use Maisenvios\Middleware\Repository\ShopRepository;
use Maisenvios\Middleware\Repository\ShippingRepository;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Client\Lojaintegrada;
use Maisenvios\Middleware\Client\Sgp;
use Maisenvios\Middleware\Model\SgpLog;
use stdClass;

class IntegrationController {

    private $shopRepo;
    private $shippingRepo;
    private $sgpLogRepo;

    public function __construct()
    {
        $this->shopRepo = new ShopRepository();
        $this->shippingRepo = new ShippingRepository();
        $this->sgpLogRepo = new SgpLogRepository();
    }

    public function run() {
        //get the next shop to run
        //this search grabs the log and check the most recent log of each shop
        //and get the older from this group
        $shops = $this->shopRepo->findNextLojaIntegradaToRun();
        foreach ($shops as $shop) {
            switch ($shop->getEcommerce()) {
                case 'lojaintegrada':
                    $this->integrateLojaIntegrada($shop);
                    break;
                
                default:
                    # code...
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
                    //TODO: testar a validação do log, ainda não foi possivel por conta da chave ser invalida 
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
}