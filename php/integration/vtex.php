<?php
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  include '../connection/mysql.php'; 
  $busca = 'SELECT * FROM shop  WHERE shop.cron = 0 and shop.active = 1 and shop.ecommerce = "VTEX"';
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.$row['account'].'.vtexcommercestable.com.br/api/oms/pvt/orders?f_creationDate=&f_status=invoiced',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'X-VTEX-API-AppToken: '.$row['token_primary'],
          'X-VTEX-API-AppKey: '.$row['key_primary']
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      $obj = json_decode($response);
      foreach ($obj->list as $key => $value) {
        $id = $row['id'];
        $orderId = $value->orderId;
        $buscaP = 'SELECT * FROM orders  WHERE orders.orderId = "'.$orderId.'" and orders.storeId = '.$id.'';
        $results = $link->query($buscaP);
        // echo $buscaP;
        // echo '<br />';
        if ($results->num_rows > 0) {}else{
          // echo 'amigo estou aqui';
          $insert = 'INSERT INTO orders (id, orderId, storeId, integrated) VALUES (NULL, "'.$orderId.'", "'.$id.'", 0)';
          $result = $link->query($insert);
        }
        
      }
    }
  }

