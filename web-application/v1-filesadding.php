<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

include 'config.php';

if (isset($_POST['import'])) {

    $fileName = $_FILES['file']['tmp_name'];

    $spreadsheet = IOFactory::load($fileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray();

// ✅ STEP 1: GET HEADER
$header = $sheetData[0];

// Normalize header (lowercase)
$header = array_map(fn($h) => strtolower(trim($h)), $header);

// ✅ STEP 2: FIND COLUMN INDEX
$col_nama = array_search('nama', $header);
$col_nik  = array_search('nik', $header);

// ❗ Safety check
if ($col_nama === false || $col_nik === false) {
    die("Kolom NAMA atau NIK tidak ditemukan di Excel!");
}

// ✅ STEP 3: LOOP DATA (start from row 2)
for ($i = 1; $i < count($sheetData); $i++) {

    $row = $sheetData[$i];

    $nama = trim($row[$col_nama] ?? '');
    $nik  = trim($row[$col_nik] ?? '');

    // 🚫 Skip kosong
    if ($nama == '' && $nik == '') continue;
    if ($nik == '') continue;

    // 🚫 Check duplicate
    $check = $conn->prepare("SELECT nik FROM pmk WHERE nik = ?");
    $check->bind_param("s", $nik);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) continue;

    // ✅ Insert
    $stmt = $conn->prepare("INSERT INTO pmk (nama, nik) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $nik);
    $stmt->execute();
}

    echo "<script>alert('Import data berhasil!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NIK Copy System</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ccc; padding: 10px; }
        
        .disabled {
            background: #ddd;
            pointer-events: none;
        }

        .used {
            background: #c8f7c5;
        }
    </style>
</head>
<body>

<h2>Data NIK</h2>
<h2>Import Data Excel ke Database</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".xls,.xlsx" required>
    <button type="submit" name="import">Import Data</button>
</form>
<br>
</body>
</html>
