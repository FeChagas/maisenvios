<?php

namespace Maisenvios\Middleware\Client;

use Curl\Curl;

class Sgp {

  private $connection;
  private $key;
  private $endpoint = 'http://www.sistemamaisenvios.com.br/novo/api/pre-postagem?chave_integracao=';

  public function __construct($key)
  {
    $this->connection = new Curl();
    $this->connection->setHeader('Content-Type', 'application/json');
    $this->key = $key;
    $this->endpoint .= $this->key;
  }

  public function createPrePost($payload) {
    $this->connection->post($this->endpoint, $payload);
    return $this->connection->response;
  }
}
?>