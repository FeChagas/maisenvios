<?php 
  header('Content-Type: application/json;charset=utf-8');
  include '../connection/mysql.php'; 
?>
<?php 
  $retorno = [];
  $busca = 'SELECT id,email,name,active FROM `users`';
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      array_push($retorno, array(
        "id" => $row['id'],
        "email" => $row['email'],
        "active" => $row['active'],
        "name" => $row['name']
      ));
    }
  }
  echo json_encode($retorno);