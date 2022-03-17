<?php
header('Content-Type: application/json;charset=utf-8');
include '../connection/mysql.php'; 

$response = [
    "success" => false,
    "data" => [],
    "message" => ""
];

if ($_GET && isset($_GET['shop_id']) && !is_null($_GET['shop_id'])) {
    $queries = [];
    $valid_params = ["vtex_integration_step", "vtex_order_status"];
    foreach ($valid_params as $key) {
        if($_POST && isset($_POST[$key]) && !is_null($_POST[$key])) {

            $query = "SELECT id FROM shop_meta WHERE shopId = '{$_GET['shop_id']}' AND name = '{$key}'";
            $exists = $link->query($query);
                       
            if ($exists->num_rows > 0) {
                while($row = $exists->fetch_assoc()) {
                    $_POST[$key] = is_array($_POST[$key]) ? serialize($_POST[$key]) : $_POST[$key];
                    $values_statement = "`value` = '{$_POST[$key]}'";
                    $where_statement = "`id` = '{$row['id']}'";
                    $query = "UPDATE `shop_meta` SET {$values_statement} WHERE {$where_statement}";
                    array_push($queries, $query);
                  }
            } else {
                $_POST[$key] = is_array($_POST[$key]) ? serialize($_POST[$key]) : $_POST[$key];
                $values_statement = "('{$key}','{$_POST[$key]}', '{$_GET['shop_id']}')";
                $query = "INSERT INTO `shop_meta`(`name`, `value`, `shopId`) VALUES {$values_statement};";
                array_push($queries, $query);
            }
        }
    }

    foreach ($queries as $query) {
        $repsonse['data'][] = $link->query($query);
    }
    $response['success'] = true;
} else {
    $response["message"] = "shop_id query string parameter is required";
}

echo json_encode($response);