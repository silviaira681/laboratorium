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

$pesan = ambilPesan();

/* Ambil data alat */
$stmt = $pdo->query("
    SELECT *
    FROM alat
    ORDER BY id_alat DESC
");

$alat = $stmt->fetchAll();

/* Statistik */
$totalAlat = $pdo->query("
    SELECT COUNT(*) FROM alat
")->fetchColumn();

$totalStok = $pdo->query("
    SELECT COALESCE(SUM(jumlah),0) FROM alat
")->fetchColumn();

$alatBaik = $pdo->query("
    SELECT COUNT(*)
    FROM alat
    WHERE LOWER(kondisi) = 'baik'
")->fetchColumn();

$alatRusak = $pdo->query("
    SELECT COUNT(*)
    FROM alat
    WHERE LOWER(kondisi) != 'baik'
")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Kelola Alat - Laboratorium</title>

<style>

* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body {
    background:#f4f7fb;
    color:#1e293b;
}

/* SIDEBAR */

.sidebar {
    position:fixed;
    left:0;
    top:0;
    width:250px;
    height:100vh;
    background:linear-gradient(180deg,#0f4c81,#1769aa,#0d5a91);
    color:white;
    padding:25px 15px;
    box-shadow:4px 0 20px rgba(0,0,0,.08);
}

.logo {
    display:flex;
    align-items:center;
    gap:12px;
    padding:5px 10px 25px;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.logo-icon {
    width:45px;
    height:45px;
    background:rgba(255,255,255,.18);
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
}

.logo h2 {
    font-size:17px;
}

.logo p {
    font-size:11px;
    color:rgba(255,255,255,.7);
    margin-top:4px;
}

.profile {
    margin:20px 5px;
    padding:14px;
    border-radius:13px;
    background:rgba(255,255,255,.1);
    display:flex;
    align-items:center;
    gap:10px;
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
    color:rgba(255,255,255,.7);
}

.menu-title {
    font-size:10px;
    color:rgba(255,255,255,.5);
    margin:20px 12px 8px;
    text-transform:uppercase;
    letter-spacing:1px;
}

.menu a {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 13px;
    border-radius:10px;
    color:rgba(255,255,255,.85);
    text-decoration:none;
    font-size:13px;
    margin-bottom:4px;
    transition:.2s;
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

/* MAIN */

.main {
    margin-left:250px;
    min-height:100vh;
}

.topbar {
    height:75px;
    background:white;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 35px;
}

.topbar h1 {
    font-size:20px;
}

.topbar p {
    font-size:12px;
    color:#94a3b8;
    margin-top:4px;
}

.date {
    background:#f1f5f9;
    padding:9px 14px;
    border-radius:9px;
    color:#64748b;
    font-size:12px;
}

/* CONTENT */

.content {
    padding:30px 35px;
}

.page-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.page-header h2 {
    font-size:22px;
}

.page-header p {
    color:#94a3b8;
    font-size:12px;
    margin-top:5px;
}

.btn-add {
    background:#1769aa;
    color:white;
    text-decoration:none;
    border:none;
    padding:11px 17px;
    border-radius:10px;
    font-size:12px;
    cursor:pointer;
}

.btn-add:hover {
    background:#0f568c;
}

/* STATISTICS */

.stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:17px;
    margin-bottom:25px;
}

.stat {
    background:white;
    padding:20px;
    border-radius:15px;
    border:1px solid #edf0f4;
    display:flex;
    align-items:center;
    gap:13px;
}

.stat-icon {
    width:47px;
    height:47px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eaf4ff;
    font-size:21px;
}

.stat:nth-child(2) .stat-icon {
    background:#eefcf5;
}

.stat:nth-child(3) .stat-icon {
    background:#fff7e6;
}

.stat:nth-child(4) .stat-icon {
    background:#fff0f0;
}

.stat small {
    color:#94a3b8;
    font-size:11px;
}

.stat strong {
    display:block;
    font-size:23px;
    margin-top:4px;
}

/* TABLE CARD */

.table-card {
    background:white;
    border-radius:16px;
    border:1px solid #edf0f4;
    overflow:hidden;
}

.table-top {
    padding:20px;
    border-bottom:1px solid #edf0f4;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.table-top h3 {
    font-size:15px;
}

.search {
    width:240px;
    padding:10px 13px;
    border:1px solid #dbe2ea;
    border-radius:9px;
    outline:none;
    font-size:12px;
}

.search:focus {
    border-color:#1769aa;
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
    color:#64748b;
    font-size:11px;
    text-align:left;
    padding:14px 18px;
    white-space:nowrap;
}

td {
    padding:15px 18px;
    border-top:1px solid #f0f2f5;
    font-size:12px;
    vertical-align:middle;
}

tr:hover td {
    background:#fafcff;
}

.tool-name {
    font-weight:bold;
    color:#1e293b;
}

.badge {
    display:inline-block;
    padding:5px 9px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
}

.badge-baik {
    background:#dcfce7;
    color:#15803d;
}

.badge-rusak {
    background:#fee2e2;
    color:#b91c1c;
}

.badge-stock {
    background:#eaf4ff;
    color:#1769aa;
}

.action {
    display:flex;
    gap:6px;
}

.btn-delete {
    background:#fee2e2;
    color:#dc2626;
    border:none;
    padding:7px 10px;
    border-radius:7px;
    text-decoration:none;
    font-size:11px;
}

.btn-delete:hover {
    background:#fecaca;
}

/* EMPTY */

.empty {
    text-align:center;
    padding:50px 20px;
    color:#94a3b8;
}

.empty-icon {
    font-size:40px;
    margin-bottom:10px;
}

/* MODAL */

.modal {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.5);
    align-items:center;
    justify-content:center;
    z-index:2000;
}

.modal-box {
    width:450px;
    max-width:90%;
    background:white;
    border-radius:18px;
    padding:25px;
}

.modal-box h2 {
    font-size:18px;
    margin-bottom:5px;
}

.modal-box p {
    font-size:11px;
    color:#94a3b8;
    margin-bottom:20px;
}

.form-group {
    margin-bottom:14px;
}

.form-group label {
    display:block;
    font-size:11px;
    font-weight:bold;
    margin-bottom:6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width:100%;
    padding:10px;
    border:1px solid #dbe2ea;
    border-radius:8px;
    outline:none;
    font-size:12px;
}

.form-group textarea {
    resize:vertical;
    min-height:70px;
}

.modal-buttons {
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:20px;
}

.btn-cancel {
    border:none;
    background:#f1f5f9;
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
}

.btn-save {
    border:none;
    background:#1769aa;
    color:white;
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
}

/* RESPONSIVE */

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

    .page-header {
        align-items:flex-start;
        gap:15px;
        flex-direction:column;
    }

    .search {
        width:180px;
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

        <a href="index.php?page=alat" class="active">
            <span class="menu-icon">💻</span>
            Kelola Alat
        </a>

        <a href="index.php?page=peminjaman">
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
        <h1>Kelola Alat</h1>
        <p>Kelola seluruh data alat laboratorium</p>
    </div>

    <div class="date">
        📅 <?= date('d F Y') ?>
    </div>

</header>


<section class="content">

    <div class="page-header">

        <div>
            <h2>Data Alat Laboratorium</h2>
            <p>Tambah, lihat, dan kelola alat yang tersedia</p>
        </div>

        <button class="btn-add" onclick="openModal()">
            ＋ Tambah Alat
        </button>

    </div>


    <div class="stats">

        <div class="stat">
            <div class="stat-icon">💻</div>
            <div>
                <small>Jenis Alat</small>
                <strong><?= $totalAlat ?></strong>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon">📦</div>
            <div>
                <small>Total Stok</small>
                <strong><?= $totalStok ?></strong>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon">✅</div>
            <div>
                <small>Kondisi Baik</small>
                <strong><?= $alatBaik ?></strong>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon">⚠️</div>
            <div>
                <small>Perlu Perhatian</small>
                <strong><?= $alatRusak ?></strong>
            </div>
        </div>

    </div>


    <div class="table-card">

        <div class="table-top">

            <h3>Daftar Alat</h3>

            <input
                type="text"
                id="search"
                class="search"
                placeholder="🔍 Cari alat..."
                onkeyup="cariAlat()"
            >

        </div>


        <div class="table-wrapper">

            <table id="tableAlat">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Kondisi</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$alat): ?>

                    <tr>
                        <td colspan="7">

                            <div class="empty">

                                <div class="empty-icon">📦</div>

                                Belum ada data alat.

                            </div>

                        </td>
                    </tr>

                <?php else: ?>

                    <?php $no = 1; ?>

                    <?php foreach ($alat as $item): ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td class="tool-name">
                            <?= e($item['nama_alat']) ?>
                        </td>

                        <td>
                            <?= e($item['kategori']) ?>
                        </td>

                        <td>
                            <span class="badge badge-stock">
                                <?= e($item['jumlah']) ?> unit
                            </span>
                        </td>

                        <td>

                            <?php if (strtolower($item['kondisi']) === 'baik'): ?>

                                <span class="badge badge-baik">
                                    ✓ Baik
                                </span>

                            <?php else: ?>

                                <span class="badge badge-rusak">
                                    ⚠ <?= e($item['kondisi']) ?>
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= e($item['deskripsi']) ?>
                        </td>

                        <td>

                            <div class="action">

                                <a
                                    class="btn-delete"
                                    href="proses/hapus_alat.php?id=<?= $item['id_alat'] ?>"
                                    onclick="return confirm('Yakin ingin menghapus alat ini?')"
                                >
                                    🗑 Hapus
                                </a>

                            </div>

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


<!-- MODAL TAMBAH -->

<div class="modal" id="modal">

    <div class="modal-box">

        <h2>Tambah Alat</h2>

        <p>Masukkan informasi alat laboratorium.</p>

        <form action="proses/tambah_alat.php" method="POST">

            <div class="form-group">

                <label>Nama Alat</label>

                <input
                    type="text"
                    name="nama_alat"
                    required
                >

            </div>

            <div class="form-group">

                <label>Kategori</label>

                <input
                    type="text"
                    name="kategori"
                    required
                >

            </div>

            <div class="form-group">

                <label>Jumlah</label>

                <input
                    type="number"
                    name="jumlah"
                    min="1"
                    required
                >

            </div>

            <div class="form-group">

                <label>Kondisi</label>

                <select name="kondisi" required>

                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                    <option value="Rusak Berat">Rusak Berat</option>

                </select>

            </div>

            <div class="form-group">

                <label>Deskripsi</label>

                <textarea name="deskripsi"></textarea>

            </div>

            <div class="modal-buttons">

                <button
                    type="button"
                    class="btn-cancel"
                    onclick="closeModal()"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-save"
                >
                    💾 Simpan Alat
                </button>

            </div>

        </form>

    </div>

</div>


<script>

function openModal() {
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function cariAlat() {

    let input =
        document.getElementById('search').value.toLowerCase();

    let rows =
        document.querySelectorAll('#tableAlat tbody tr');

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