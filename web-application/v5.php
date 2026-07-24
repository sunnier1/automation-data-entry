<?php
// manage web domain: https://dash.infinityfree.com/accounts/if0_41570053/databases
//online web: https://inputlala.infinityfreeapp.com/?table=pmk
include 'config.php';
$allowed_tables = ['pb', 'pmk', 'sbk', 'serumpun'];
$table = $_GET['table'] ?? 'pmk';

$table_names = [
    'pmk' => 'Anton',
    'pb' => 'Hariadi',
    'sbk' => 'Ofendi',
    'serumpun' => 'Saari'
];

$display_name = $table_names[$table];


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
        background: #ff0000;
        color: white;
        font-weight: 600;
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
    .id-cell {
        position: relative;
    }
    .edit-btn {
        position: absolute;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        opacity: 0;
        transition: 0.2s;
    }

    .id-cell:hover .edit-btn,
    .id-cell:hover .delete-btn {
        opacity: 1;
    }

    .delete-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        opacity: 0;
        transition: 0.2s;
    }

    .id-cell:hover .delete-btn {
        opacity: 1;
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

    .shuffle-btn { background:#673ab7; color:white; }
    .shuffle-btn:active {
        transform: scale(0.95);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
	}

    /* MODAL BACKGROUND */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
    }

    /* MODAL BOX */
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        margin: 15% auto;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    /* LOADING OVERLAY */
    .loading-overlay {
        display: none;
        position: fixed;
        z-index: 2000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        color: white;
        text-align: center;
        padding-top: 20%;
    }

    /* SPINNER */
    .loader {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #4CAF50;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin: auto;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
    <div class="btn-group">
        <button class="btn shuffle-btn" onclick="openModal()">Shuffle</button>
        <button class="clear-btn" onclick="clearClipboard()">Clear Copy</button>
     </div>
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
    (disabled_until IS NOT NULL AND disabled_until > CURDATE()) ASC,
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
    <td class="id-cell">
        <?= $row['id'] ?>
        <span class="edit-btn"
            onclick="openEditModal(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['nik'], ENT_QUOTES) ?>',
                this
            )">
            ✏️
        </span>

        <span class="delete-btn" 
            onclick="openDeleteModal(
                <?= $row['id'] ?>, 
                '<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>',
                '<?= $row['nik']?>',
                this
            )">
            🗑️
        </span>
    </td>
    <td title="<?= $row['nama'] ?>"><?= $row['nama'] ?></td>
    <td>
    <?php
        $cleanNik = preg_replace('/\D/', '', $row['nik']);

        $formattedNik = trim(chunk_split($cleanNik, 6, ' '));

        echo $formattedNik;
    ?>
    </td>

    <td>
        <?php if(!$disabled): ?>

            <button class="copy-btn"
                onclick="copyNIK(this, '<?= $row['nik'] ?>', <?= $row['id'] ?>, '<?= $table ?>')">
                Copy
            </button>

        <?php else: ?>

            <button class="btn"
                style="background:#ff9800; color:white; width:100%;"
                onclick="undoUsed(this, <?= $row['id'] ?>, '<?= $table ?>')">
                Undo
            </button>

        <?php endif; ?>
    </td>

    <td><?= $row['total_use'] ?></td>
</tr>
<?php } ?>
</tbody>

</table>

<script>
function showToast(message){
    const toast = document.getElementById("info");
    toast.innerText = message;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 1500);
}
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


function openModal(){
    document.getElementById("shuffleModal").style.display = "block";
}

function closeModal(){
    document.getElementById("shuffleModal").style.display = "none";
}
function confirmShuffle(){

    closeModal();

    // 🔥 SHOW LOADING OVERLAY IMMEDIATELY
    document.getElementById("loadingOverlay").style.display = "block";

    fetch(`shuffle.php?table=<?= $table ?>`)
    .then(res => res.text())
    .then(text => {

        try {
            const data = JSON.parse(text);

            // 🔥 HIDE LOADING
            document.getElementById("loadingOverlay").style.display = "none";

            if(data.success){
                showToast("Shuffled <?= $display_name ?> successfully!");

                setTimeout(() => {
                    location.reload();
                }, 1200);
            } else {
                showToast("Shuffle failed!");
            }

        } catch (e) {
            document.getElementById("loadingOverlay").style.display = "none";
            showToast("Shuffle done!");
        }
    })
    .catch(() => {
        document.getElementById("loadingOverlay").style.display = "none";
        showToast("Network error!");
    });
}
let deleteId = null;
let deleteRow = null;

function openDeleteModal(id, nama, nik, el){
    deleteId = id;
    deleteRow = el.closest("tr");
    

    // 🔥 tampilkan ID + NAMA
    document.getElementById("deleteText").innerHTML =
    `Delete data:<br>
     ${id}<br>
     <b>${nama}</b><br>
     <b>${nik}</b>`;

    document.getElementById("deleteModal").style.display = "block";
}
function closeDeleteModal(){
    document.getElementById("deleteModal").style.display = "none";
}

