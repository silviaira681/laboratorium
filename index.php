<?php

session_start();

require_once 'config.php';
require_once 'fungsi.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

$halaman = [
    'dashboard'      => 'dashboard.php',
    'alat'            => 'alat.php',
    'alat_siswa'     => 'alat_siswa.php',
    'pinjam'         => 'pinjam.php',
    'riwayat'        => 'riwayat.php',
    'peminjaman'     => 'peminjaman.php',
    'pengembalian'   => 'pengembalian.php',
    'laporan'         => 'laporan.php'
];

if (isset($halaman[$page])) {
    require $halaman[$page];
} else {
    require 'dashboard.php';
}