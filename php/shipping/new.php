<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 
  $retorno = [];
  $name = $_POST['name'];
  $correios = $_POST['correios'];
  $idShop = $_POST['idShop'];
  $insert = "INSERT INTO `shipping` (`id`, `idShop`, `name`, `correios`, `active`) VALUES (NULL, '$idShop', '$name', '$correios', '1');";
  $result = $link->query($insert);
  array_push($retorno, array(
    "status" => 0,
    "message" => 'Cadastro realizado com sucesso'
  ));
  echo json_encode($retorno);

?>
