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
        //list all shops
        //TODO: search for the next shop to run
        $shops = $this->shopRepo->findAll(['ecommerce' => 'lojaintegrada', 'active' => 1]);
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
        if (!$shop->getCustomerKey() || !$shop->getCustomerToken()) {
            debug($shop);
            //TODO: log fail
            return;            
        }
        $sgpClient = new Sgp($shop->getSysKey());
        $lojaIntegradaClient = new Lojaintegrada( $shop->getCustomerKey() , $shop->getCustomerToken() );
        $orders = $lojaIntegradaClient->listOrders();
        foreach ($orders as $order) {
            $fullOrder = $lojaIntegradaClient->getOrder($order->numero);
            $shipping = $this->shippingRepo->findOneBy([ 'idShop' => $shop->getId(), 'name' => $fullOrder->envios[0]->forma_envio->nome ]);
            $sgpObj = SgpPrePost::createFromLojaintegrada($fullOrder, $shipping[0]);
            $json = SgpPrePost::generatePayload([$sgpObj]);
            $result = $sgpClient->createPrePost($json);
            $log = SgpLog::createFromSgpResponse($shop->getId(), $fullOrder->numero, $result);
            $s = $this->sgpLogRepo->create($log);
            debug($log);
        }
    }
}