<?php
$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];
$table = $_GET['table'] ?? 'pmk';

if (!in_array($table, $allowed_tables)) {
    $table = 'pmk';
}

require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

include 'config.php';

if (isset($_POST['import'])) {

    $fileName = $_FILES['file']['tmp_name'];

    $spreadsheet = IOFactory::load($fileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    $header = array_map(fn($h) => strtolower(trim($h)), $sheetData[0]);

    $col_nama = array_search('nama', $header);
    $col_nik  = array_search('nik', $header);

    if ($col_nama === false || $col_nik === false) {
        die("Kolom NAMA atau NIK tidak ditemukan!");
    }

    for ($i = 1; $i < count($sheetData); $i++) {

        $row = $sheetData[$i];

        $nama = trim($row[$col_nama] ?? '');
        $nik  = trim($row[$col_nik] ?? '');

        if ($nama == '' && $nik == '') continue;
        if ($nik == '') continue;

        $check = $conn->prepare("SELECT nik FROM $table WHERE nik = ?");
        $check->bind_param("s", $nik);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) continue;

        $stmt = $conn->prepare("INSERT INTO $table (nama, nik) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $nik);
        $stmt->execute();
    }

    echo "<script>alert('Import berhasil!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>NIK System</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f9;
    padding: 20px;
}

/* BUTTON GROUP */
.btn-group a {
    text-decoration: none;
}

.btn {
    padding: 8px 16px;
    margin-right: 5px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    background: #ddd;
    transition: 0.3s;
}

.btn:hover {
    background: #bbb;
}

.active {
    background: #4CAF50;
    color: white;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    table-layout: fixed; /* IMPORTANT */
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

/* FIX COLUMN WIDTH */
th:nth-child(1), td:nth-child(1) { width: 50px; }
th:nth-child(2), td:nth-child(2) { width: 250px; }
th:nth-child(3), td:nth-child(3) { width: 200px; }
th:nth-child(4), td:nth-child(4) { width: 120px; }
th:nth-child(5), td:nth-child(5) { width: 100px; }

/* TEXT CUT */
td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* COPY BUTTON */
.copy-btn {
    background: #2196F3;
    color: white;
    padding: 5px 10px;
    width:100%;
    border-radius: 6px;
}

.copy-btn:hover {
    background: #0b7dda;
}

/* USED */
.used {
    background: #d4edda;
}
</style>

</head>
<body>

<h2>NIK Copy System</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit" name="import">Import</button>
</form>

<br>

<!-- BUTTON TABLE SWITCH -->
<div class="btn-group">
    <a href="?table=pmk"><button class="btn <?= $table=='pmk'?'active':'' ?>">PMK</button></a>
    <a href="?table=pb"><button class="btn <?= $table=='pb'?'active':'' ?>">PB</button></a>
    <a href="?table=sbk"><button class="btn <?= $table=='sbk'?'active':'' ?>">SBK</button></a>
    <a href="?table=serumpun"><button class="btn <?= $table=='serumpun'?'active':'' ?>">SERUMPUN</button></a>
</div>

<br>

<table>
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>NIK</th>
    <th>Action</th>
    <th>Total Use</th>
</tr>

<?php
$today = date("Y-m-d");
$month = date("m");
$year = date("Y");

$query = "SELECT * FROM $table ORDER BY disabled_until IS NOT NULL, id ASC";
$result = $conn->query($query);

$i = 1;

while($row = $result->fetch_assoc()) {

    if ($row['last_used_month'] != $month || $row['last_used_year'] != $year) {
        $conn->query("UPDATE $table 
            SET total_use = 0,
                last_used_month = $month,
                last_used_year = $year
            WHERE id = ".$row['id']);
        $row['total_use'] = 0;
    }

    $disabled = ($row['disabled_until'] && $row['disabled_until'] > $today);
?>

<tr class="<?= $disabled ? 'used' : '' ?>">
    <td><?= $i++ ?></td>
    <td title="<?= $row['nama'] ?>"><?= $row['nama'] ?></td>
    <td><?= $row['nik'] ?></td>

    <td>
        <?php if(!$disabled): ?>
            <button class="copy-btn" onclick="copyNIK('<?= $row['nik'] ?>', <?= $row['id'] ?>, '<?= $table ?>')">
                Copy
            </button>
        <?php else: ?>
            <span>Used</span>
        <?php endif; ?>
    </td>

    <td><?= $row['total_use'] ?></td>
</tr>

<?php } ?>
</table>

<script>
function copyNIK(nik, id, table) {
    navigator.clipboard.writeText(nik);

    fetch(`update.php?id=${id}&table=${table}`)
    .then(() => location.reload());
}
</script>

</body>
</html>