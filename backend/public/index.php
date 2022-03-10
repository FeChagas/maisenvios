<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Maisenvios\Middleware\Controller\IntegrationController;
use Maisenvios\Middleware\Controller\VtexController;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Repository\ShopRepository;

$is_dev = true;

function debug($to_print = false, $show_details = false) {
    global $is_dev;

    if ($is_dev) {
        $debug_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $line = $debug_arr[0]['line'];
        $file = $debug_arr[0]['file'];
        
        header('Content-Type: text/plain');
        
        echo "line: $line\n";
        echo "file: $file\n\n";
        if ($to_print !== false) {
            print_r($to_print);
        }
        if ($show_details) {
            print_r(array('GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER));
        }
        exit;
    }
}

if (isset($_GET['shop_id']) && !is_null($_GET['shop_id']) && strcmp($_GET['method'], 'vtex-order-hook') === 0) {
    if (isset($_GET['shop_id']) && !is_null($_GET['shop_id'])) {        
        
        $log = new SgpLog();
        $log->setObjetos(array('GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER));
        $log->setShopId($_GET['shop_id']);
        $log->setStatus('Feed VTEX recebido');
        (new SgpLogRepository())->create($log);

        $shops = (new ShopRepository())->findOneBy(['id' => $_GET['shop_id']]);
        foreach ($shops as $shop) {
            (new VtexController($shop))->processFeed($_POST);
        }
    }
} else {
    (new IntegrationController())->run();
}
