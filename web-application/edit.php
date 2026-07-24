<?php
include 'config.php';

$table = $_POST['table'];

$old_id = (int)$_POST['old_id'];
$new_id = (int)$_POST['new_id'];

$nama = trim($_POST['nama']);
$nik  = trim($_POST['nik']);

$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];

if(!in_array($table, $allowed_tables)){
    echo json_encode([
        "success" => false,
        "message" => "Invalid table"
    ]);
    exit;
}

/* =========================
   CHECK DUPLICATE ID
========================= */
$checkId = $conn->query("
    SELECT id 
    FROM $table 
    WHERE id = $new_id
    AND id != $old_id
");

if($checkId->num_rows > 0){
    echo json_encode([
        "success" => false,
        "message" => "ID already exists"
    ]);
    exit;
}

/* =========================
   CHECK DUPLICATE NIK
========================= */
$checkNik = $conn->query("
    SELECT nik
    FROM $table
    WHERE nik = '$nik'
    AND id != $old_id
");

if($checkNik->num_rows > 0){
    echo json_encode([
        "success" => false,
        "message" => "NIK already exists"
    ]);
    exit;
}

/* =========================
   UPDATE DATA
========================= */
$query = "
UPDATE $table
SET
    id = $new_id,
    nama = '$nama',
    nik = '$nik'
WHERE id = $old_id
";

if($conn->query($query)){

    echo json_encode([
        "success" => true
    ]);

}else{

    echo json_encode([
        "success" => false,
        "message" => $conn->error
    ]);
}
?>