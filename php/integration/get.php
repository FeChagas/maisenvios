<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 

$where = 'WHERE 1 = 1';
if ($_GET && isset($_GET['shop_id']) && !is_null($_GET['shop_id'])) {
  $where .= " AND shopId = {$_GET['shop_id']}";
}

$retorno = [];
$busca = "SELECT * FROM `shop_meta` {$where}";
$result = $link->query($busca);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $row['value'] = unserialize($row['value']);
    array_push($retorno, $row);
  }
}
echo json_encode($retorno);