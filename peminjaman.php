<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'fungsi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'laboran') {
    header("Location: index.php?page=dashboard");
    exit;
}

$stmt = $pdo->query("
    SELECT
        p.*,
        s.nis,
        s.kelas,
        u.nama,
        a.nama_alat,
        a.kategori
    FROM peminjaman p

    INNER JOIN siswa s
        ON s.id_siswa = p.id_siswa

    INNER JOIN users u
        ON u.id_user = s.id_user

    INNER JOIN alat a
        ON a.id_alat = p.id_alat

    ORDER BY p.id_peminjaman DESC
");

$data = $stmt->fetchAll();

$total = count($data);

$menunggu = $pdo->query("
    SELECT COUNT(*)
    FROM peminjaman
    WHERE status='Menunggu'
")->fetchColumn();

$disetujui = $pdo->query("
    SELECT COUNT(*)
    FROM peminjaman
    WHERE status='Disetujui'
")->fetchColumn();

$ditolak = $pdo->query("
    SELECT COUNT(*)
    FROM peminjaman
    WHERE status='Ditolak'
")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Peminjaman</title>

<style>

* {
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:Arial,sans-serif;
}

body {
    background:#f4f7fb;
    color:#1e293b;
}

.sidebar {
    position:fixed;
    width:250px;
    height:100vh;
    background:linear-gradient(180deg,#0f4c81,#1769aa,#0d5a91);
    padding:25px 15px;
    color:white;
}

.logo {
    display:flex;
    gap:12px;
    align-items:center;
    padding:5px 10px 25px;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.logo-icon {
    width:45px;
    height:45px;
    background:rgba(255,255,255,.15);
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.logo h2 {
    font-size:17px;
}

.logo p {
    font-size:10px;
    color:#cbdff0;
    margin-top:4px;
}

.profile {
    margin:20px 5px;
    padding:14px;
    border-radius:13px;
    background:rgba(255,255,255,.1);
    display:flex;
    gap:10px;
    align-items:center;
}

.profile-icon {
    width:40px;
    height:40px;
    border-radius:50%;
    background:white;
    color:#1769aa;
    display:flex;
    align-items:center;
    justify-content:center;
}

.profile strong {
    display:block;
    font-size:13px;
}

.profile span {
    font-size:11px;
    color:#cbdff0;
}

.menu-title {
    margin:20px 12px 8px;
    font-size:10px;
    color:#a8c5dc;
}

.menu a {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    color:#e7f1f8;
    text-decoration:none;
    border-radius:10px;
    font-size:13px;
    margin-bottom:4px;
}

.menu a:hover {
    background:rgba(255,255,255,.13);
}

.menu a.active {
    background:white;
    color:#1769aa;
    font-weight:bold;
}

.menu-icon {
    width:24px;
    text-align:center;
}

.logout {
    margin-top:15px;
    border-top:1px solid rgba(255,255,255,.15);
    padding-top:15px;
}

.logout a {
    color:#ffd4d4 !important;
}

.main {
    margin-left:250px;
    min-height:100vh;
}

.topbar {
    height:75px;
    background:white;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 35px;
}

.topbar h1 {
    font-size:20px;
}

.topbar p {
    color:#94a3b8;
    font-size:12px;
    margin-top:4px;
}

.date {
    padding:9px 14px;
    background:#f1f5f9;
    border-radius:9px;
    color:#64748b;
    font-size:12px;
}

.content {
    padding:30px 35px;
}

.page-title {
    margin-bottom:22px;
}

.page-title h2 {
    font-size:22px;
}

.page-title p {
    font-size:12px;
    color:#94a3b8;
    margin-top:5px;
}

.stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:17px;
    margin-bottom:25px;
}

.stat {
    background:white;
    padding:19px;
    border-radius:15px;
    border:1px solid #edf0f4;
    display:flex;
    gap:13px;
    align-items:center;
}

.icon {
    width:46px;
    height:46px;
    border-radius:12px;
    background:#eaf4ff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.stat:nth-child(2) .icon {
    background:#fff7df;
}

.stat:nth-child(3) .icon {
    background:#e7f9ef;
}

.stat:nth-child(4) .icon {
    background:#ffeaea;
}

.stat small {
    color:#94a3b8;
    font-size:10px;
}

.stat strong {
    display:block;
    font-size:22px;
    margin-top:3px;
}

.table-card {
    background:white;
    border-radius:16px;
    border:1px solid #edf0f4;
    overflow:hidden;
}

.table-header {
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #edf0f4;
}

.table-header h3 {
    font-size:15px;
}

.search {
    padding:10px 13px;
    width:230px;
    border:1px solid #dbe2ea;
    border-radius:9px;
    outline:none;
    font-size:12px;
}

.table-wrapper {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#f8fafc;
    padding:14px 16px;
    text-align:left;
    font-size:10px;
    color:#64748b;
    white-space:nowrap;
}

td {
    padding:15px 16px;
    border-top:1px solid #f0f2f5;
    font-size:11px;
    vertical-align:middle;
}

tr:hover td {
    background:#fafcff;
}

.student {
    font-weight:bold;
}

.student small {
    display:block;
    color:#94a3b8;
    margin-top:4px;
    font-weight:normal;
}

.status {
    padding:6px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
    display:inline-block;
}

.menunggu {
    background:#fef3c7;
    color:#a16207;
}

.disetujui {
    background:#dcfce7;
    color:#15803d;
}

.ditolak {
    background:#fee2e2;
    color:#b91c1c;
}

.dikembalikan {
    background:#ede9fe;
    color:#6d28d9;
}

.actions {
    display:flex;
    gap:5px;
    flex-wrap:wrap;
}

.btn {
    border:0;
    border-radius:7px;
    padding:7px 9px;
    font-size:10px;
    text-decoration:none;
    cursor:pointer;
}

.btn-approve {
    background:#dcfce7;
    color:#15803d;
}

.btn-reject {
    background:#fee2e2;
    color:#dc2626;
}

.waiting {
    color:#94a3b8;
    font-size:10px;
}

@media(max-width:1000px) {
    .stats {
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:700px) {

    .sidebar {
        position:relative;
        width:100%;
        height:auto;
    }

    .main {
        margin-left:0;
    }

    .stats {
        grid-template-columns:1fr;
    }

    .table-header {
        align-items:flex-start;
        gap:10px;
        flex-direction:column;
    }

}

</style>

</head>

<body>

<aside class="sidebar">

<div class="logo">
    <div class="logo-icon">🔬</div>
    <div>
        <h2>LABORATORIUM</h2>
        <p>Sistem Peminjaman Alat</p>
    </div>
</div>

<div class="profile">
    <div class="profile-icon">👤</div>
    <div>
        <strong><?= e($_SESSION['nama']) ?></strong>
        <span>Laboran</span>
    </div>
</div>

<div class="menu-title">Menu Utama</div>

<nav class="menu">

<a href="index.php?page=dashboard">
    <span class="menu-icon">🏠</span>
    Dashboard
</a>

<a href="index.php?page=alat">
    <span class="menu-icon">💻</span>
    Kelola Alat
</a>

<a href="index.php?page=peminjaman" class="active">
    <span class="menu-icon">📋</span>
    Data Peminjaman
</a>

<a href="index.php?page=pengembalian">
    <span class="menu-icon">↩️</span>
    Pengembalian
</a>

<a href="index.php?page=laporan">
    <span class="menu-icon">📊</span>
    Laporan
</a>

</nav>

<div class="logout">
<a href="index.php?logout=1">
    <span class="menu-icon">🚪</span>
    Logout
</a>
</div>

</aside>


<main class="main">

<header class="topbar">

<div>
<h1>Data Peminjaman</h1>
<p>Kelola pengajuan peminjaman alat dari siswa</p>
</div>

<div class="date">
📅 <?= date('d F Y') ?>
</div>

</header>


<section class="content">

<div class="page-title">

<h2>Daftar Peminjaman</h2>

<p>
Periksa, setujui, atau tolak pengajuan peminjaman.
</p>

</div>


<div class="stats">

<div class="stat">
<div class="icon">📋</div>
<div>
<small>Total Peminjaman</small>
<strong><?= $total ?></strong>
</div>
</div>

<div class="stat">
<div class="icon">⏳</div>
<div>
<small>Menunggu</small>
<strong><?= $menunggu ?></strong>
</div>
</div>

<div class="stat">
<div class="icon">✅</div>
<div>
<small>Disetujui</small>
<strong><?= $disetujui ?></strong>
</div>
</div>

<div class="stat">
<div class="icon">❌</div>
<div>
<small>Ditolak</small>
<strong><?= $ditolak ?></strong>
</div>
</div>

</div>


<div class="table-card">

<div class="table-header">

<h3>Data Pengajuan Siswa</h3>

<input
    class="search"
    id="search"
    placeholder="🔍 Cari siswa / alat..."
    onkeyup="searchTable()"
>

</div>


<div class="table-wrapper">

<table id="table">

<thead>

<tr>
<th>No</th>
<th>Siswa</th>
<th>Alat</th>
<th>Jumlah</th>
<th>Tanggal</th>
<th>Keperluan</th>
<th>Status</th>
<th>Aksi</th>
</tr>

</thead>

<tbody>

<?php if (!$data): ?>

<tr>
<td colspan="8" style="text-align:center;padding:50px;color:#94a3b8;">
📋<br><br>
Belum ada data peminjaman.
</td>
</tr>

<?php else: ?>

<?php $no=1; ?>

<?php foreach ($data as $row): ?>

<tr>

<td><?= $no++ ?></td>

<td class="student">

<?= e($row['nama']) ?>

<small>
NIS: <?= e($row['nis']) ?> · <?= e($row['kelas']) ?>
</small>

</td>

<td>

<strong><?= e($row['nama_alat']) ?></strong>

<br>

<small style="color:#94a3b8;">
<?= e($row['kategori']) ?>
</small>

</td>

<td>
<?= e($row['jumlah']) ?> unit
</td>

<td>

<?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?>

<br>

<small style="color:#94a3b8;">
s/d <?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?>
</small>

</td>

<td>
<?= e($row['keperluan']) ?>
</td>

<td>

<?php

$status = strtolower($row['status']);

if ($status === 'menunggu') {
    echo '<span class="status menunggu">⏳ Menunggu</span>';
}
elseif ($status === 'disetujui') {
    echo '<span class="status disetujui">✓ Disetujui</span>';
}
elseif ($status === 'ditolak') {
    echo '<span class="status ditolak">✕ Ditolak</span>';
}
elseif ($status === 'dikembalikan') {
    echo '<span class="status dikembalikan">↩ Dikembalikan</span>';
}
else {
    echo '<span class="status">'.$status.'</span>';
}

?>

</td>

<td>

<?php if ($row['status'] === 'Menunggu'): ?>

<div class="actions">

<a
href="proses/setujui.php?id=<?= $row['id_peminjaman'] ?>"
class="btn btn-approve"
onclick="return confirm('Setujui peminjaman ini?')"
>
✓ Setujui
</a>

<a
href="proses/tolak.php?id=<?= $row['id_peminjaman'] ?>"
class="btn btn-reject"
onclick="return confirm('Tolak peminjaman ini?')"
>
✕ Tolak
</a>

</div>

<?php elseif ($row['status'] === 'Disetujui'): ?>

<span class="waiting">
Menunggu pengembalian
</span>

<?php else: ?>

<span class="waiting">
—
</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</section>

</main>


<script>

function searchTable() {

let input =
document.getElementById('search')
.value
.toLowerCase();

let rows =
document.querySelectorAll('#table tbody tr');

rows.forEach(function(row) {

let text =
row.innerText.toLowerCase();

row.style.display =
text.includes(input) ? '' : 'none';

});

}

</script>

</body>
</html>