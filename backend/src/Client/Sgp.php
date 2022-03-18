<?php

namespace Maisenvios\Middleware\Client;

use Curl\Curl;

class Sgp {

  private $connection;
  private $key;
  private $endpoint = 'http://www.sistemamaisenvios.com.br/novo/api';

  public function __construct($key)
  {
    $this->connection = new Curl();
    $this->connection->setHeader('Content-Type', 'application/json');
    $this->key = $key;
  }

  public function createPrePost($payload) {
    $this->connection->post($this->getEndpoint('/pre-postagem'), $payload);
    return $this->connection->response;
  }
  
  public function getByInvoiceNumbers(array $args){
    $this->connection->get($this->getEndpoint('/consulta-postagens'), ['notas_fiscais' => implode(',', $args)]);
    return $this->connection->response;
  }

  private function getEndpoint($endpoint) {
    return "{$this->endpoint}{$endpoint}?chave_integracao={$this->key}";
  }
}
?>