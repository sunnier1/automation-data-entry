<?php
// manage web domain: https://dash.infinityfree.com/accounts/if0_41570053/databases
//online web: https://inputlala.infinityfreeapp.com/?table=pmk
include 'config.php';
$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];
$table = $_GET['table'] ?? 'pmk';

if (!in_array($table, $allowed_tables)) {
    $table = 'pmk';
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

    /* FLEXIBLE COLUMN WIDTH */
    th, td {
        text-align: left;
    }

    /* SMALL FIXED COLUMNS */
    th:nth-child(1), td:nth-child(1) { width: 50px; text-align: center; }
    th:nth-child(5), td:nth-child(5),
    th:nth-child(6), td:nth-child(6) { width: 100px; text-align: center; }

    /* AUTO ADJUST TEXT COLUMNS */
    th:nth-child(2), td:nth-child(2),
    th:nth-child(3), td:nth-child(3),
    th:nth-child(4), td:nth-child(4) {
        width: auto;
    }
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
    /* TOP BAR FLEX */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; /* IMPORTANT for responsiveness */
        gap: 10px;
    }

    /* CLEAR BUTTON */
    .clear-btn {
        background: #f44336;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .clear-btn:hover {
        background: #d32f2f;
    }
    form input {
        outline: none;
    }

    form input:focus {
        border-color: #4CAF50;
    }
    /* RESPONSIVE (WHEN SCREEN SMALL) */
    @media (max-width: 600px) {
        .top-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .clear-btn {
            width: 100%;
        }
    }
    /* TOAST NOTIFICATION */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 999;
        pointer-events: none;
    }

    /* SHOW STATE */
    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>

</head>
<body>

<h2>NIK Copy System</h2>

<!-- BUTTON TABLE SWITCH -->
<div class="top-bar">
    <div class="btn-group">
        <a href="?table=pmk"><button class="btn <?= $table=='pmk'?'active':'' ?>">Anton</button></a>
        <a href="?table=pb"><button class="btn <?= $table=='pb'?'active':'' ?>">Hariadi</button></a>
        <a href="?table=sbk"><button class="btn <?= $table=='sbk'?'active':'' ?>">Ofendi</button></a>
        <a href="?table=serumpun"><button class="btn <?= $table=='serumpun'?'active':'' ?>">Saari</button></a>
    </div>

    <!-- NEW CLEAR BUTTON -->
    <button class="clear-btn" onclick="clearClipboard()">Clear Copy</button>
</div>

<br>
<form method="GET" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    
    <!-- KEEP TABLE -->
    <input type="hidden" name="table" value="<?= $table ?>">

    <input type="number" name="start" placeholder="Start (e.g 200)" 
        value="<?= $_GET['start'] ?? '' ?>" 
        style="padding:6px; border-radius:6px; border:1px solid #ccc; width:150px;">

    <input type="number" name="end" placeholder="Leave empty = no limit" 
        value="<?= $_GET['end'] ?? '' ?>" 
        style="padding:6px; border-radius:6px; border:1px solid #ccc; width:150px;">

    <button type="submit" class="btn" style="background:#4CAF50; color:white;">
        Filter
    </button>

    <!-- RESET BUTTON -->
    <a href="?table=<?= $table ?>">
        <button type="button" class="btn" style="background:#999; color:white;">
            Reset
        </button>
    </a>

</form>
<table>
<thead>
<tr>
    <th>No</th>
    <th>ID Number</th>
    <th>Nama</th>
    <th>NIK</th>
    <th>Action</th>
    <th>Total Use</th>
</tr>
</thead>
<tbody>
<?php
$today = date("Y-m-d");
$month = date("m");
$year = date("Y");

$query = "SELECT * FROM $table 
          ORDER BY 
          disabled_until IS NOT NULL,
          shuffle_order ASC,
          id ASC";
$result = $conn->query($query);

$start = isset($_GET['start']) ? (int)$_GET['start'] : null;
$end   = isset($_GET['end']) ? (int)$_GET['end'] : null;

$i = 1;

while($row = $result->fetch_assoc()) {

    // FILTER BY DISPLAY NUMBER (i++)
    if ($start && $i < $start) {
        $i++;
        continue;
    }

    if ($end && $i > $end) {
        break;
    }

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
    <td><?= $row['id'] ?></td>
    <td title="<?= $row['nama'] ?>"><?= $row['nama'] ?></td>
    <td><?= $row['nik'] ?></td>

    <td>
        <?php if(!$disabled): ?>
            <button class="copy-btn" 
onclick="copyNIK(this, '<?= $row['nik'] ?>', <?= $row['id'] ?>, '<?= $table ?>')">
                Copy
            </button>
        <?php else: ?>
            <span>Used</span>
        <?php endif; ?>
    </td>

    <td><?= $row['total_use'] ?></td>
</tr>
<?php } ?>
</tbody>

</table>

<script>
function clearClipboard() {
    navigator.clipboard.writeText("");

    const toast = document.getElementById("toast");
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 500); // 0.5 seconds
}

function copyNIK(btn, nik, id, table) {

    // 1. Copy instantly
    navigator.clipboard.writeText(nik);

    const row = btn.closest("tr");
    const tbody = row.parentElement;

    // 2. UI change instantly
    row.classList.add("used");
    btn.parentElement.innerHTML = "<span>Used</span>";

    // 3. Move row to bottom instantly
    tbody.appendChild(row);

    // 4. Update DB (IMPORTANT)
    fetch(`update.php?id=${id}&table=${table}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // update total_use column instantly
                row.cells[5].innerText = data.total_use;
            }
        })
        .catch(() => {
            console.log("Update failed");
        });
}
</script>
<div id="toast" class="toast">Clipboard Cleared</div>
</body>
</html>