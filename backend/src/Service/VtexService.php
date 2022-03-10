<?php
namespace Maisenvios\Middleware\Service;

use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Model\Shop;
use Maisenvios\Middleware\Repository\SgpLogRepository;

class VtexService {

    private $vtexClient;
    private $shop;
    private $sgpLogRepo;

    public function __construct(Shop $shop)
    {
        $this->sgpLogRepo = new SgpLogRepository();
        if (strcmp($shop->getEcommerce(), 'VTEX') !== 0) {
            $log = new SgpLog();
            $log->setShopId( $shop->getId() );
            $log->setStatus("Algo deu errado, uma loja não VTEX está tentando acessar seus controladores.");
            $this->sgpLogRepo->create($log);
            throw new \Exception("Trying to use an non-VTEX shop on VtexService", 1);            
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
    }

    public function validateOrderFeedAndHook() {
        $feedArgs = [
            "filter" => [
                "type" => "FromWorkflow",
                "status" => ["invoiced"],
                "disableSingleFire" => false
            ],
            "queue" => [
                "MessageRetentionPeriodInSeconds" => 60*60*24*7,
                "visibilityTimeoutInSeconds" => 60*60*1
            ]
        ];
        $this->vtexClient->createFeed($feedArgs);

        $url = "https://maisenviosintegracao.com.br/painel/backend/public/index.php?method=vtex-order-hook&shop_id={$this->shop->getId()}";
        $hook = $this->vtexClient->getHooks();
        if (!isset($hook->hook) || strcmp($hook->hook->url, $url) !== 0) {
            $hookArgs = [
                "filter" => [
                    "type" => "FromWorkflow",
                    "status" => ["invoiced"]
                ],
                "hook" => [
                    "url" => $url                
                ]
            ];
            $this->vtexClient->createHook($hookArgs);
            $log = new SgpLog();
            $log->setShopId( $this->shop->getId() );
            $log->setStatus("Hook criado.");
            $log->setObjetos(json_encode($hookArgs));
            $this->sgpLogRepo->create($log);
        }
        return;
    }
}