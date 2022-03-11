<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Model\Shop;
use Maisenvios\Middleware\Model\Order;
use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Repository\OrderRepository;

class VtexController {
    
    private $shop;
    private $orderRepo;
    private $sgpLogRepo;
    private $vtexClient;

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
    }

    public function processFeed() {
        $toCommit = [];
        $orders = $this->vtexClient->getFeed();
        foreach ($orders as $order) {
            $orderObj = (new Order())->createFromVtexFeed($order, $this->shop->getId());
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
}