<?php
include 'config.php';

$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];

$table = $_GET['table'] ?? 'pmk';

if(!in_array($table, $allowed_tables)){
    die("Invalid table");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Data</title>

    <style>
        body{
            font-family:Arial;
            background:#f4f6f9;
            padding:20px;
        }

        .box{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:30px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }

        input, textarea{
            width:100%;
            padding:10px;
            margin-top:10px;
            margin-bottom:15px;
            border-radius:6px;
            border:1px solid #ccc;
            box-sizing:border-box;
        }

        button{
            padding:10px 16px;
            border:none;
            border-radius:8px;
            cursor:pointer;
            background:#4CAF50;
            color:white;
        }

        button:hover{
            background:#45a049;
        }

        .back-btn{
            background:#777;
        }

        .back-btn:hover{
            background:#555;
        }

        .note{
            color:#777;
            font-size:14px;
        }
    </style>
</head>
<body>

<a href="index.php?table=<?= $table ?>">
    <button class="back-btn">← Back</button>
</a>

<h2>Add New Data</h2>

<!-- ================= IMPORT FILE ================= -->
<div class="box">

    <h3>Import Excel File</h3>

    <p class="note">
        Excel must contain columns: <b>nama</b> and <b>nik</b>
    </p>

    <form action="process-add.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="table" value="<?= $table ?>">

        <input type="file" name="file" accept=".xls,.xlsx" required>

        <button type="submit" name="import">
            Import File
        </button>

    </form>

</div>

<!-- ================= MANUAL INPUT ================= -->
<div class="box">

    <h3>Manual Input</h3>

    <form action="process-add.php" method="POST">

        <input type="hidden" name="table" value="<?= $table ?>">

        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>NIK</label>
        <input type="text" name="nik" required>

        <button type="submit" name="manual">
            Save Data
        </button>

    </form>

</div>

</body>
</html>