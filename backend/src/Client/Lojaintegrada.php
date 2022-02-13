<?php
namespace Maisenvios\Middleware\Client;

use Curl\Curl;
use Maisenvios\Middleware\Model\Order;

class Lojaintegrada {
    
    private $connection;
    private $key;
    private $token;
    private $endpoint = 'https://api.awsli.com.br/v1';

    public function __construct($key, $token)
    {
        $this->connection = new Curl();
        $this->connection->setHeader('Content-Type', 'application/json');
        $this->connection->setHeader('Authorization', "chave_api {$key} aplicacao {$token}");
    }

    public function listOrders(Array $args = []) {
        $args['limit'] = ($args['limit']) ? $args['limit'] : 20;
        $this->connection->get("{$this->endpoint}/pedido/search/", $args); // The API limit is 20, sadly.
        return $this->connection->response;
    }

    public function getOrder($id) {
        $this->connection->get("{$this->endpoint}/pedido/{$id}");
        return $this->connection->response;
    }

    public function addShippingCode($packageId, $trackingId) {
        $this->connection->put("{$this->endpoint}/pedido_envio/{$packageId}", ["objeto" => $trackingId]);
        return $this->connection->response;
    }
}
?>