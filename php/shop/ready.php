<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 
  $retorno = [];
  $busca = 'SELECT id, name, ecommerce, account, active FROM `shop`';
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      array_push($retorno, array(
        "id" => $row['id'],
        "name" => $row['name'],
        "ecommerce" => $row['ecommerce'],
        "account" => $row['account'],
        "active" => $row['active']
      ));
    }
  }
  echo json_encode($retorno);