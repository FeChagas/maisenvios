<?php 
header('Content-Type: application/json;charset=utf-8');
include '../connection/mysql.php'; 
$set = '';
$where = 'WHERE 1 = 1';
if ($_GET && isset($_GET['id']) && !is_null($_GET['id'])) {
  $where .= " AND id = {$_GET['id']}";
} else {
    echo json_encode([
        'success' => false,
        'message' => 'query parameter id is required'
    ]);
}

$valid_fields = [
    'id',
    'name',
    'key_mais',
    'key_primary',
    'token_primary',
    'account',
    'ecommerce',
    'cron',
    'active'
];

if ($_POST && isset($_POST) && !is_null($_POST)) {
    foreach ($_POST as $key => $value) {
        if(in_array($key, $valid_fields)) {
            if ($key === array_key_first($_POST)) {
                $set .= 'SET ';
            }
            
            $set .= "`{$key}` = '{$value}'";
            
            if ($key !== array_key_last($_POST)) {
                $set .= ', ';
            }
        }
    }
}else {
    echo json_encode([
        'success' => false,
        'message' => 'no data was sent'
    ]);
}
    
$retorno = [];
$busca = "UPDATE `shop` {$set} {$where}";
echo json_encode([
    'success' => $link->query($busca),
]);
?>

