<?php
namespace Maisenvios\Middleware\Service;

use Maisenvios\Middleware\Client\Vtex;
use Maisenvios\Middleware\Model\Shop;

class VtexService {

    private $vtexClient;
    private $shop;

    public function __construct(Shop $shop)
    {
        if (strcmp($shop->getEcommerce(), 'VTEX') !== 0) {
            //gravar log de erro
            throw new \Exception("Shop must by VTEX", 1);            
        }
        
        if ($shop->getAccount() !== null && $shop->getCustomerKey() !== null && $shop->getCustomerToken() !== null) {
            $this->vtexClient = new Vtex($shop->getAccount(), $shop->getCustomerKey(), $shop->getCustomerToken());
        } else {
            //Grava log de erro
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
        }
        return;
    }
}