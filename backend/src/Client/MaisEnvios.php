<?php

namespace Maisenvios\Middleware\Client;

use Curl\Curl;

class MaisEnvios {

    private $connection;
    private $username;
    private $password;
    private $token = false;
    private $host = 'https://sistema.maisenvios.com.br/api';

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->connection = new Curl();
        $this->connection->setHeader('Content-Type', 'application/json');
        $this->auth();
        $this->connection->setHeader('Authorization', "Bearer {$this->token}");
    }

    public function isConnected() {
        return !!$this->token;
    }

    public function prepost($payload) {
        $this->connection->post( $this->getEndpoint('/prepost'), $payload );
        return $this->connection->response;
    }

    public function getCustomer($id) {
        $this->connection->get( $this->getEndpoint("/customers/{$id}") );
        return $this->connection->response;
    }

    public function getMe() {
        $this->connection->get( $this->getEndpoint('/auth/me') );
        return $this->connection->response;
    }
        
    private function auth() {
        $this->connection->post($this->getEndpoint('/auth/login'), [
            "username" => $this->username,
            "password" => $this->password
        ]);
        $this->token = ($this->connection->response->statusCode) ? false : $this->connection->response;
        return;
    }

    private function getEndpoint($route) {
        return "{$this->host}{$route}";
    }
}
?>