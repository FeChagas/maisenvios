<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\Order;
use Maisenvios\Middleware\Repository\ShopRepository;
use Maisenvios\Middleware\Client\Lojaintegrada;
use Maisenvios\Middleware\Client\Sgp;
use stdClass;

class IntegrationController {

    private $shopRepo;

    public function __construct()
    {
        $this->shopRepo = new ShopRepository();
    }

    public function run() {
        //list all shops
        //TODO: search for the next shop to run
        $shops = $this->shopRepo->findAll();
        
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
            //TODO: log fail
            return;            
        }
        $sgpClient = new Sgp($shop->getSysKey());
        $lojaIntegradaClient = new Lojaintegrada( $shop->getCustomerKey() , $shop->getCustomerToken() );
        $orders = $lojaIntegradaClient->listOrders();
        foreach ($orders as $order) {
            $fullOrder = $lojaIntegradaClient->getOrder($order->numero);
            //TODO: create SGP object
            $sgpObj = [];
            $sgpClient->createPrePost($sgpObj);
            //TODO: store result? one day maybe.            
        }
    }
}