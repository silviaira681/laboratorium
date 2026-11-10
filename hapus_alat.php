<?php

session_start();

require_once '../config.php';
require_once '../fungsi.php';

if (!isset($_SESSION['id_user'])) {
    redirect('../login.php');
}

if ($_SESSION['role'] !== 'laboran') {
    redirect('../index.php?page=dashboard');
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    pesan('error', 'ID alat tidak valid.');

    redirect('../index.php?page=alat');
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM alat
        WHERE id_alat = ?
    ");

    $stmt->execute([$id]);

    pesan('success', 'Data alat berhasil dihapus.');

} catch (PDOException $e) {

    pesan(
        'error',
        'Alat tidak dapat dihapus karena masih digunakan pada data peminjaman.'
    );
}

redirect('../index.php?page=alat');