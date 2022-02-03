<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Maisenvios\Middleware\Controller\IntegrationController;

$is_dev = true;

function debug($to_print = false) {
    global $is_dev;

    if ($is_dev) {
        $debug_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $line = $debug_arr[0]['line'];
        $file = $debug_arr[0]['file'];

        header('Content-Type: text/plain');

        echo "linha: $line\n";
        echo "arquivo: $file\n\n";
        print_r(array('GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER));

        if ($to_print) {
            print_r($to_print);
        }
        exit;
    }
}

new IntegrationController();