function confirmDelete(){
    closeDeleteModal();

    fetch(`delete.php?id=${deleteId}&table=<?= $table ?>`)
    .then(res => res.json())
    .then(data => {
        if(data.success){

            // 🔥 HAPUS ROW TANPA RELOAD
            deleteRow.remove();

            // 🔥 TOAST 3 DETIK
            showToast("ID berhasil dihapus");
            
        } else {
            showToast("Gagal hapus data");
        }
    })
    .catch(() => {
        showToast("Network error");
    });
}
let currentEditRow = null;
let originalId = null;

function openEditModal(id, nama, nik, el){

    currentEditRow = el.closest("tr");
    originalId = id;

    document.getElementById("editId").value = id;
    document.getElementById("editNama").value = nama;
    document.getElementById("editNik").value = nik;
    document.getElementById("editPassword").value = "";

    document.getElementById("editModal").style.display = "block";
}

function closeEditModal(){
    document.getElementById("editModal").style.display = "none";
}

function confirmEdit(){

    const password = document.getElementById("editPassword").value;

    if(password !== "224488"){
        showToast("Wrong password");
        return;
    }

    const newId = document.getElementById("editId").value;
    const newNama = document.getElementById("editNama").value;
    const newNik = document.getElementById("editNik").value;

    fetch(`edit.php`, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
            `table=<?= $table ?>` +
            `&old_id=${originalId}` +
            `&new_id=${newId}` +
            `&nama=${encodeURIComponent(newNama)}` +
            `&nik=${encodeURIComponent(newNik)}`
    })
    .then(res => res.json())
    .then(data => {

        if(data.success){

            // UPDATE TABLE INSTANTLY
            currentEditRow.cells[1].innerHTML = `
                ${newId}

                <span class="edit-btn"
                    onclick="openEditModal(
                        ${newId},
                        '${newNama}',
                        '${newNik}',
                        this
                    )">✏️</span>

                <span class="delete-btn"
                    onclick="openDeleteModal(
                        ${newId},
                        '${newNama}',
                        '${newNik}',
                        this
                    )">🗑️</span>
            `;

            currentEditRow.cells[2].innerText = newNama;
            currentEditRow.cells[3].innerText = newNik;

            closeEditModal();

            showToast("Data updated successfully");

        } else {
            showToast("Update failed");
        }

    })
    .catch(() => {
        showToast(data.message);
    });
}
function undoUsed(btn, id, table){

    const row = btn.closest("tr");
    const tbody = row.parentElement;

    fetch(`undo.php?id=${id}&table=${table}`)
    .then(res => res.json())
    .then(data => {

        if(data.success){

            // REMOVE USED STYLE
            row.classList.remove("used");

            // CHANGE BUTTON BACK
            row.cells[4].innerHTML = `
                <button class="copy-btn"
                    onclick="copyNIK(this, '${row.cells[3].innerText}', ${id}, '${table}')">
                    Copy
                </button>
            `;

            // UPDATE TOTAL USE
            row.cells[5].innerText = data.total_use;

            // MOVE ROW TO TOP
            tbody.prepend(row);

            showToast("Usage restored");

        } else {
            showToast("Undo failed");
        }

    })
    .catch(() => {
        showToast("Network error");
    });
}
</script>
<div id="info" class="toast">Shuffled! Please Refresh the WEB</div>
<div id="toast" class="toast">Clipboard Cleared</div>
<div id="shuffleModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Shuffle</h3>
            <p>Are you sure you want to shuffle <b><?= $display_name ?></b> data?</p>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button class="btn" onclick="closeModal()">Cancel</button>
            <button class="btn shuffle-btn" onclick="confirmShuffle()">Confirm</button>
        </div>
    </div>
</div>
<div id="loadingOverlay" class="loading-overlay">
    <div class="loader"></div>
    <p>Shuffling data...</p>
</div>
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Delete Data</h3>
        <p id="deleteText">>Are you sure want to delete this ID?</p>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button class="btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn clear-btn" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>
<div id="editModal" class="modal">
    <div class="modal-content">

        <h3>Edit Data</h3>

        <input type="number" id="editId"
            placeholder="ID Number"
            style="width:100%; padding:8px; margin-bottom:10px;">

        <input type="text" id="editNama"
            placeholder="Nama"
            style="width:100%; padding:8px; margin-bottom:10px;">

        <input type="text" id="editNik"
            placeholder="NIK"
            style="width:100%; padding:8px; margin-bottom:10px;">

        <input type="password" id="editPassword"
            placeholder="Enter Password"
            style="width:100%; padding:8px; margin-bottom:10px;">

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button class="btn" onclick="closeEditModal()">Cancel</button>

            <button class="btn shuffle-btn" onclick="confirmEdit()">
                Save
            </button>
        </div>

    </div>
</div>
</body>
</html>