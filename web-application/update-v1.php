<?php
include 'config.php';

$id = $_GET['id'];
$table = $_GET['table'];

$today = date("Y-m-d");
$month = date("m");
$year = date("Y");

/* 1. Get current data */
$res = $conn->query("SELECT total_use, last_used_month, last_used_year FROM $table WHERE id = $id");
$row = $res->fetch_assoc();

$total_use = $row['total_use'];

/* 2. Reset if new month */
if ($row['last_used_month'] != $month || $row['last_used_year'] != $year) {
    $total_use = 0;
}

/* 3. Increase usage */
$total_use++;

/* 4. Move to bottom (IMPORTANT 🔥) */
$maxOrder = $conn->query("SELECT MAX(shuffle_order) as max_order FROM $table")->fetch_assoc()['max_order'];
$newOrder = $maxOrder ? $maxOrder + 1 : 1;

/* 5. Update database */
$conn->query("
    UPDATE $table 
    SET 
        total_use = $total_use,
        last_used_month = $month,
        last_used_year = $year,
        shuffle_order = $newOrder,
        disabled_until = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    WHERE id = $id
");

/* 6. Return JSON */
echo json_encode([
    "success" => true,
    "total_use" => $total_use
]);