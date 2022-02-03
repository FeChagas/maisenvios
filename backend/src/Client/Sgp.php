<?php

namespace Maisenvios\Middleware\Client;

use Curl\Curl;

// $curl = new Curl();
// $curl->get('https://www.example.com/');

// if ($curl->error) {
//     echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
// } else {
//     echo 'Response:' . "\n";
//     var_dump($curl->response);
// }


class Sgp {

  private $connection;
  private $key;
  private $endpoint = 'http://www.sistemamaisenvios.com.br/novo/api/pre-postagem?chave_integracao={$}';

  public function __construct($key)
  {
    $this->connection = new Curl();
    $this->connection->setHeader('Content-Type', 'application/json');
    $this->connection->setHeader('Cookie', 'PHPSESSID=go2l879d8g5u8c9h35s5tl0kf6');
    $this->key = $key;
    $this->endpoint .= $this->key;
  }

  public function createPrePost(Array $payload) {
    $this->connection->post($this->endpoint,$payload);
    return $this->connection->response;
  }
}

// $integracao = $_POST['integracao'];
// $identificador = $_POST['identificador'];
// $observacao = $_POST['observacao'];
// $destinatario = $_POST['destinatario'];
// $cpf_cnpj = $_POST['cpf_cnpj'];
// $endereco = $_POST['endereco'];
// $numero = $_POST['numero'];
// $bairro = $_POST['bairro'];
// $cidade = $_POST['cidade'];
// $uf = $_POST['uf'];
// $cep = $_POST['cep'];
// $servico_correios = $_POST['servico_correios'];
// $complemento = $_POST['complemento'];
// $email = $_POST['email'];
//   $importacao = '
//   {
//       "objetos":[
//         {
//           "identificador": "'.$identificador.'",
//           "observacao": "'.$observacao.'",
//           "destinatario": "'.$destinatario.'",
//           "cpf_cnpj": "'.$cpf_cnpj.'",
//           "endereco": "'.$endereco.'",
//           "numero": "'.$numero.'",
//           "bairro": "'.$bairro.'",
//           "cidade": "'.$cidade.'",
//           "uf": "'.$uf.'",
//           "cep": "'.$cep.'",
//           "servico_correios": "'.$servico_correios.'",
//           "complemento": "'.$complemento.'",
//           "email": "'.$email.'",
//           "peso": 100,
//           "comprimento": 11,
//           "largura": 2,
//           "altura": 16
//         }
//       ]
//   }
//   ';

//   $curl = curl_init();
    
//   curl_setopt_array($curl, array(
//     CURLOPT_URL => 'http://www.sistemamaisenvios.com.br/novo/api/pre-postagem?chave_integracao='.$integracao,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'POST',
//     CURLOPT_POSTFIELDS =>$importacao,
//     CURLOPT_HTTPHEADER => array(
//       'Content-Type: application/json',
//       'Cookie: PHPSESSID=go2l879d8g5u8c9h35s5tl0kf6'
//     ),
//   ));

//   $response = curl_exec($curl);
//   echo $response;

?>