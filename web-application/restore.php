<?php
include 'config.php';

$id = (int)$_GET['id'];
$table = $_GET['table'];

$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];

if(!in_array($table, $allowed_tables)){
    die(json_encode([
        "success" => false
    ]));
}

// decrease total_use
$conn->query("
    UPDATE $table
    SET
        total_use = GREATEST(total_use - 1, 0),
        disabled_until = NULL
    WHERE id = $id
");

// get updated data
$result = $conn->query("
    SELECT total_use, nik
    FROM $table
    WHERE id = $id
");

$row = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "total_use" => $row['total_use'],
    "nik" => $row['nik']
]);
?>