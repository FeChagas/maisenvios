<?php
namespace Maisenvios\Middleware\Client;

use Curl\Curl;

class Vtex {
    
    private $connection;
    private $endpoint;

    public function __construct($account, $key, $token)
    {
        $this->endpoint = "https://{$account}.vtexcommercestable.com.br/api";
        $this->connection = new Curl();
        $this->connection->setHeader('Content-Type', 'application/json');
        $this->connection->setHeader('X-VTEX-API-AppKey', $key);
        $this->connection->setHeader('X-VTEX-API-AppToken', $token);
    }

    public function getOrder($args) {
        $this->connection->get("{$this->endpoint}/oms/pvt/orders/{$args}");
        return $this->connection->response;
    }

    public function createHook($args) {        
        $this->connection->post("{$this->endpoint}/orders/hook/config", $args);
        return $this->connection->response;
    }

    public function getHooks() {
        $this->connection->get("{$this->endpoint}/orders/hook/config");
        return $this->connection->response;
    }

    public function createFeed($args) {        
        $this->connection->post("{$this->endpoint}/orders/feed/config", $args);
        return $this->connection->response;
    }

    public function getFeeds() {
        $this->connection->get("{$this->endpoint}/orders/feed/config");
        return $this->connection->response;
    }

    public function commit(array $args) {
        $this->connection->post("{$this->endpoint}/orders/feed", $args);
        return $this->connection->response;
    }
}
?>