<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php
  $retorno = [];
  $name = $_POST['name'];
  $key_mais = $_POST['key_mais'];
  $key_primary = $_POST['key_primary'];
  $token_primary = $_POST['token_primary'];
  $account = $_POST['account'];
  $ecommerce = $_POST['ecommerce'];
  
  $insert = "INSERT INTO `shop` (`id`, `name`, `key_mais`, `key_primary`, `token_primary`, `account`, `ecommerce`, `active`) VALUES (NULL, '$name', '$key_mais', '$key_primary', '$token_primary', '$account', '$ecommerce', '1');";
  $result = $link->query($insert);
  array_push($retorno, array(
    "status" => 0,
    "message" => 'Cadastro realizado com sucesso'
  ));
      
  echo json_encode($retorno);
?>
