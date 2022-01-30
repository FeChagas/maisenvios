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
  $busca = "SELECT * FROM `shop` WHERE account = '$account'";
  $result = $link->query($busca);
    if ($result->num_rows > 0) {
      array_push($retorno, array(
        "status" => 1,
        "message" => 'Loja já cadastrada'
      ));
    }else{
      $key_mais = sha1($_POST['key_mais']);
      $key_primary = sha1($_POST['key_primary']);
      $token_primary = sha1($_POST['token_primary']);
      $insert = "INSERT INTO `shop` (`id`, `name`, `key_mais`, `key_primary`, `token_primary`, `account`, `ecommerce`, `active`) VALUES (NULL, '$name', '$key_mais', '$key_primary', '$token_primary', '$account', '$ecommerce', '1');";
      $result = $link->query($insert);
      array_push($retorno, array(
        "status" => 0,
        "message" => 'Cadastro realizado com sucesso'
      ));
      
    }
    echo json_encode($retorno)
?>
