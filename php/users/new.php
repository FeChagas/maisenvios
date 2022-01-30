<?php include '../connection/mysql.php'; ?>
<?php 
  header('Content-Type: application/json;charset=utf-8');
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $retorno = [];
  if($password === $confirm_password){
    $busca = "SELECT * FROM `users` WHERE email = '$email'";
    $result = $link->query($busca);
    if ($result->num_rows > 0) {
      array_push($retorno, array(
        "status" => 1,
        "message" => 'E-mail já cadastrado na base'
      ));
    }else{
      $password = sha1($_POST['password']);
      $insert = "INSERT INTO `users` (`id`, `email`, `password`, `name`, `active`) VALUES (NULL, '$email', '$password', '$name', '1')";
      $result = $link->query($insert);
      array_push($retorno, array(
        "status" => 0,
        "message" => 'Cadastro realizado com sucesso'
      ));
    }
  }else{
    array_push($retorno, array(
      "status" => 2,
      "message" => 'Senhas não conferem'
    ));
  }
  echo json_encode($retorno);
?>