<?php
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  include '../connection/mysql.php'; 
  $busca = 'SELECT orders.id ,shipping.name, orders.orderId, shop.account, shop.token_primary, shop.key_primary, shop.key_mais, shipping.correios FROM orders  INNER JOIN shop ON orders.storeId = shop.id  INNER JOIN shipping ON orders.storeId = shipping.idShop WHERE orders.integrated = 0 and shop.active = 1 and shop.ecommerce = "VTEX"';
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.$row['account'].'.vtexcommercestable.com.br/api/oms/pvt/orders/'.$row['orderId'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Content-Type: application/json',
          'X-VTEX-API-AppToken: '.$row['token_primary'],
          'X-VTEX-API-AppKey: '.$row['key_primary']
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      $pedidos = json_decode($response);
      $sla = $pedidos->shippingData->logisticsInfo[0]->selectedSla;
      $deliveryCompany = $pedidos->shippingData->logisticsInfo[0]->deliveryCompany;
      if($deliveryCompany == $row['name']){
        $servicos = $row['correios'];

        $importacao = '
        {
            "objetos":[
              {
                "identificador": "'.$pedidos->orderId.'",
                "observacao": "Pedido N'.$pedidos->orderId.'",
                "destinatario": "'.$pedidos->shippingData->address->receiverName.'",
                "cpf_cnpj": "'.$pedidos->document.'",
                "endereco": "'.$pedidos->shippingData->address->street.'",
                "numero": "'.$pedidos->shippingData->address->number.'",
                "bairro": "'.$pedidos->shippingData->address->neighborhood.'",
                "cidade": "'.$pedidos->shippingData->address->city.'",
                "uf": "'.$pedidos->shippingData->address->state.'",
                "cep": "'.$pedidos->shippingData->address->postalCode.'",
                "servico_correios": "'.$servicos.'",
                "complemento": "'.$pedidos->shippingData->address->complement.'",
                "email": "'.$pedidos->clientProfileData->email.'",
                "peso": 100,
                "comprimento": 11,
                "largura": 2,
                "altura": 16
              }
            ]
        }
        ';
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://www.sistemamaisenvios.com.br/novo/api/pre-postagem?chave_integracao='.$row['key_mais'],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$importacao,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
      
        $response = curl_exec($curl);

        $update = "UPDATE `orders` SET `integrated` = '1' WHERE `orders`.`id` = ".$row['id'];
        $results = $link->query($update);
      }
    }
  }

