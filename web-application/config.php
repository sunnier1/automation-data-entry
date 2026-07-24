<?php $conn = new mysqli("localhost", "root", "", "nik_app");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} ?>