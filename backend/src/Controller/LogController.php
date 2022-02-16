<?php
namespace Maisenvios\Middleware\Controller;

use Maisenvios\Middleware\Repository\ShopRepository;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Model\SgpLog;

class LogController {
    public static function warmUp() {
        $shopRepo = new ShopRepository();
        $sgpLogRepo = new SgpLogRepository();
        $shops = $shopRepo->findAll();
        foreach ($shops as $shop) {
            $hasLogs = $sgpLogRepo->findOneBy(['shopId' => $shop->getId()]);
            if (count($hasLogs) <= 0) {
                $log = new SgpLog();
                $log->setShopId($shop->getId());
                $log->setStatus("log warm up");
                $sgpLogRepo->create($log);
            }
        }
        return;
    }
}