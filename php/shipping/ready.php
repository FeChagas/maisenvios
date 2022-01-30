<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 
  $idShop = $_GET['idShop'];
  $retorno = [];
  $busca = "SELECT id, name, correios, active FROM `shipping` WHERE idShop = $idShop";
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      array_push($retorno, array(
        "id" => $row['id'],
        "name" => $row['name'],
        "correios" => $row['correios'],
        "active" => $row['active']
      ));
    }
  }
  echo json_encode($retorno);