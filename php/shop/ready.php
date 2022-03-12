<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 

$where = 'WHERE 1 = 1';
if ($_GET && isset($_GET['id']) && !is_null($_GET['id'])) {
  $where .= " AND id = {$_GET['id']}";
}

$retorno = [];
$busca = "SELECT * FROM `shop` {$where}";
$result = $link->query($busca);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    array_push($retorno, $row);
  }
}
echo json_encode($retorno);