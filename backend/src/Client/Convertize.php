<?php
namespace Maisenvios\Middleware\Client;

use Curl\Curl;

class Convertize {
    
    private $connection;
    private $endpoint;

    public function __construct($account, $token)
    {
        $this->connection = new Curl();
        $this->connection->setHeader('Authorization', "Token {$token}");
        $this->endpoint = "https://api.myconvertize.com.br/{$account}/api/1.0";
    }

    public function listOrders(Array $args = []) {
        $this->connection->get("{$this->endpoint}/orders/", $args);
        return $this->connection->response;
    }

    public function setOrderTracker($orderId, $payload) {
        $this->connection->post("{$this->endpoint}/orders/{$orderId}/trackers/", $payload);
        return $this->connection->response;
    }

    public function setOrderStatus($orderId, $payload) {
        $this->connection->put("{$this->endpoint}/orders/{$orderId}/status/", $payload);
        return $this->connection->response;
    }
}
?>