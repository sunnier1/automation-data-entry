<?php
include 'config.php';

$id = $_GET['id'];
$table = $_GET['table'];

$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(["success"=>false]);
    exit;
}

$query = "DELETE FROM $table WHERE id = $id";

if($conn->query($query)){
    echo json_encode(["success"=>true]);
}else{
    echo json_encode(["success"=>false]);
}
?>