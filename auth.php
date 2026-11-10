<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fungsi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama    = $_SESSION['nama'];
$role    = $_SESSION['role'];

$id_siswa = null;

if ($role === 'siswa') {

    $stmt = $pdo->prepare("
        SELECT id_siswa
        FROM siswa
        WHERE id_user = ?
    ");

    $stmt->execute([$id_user]);

    $dataSiswa = $stmt->fetch();

    if ($dataSiswa) {
        $id_siswa = $dataSiswa['id_siswa'];
    }
}

$pesan = ambilPesan();