<?php 
  header('Content-Type: application/json;charset=utf-8');
  session_start();
  include '../connection/mysql.php'; 
?>
<?php 
  $retorno = [];
  $email = $_POST['email'];
  $password = sha1($_POST['password']);
  $busca = "SELECT * FROM `users` WHERE `email` = '$email' AND `password`= '$password'";
  $result = $link->query($busca);
  if ($result->num_rows > 0) {
    $_SESSION['id'] = $idUser;
    array_push($retorno, array(
      "status" => 0
    ));
  }
  else{
    unset ($_SESSION['id']);
    array_push($retorno, array(
      "status" => 1
    ));
  }
  echo json_encode($retorno);

?>