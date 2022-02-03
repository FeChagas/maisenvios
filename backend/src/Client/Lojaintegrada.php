<?php
namespace Maisenvios\Middleware\Client;

use Curl\Curl;
use Maisenvios\Middleware\Model\Order;

class Lojaintegrada {
    
    private $connection;
    private $key;
    private $token;
    private $endpoint = 'https://private-anon-0f49c68b04-lojaintegrada.apiary-mock.com/v1/';

    public function __construct($key, $token)
    {
        $this->connection = new Curl();
        $this->connection->setHeader('Content-Type', 'application/json');
        $this->connection->setHeader('Authorization', "chave_api {$key} aplicacao {$token}");
    }

    public function listOrders() {
        // $this->connection->get("{$this->endpoint}pedido/search/",['since_numero' => 345, 'situacao_id' => 1, 'pagamento_id' => 1, 'limit' => 10 ]);
        $this->connection->get('http://maisenvios.test/backend/src/Client/mock/lojaintegrada/listOrders.json');
        return $this->connection->response->response;
    }

    public function getOrder($id) {
        $this->connection->get($this->endpoint . "pedido/{$id}");
        return $this->connection->response;
    }

    public function addShippingCode($packageId, $trackingId) {
        ////2417337
        $this->connection->put("{$this->endpoint}pedido_envio/{$packageId}", ["objeto" => $trackingId]);
        return $this->connection->response;
    }
}
?>