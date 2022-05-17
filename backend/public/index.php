<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Maisenvios\Middleware\Controller\IntegrationController;
use Maisenvios\Middleware\Controller\VtexController;
use Maisenvios\Middleware\Model\SgpLog;
use Maisenvios\Middleware\Repository\SgpLogRepository;
use Maisenvios\Middleware\Repository\ShopRepository;

$is_dev = true;

//Runs the feed process to record the VTEX Orders
if (isset($_GET['shop_id']) && !is_null($_GET['shop_id']) && isset($_GET['method']) && !is_null($_GET['method']) && strcmp($_GET['method'], 'vtex-order-hook') === 0) {
    if (isset($_GET['shop_id']) && !is_null($_GET['shop_id'])) {        
        
        $log = new SgpLog();
        $log->setObjetos(json_encode( array('GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER) ));
        $log->setShopId($_GET['shop_id']);
        $log->setStatus('Feed VTEX recebido');
        (new SgpLogRepository())->create($log);

        $shops = (new ShopRepository())->findOneBy(['id' => $_GET['shop_id']]);
        foreach ($shops as $shop) {
            (new VtexController($shop))->processFeed();
        }
    }
// Run the integration to a specific shop
} else if(isset($_GET['shop_id']) && !is_null($_GET['shop_id'])){
    (new IntegrationController())->run($_GET['shop_id']);
// Run the standard workflow 
} else {
    (new IntegrationController())->run();
}

/**
 * Print and stop execution
 * @param Array|Boolean $to_print 
 */
function debug($to_print = null, $show_details = false) {
    global $is_dev;

    if ($is_dev) {
        $debug_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $line = $debug_arr[0]['line'];
        $file = $debug_arr[0]['file'];
        
        header('Content-Type: text/plain');
        
        echo "line: $line\n";
        echo "file: $file\n\n";
        if (!is_null($to_print)) {
            if (is_bool($to_print)) {
                ($to_print) ? print_r('true') : print_r('false');
            } else {
                print_r($to_print);
            }
        } else {
            print_r('INFO: a null value was passed or nothing was passed, either way nothing to show.');
        }
        if ($show_details) {
            print_r(array('GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER));
        }
        exit;
    }
}

/**
 * Unserialize data only if it was serialized.
 */
function maybe_unserialize( $data ) {
    if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
        return @unserialize( trim( $data ) );
    }
    return $data;
}

/**
 * Check value to find if it was serialized.
 */
function is_serialized( $data, $strict = true ) {
    // If it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' === $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace ) {
            return false;
        }
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 ) {
            return false;
        }
        if ( false !== $brace && $brace < 4 ) {
            return false;
        }
    }
    $token = $data[0];
    switch ( $token ) {
        case 's':
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            // Or else fall through.
        case 'a':
        case 'O':
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b':
        case 'i':
        case 'd':
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
    }
    return false;
}