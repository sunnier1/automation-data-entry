<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

include 'config.php';

$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];

$table = $_POST['table'] ?? 'pmk';

if(!in_array($table, $allowed_tables)){
    die("Invalid table");
}






/* ======================================================
   MANUAL INPUT
====================================================== */

if(isset($_POST['manual'])){

    $nama = trim($_POST['nama']);
    $nik  = trim($_POST['nik']);

    // remove spaces
    $nik = preg_replace('/\s+/', '', $nik);

    // CHECK DUPLICATE
    $check = $conn->prepare("
        SELECT id FROM $table
        WHERE nik = ?
    ");

    $check->bind_param("s", $nik);
    $check->execute();

    $result = $check->get_result();

    if($result->num_rows > 0){

        echo "
        <script>
            alert('Duplicate NIK detected!');
            history.back();
        </script>
        ";

        exit;
    }

    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO $table (nama, nik)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ss", $nama, $nik);

    if($stmt->execute()){

        $_SESSION['toast'] = "Data added successfully!";

        header("Location: index.php?table=$table");
        exit;

    }else{

        $_SESSION['toast'] = "Duplicate NIK detected!";

        header("Location: add-data.php?table=$table");
        exit;
    }

}






/* ======================================================
   IMPORT EXCEL
====================================================== */

if(isset($_POST['import'])){

    $fileName = $_FILES['file']['tmp_name'];

    $spreadsheet = IOFactory::load($fileName);

    $sheetData = $spreadsheet
        ->getActiveSheet()
        ->toArray();

    // HEADER
    $header = $sheetData[0];

    $header = array_map(
        fn($h) => strtolower(trim($h)),
        $header
    );

    $col_nama = array_search('nama', $header);
    $col_nik  = array_search('nik', $header);

    if($col_nama === false || $col_nik === false){

        die("Column nama or nik not found!");

    }

    $inserted = 0;
    $duplicate = 0;

    // LOOP
    for($i = 1; $i < count($sheetData); $i++){

        $row = $sheetData[$i];

        $nama = trim($row[$col_nama] ?? '');
        $nik  = trim($row[$col_nik] ?? '');

        // remove spaces
        $nik = preg_replace('/\s+/', '', $nik);

        // skip empty
        if($nik == '') continue;

        // CHECK DUPLICATE
        $check = $conn->prepare("
            SELECT id FROM $table
            WHERE nik = ?
        ");

        $check->bind_param("s", $nik);

        $check->execute();

        $result = $check->get_result();

        if($result->num_rows > 0){

            $duplicate++;
            continue;

        }

        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO $table (nama, nik)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ss", $nama, $nik);

        if($stmt->execute()){

            $inserted++;

        }

    }

    $_SESSION['toast'] =
    "Import finished | Inserted: $inserted | Duplicate skipped: $duplicate";

    header("Location: index.php?table=$table");
    exit;

}
?>