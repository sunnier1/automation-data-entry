<?php
include 'config.php';

$table = $_GET['table'];

$allowed_tables = ['pb','pmk','sbk','serumpun'];
if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success'=>false]);
    exit;
}

$ids = [];
$res = $conn->query("SELECT id FROM $table");

while($r = $res->fetch_assoc()) {
    $ids[] = $r['id'];
}

shuffle($ids);

$order = 1;
foreach($ids as $id){
    $conn->query("UPDATE $table SET shuffle_order = $order WHERE id = $id");
    $order++;
}

echo json_encode(['success'=>true]);
exit;