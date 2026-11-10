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

$nama = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? 'siswa';

/* =========================
   DATA STATISTIK
========================= */

$total_alat = 0;
$total_siswa = 0;
$total_peminjaman = 0;
$total_dipinjam = 0;

try {

    $stmt = $pdo->query("SELECT COUNT(*) FROM alat");
    $total_alat = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM siswa");
    $total_siswa = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM peminjaman");
    $total_peminjaman = $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(jumlah), 0)
        FROM peminjaman
        WHERE status = 'Disetujui'
    ");
    $total_dipinjam = $stmt->fetchColumn();

} catch (PDOException $e) {

    $total_alat = 0;
    $total_siswa = 0;
    $total_peminjaman = 0;
    $total_dipinjam = 0;
}

/* =========================
   PEMINJAMAN TERBARU
========================= */

$peminjaman_terbaru = [];

try {

    $stmt = $pdo->query("
        SELECT
            p.id_peminjaman,
            p.jumlah,
            p.tanggal_pinjam,
            p.status,
            s.nis,
            u.nama,
            a.nama_alat
        FROM peminjaman p
        JOIN siswa s
            ON p.id_siswa = s.id_siswa
        JOIN users u
            ON s.id_user = u.id_user
        JOIN alat a
            ON p.id_alat = a.id_alat
        ORDER BY p.id_peminjaman DESC
        LIMIT 5
    ");

    $peminjaman_terbaru = $stmt->fetchAll();

} catch (PDOException $e) {

    $peminjaman_terbaru = [];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Sistem Laboratorium</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #1769e0, #0d47a1);
            color: white;
            padding: 25px 16px;
            z-index: 1000;
        }

        .logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            width: 55px;
            height: 55px;
            margin: auto;
            border-radius: 15px;
            background: rgba(255,255,255,.18);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .logo h2 {
            font-size: 18px;
            letter-spacing: .5px;
        }

        .logo p {
            font-size: 12px;
            opacity: .75;
            margin-top: 5px;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            opacity: .6;
            margin: 20px 12px 10px;
            letter-spacing: 1px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: .2s;
        }

        .menu a:hover,
        .menu a.active {
            background: rgba(255,255,255,.16);
        }

        .menu-icon {
            width: 25px;
            text-align: center;
            font-size: 17px;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 16px;
            right: 16px;
        }

        .logout a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border-radius: 9px;
            color: white;
            background: rgba(220,53,69,.9);
            text-decoration: none;
            font-size: 14px;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 250px;
            min-height: 100vh;
        }

        /* =========================
           TOPBAR
        ========================= */

        .topbar {
            height: 75px;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 35px;
            border-bottom: 1px solid #e5e7eb;
        }

        .topbar-left h3 {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .topbar-left p {
            font-size: 12px;
            color: #6b7280;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #e8f0ff;
            color: #1769e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 17px;
        }

        .profile-info strong {
            display: block;
            font-size: 14px;
        }

        .profile-info span {
            font-size: 11px;
            color: #6b7280;
        }

        /* =========================
           CONTENT
        ========================= */

        .content {
            padding: 30px 35px;
        }

        /* =========================
           WELCOME
        ========================= */

        .welcome {
            background: linear-gradient(110deg, #1769e0, #3988f5);
            color: white;
            border-radius: 18px;
            padding: 28px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            overflow: hidden;
            position: relative;
        }

        .welcome::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            right: -60px;
            top: -80px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
        }

        .welcome h1 {
            font-size: 25px;
            margin-bottom: 8px;
        }

        .welcome p {
            font-size: 14px;
            opacity: .9;
        }

        .welcome-button {
            position: relative;
            z-index: 2;
        }

        .welcome-button a {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 9px;
            background: white;
            color: #1769e0;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        /* =========================
           STATISTIC
        ========================= */

        .section-title {
            margin-bottom: 15px;
        }

        .section-title h2 {
            font-size: 18px;
        }

        .section-title p {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 21px;
            border-radius: 14px;
            border: 1px solid #e8edf3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,.07);
        }

        .stat-info span {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .stat-info h2 {
            font-size: 25px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eaf2ff;
            color: #1769e0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
        }

        /* =========================
           BOTTOM GRID
        ========================= */

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
        }

        .panel {
            background: white;
            border-radius: 14px;
            border: 1px solid #e8edf3;
            padding: 22px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .panel-header h3 {
            font-size: 16px;
        }

        .panel-header a {
            font-size: 12px;
            color: #1769e0;
            text-decoration: none;
        }

        /* =========================
           QUICK MENU
        ========================= */

        .quick-menu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .quick-menu a {
            padding: 18px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            text-decoration: none;
            color: #1f2937;
            transition: .2s;
        }

        .quick-menu a:hover {
            border-color: #1769e0;
            background: #f7faff;
        }

        .quick-icon {
            font-size: 23px;
            margin-bottom: 9px;
        }

        .quick-menu strong {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .quick-menu span {
            font-size: 10px;
            color: #6b7280;
        }

        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 10px;
            font-size: 11px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 12px 10px;
            font-size: 12px;
            border-bottom: 1px solid #f0f2f5;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-menunggu {
            background: #fff4d6;
            color: #9a6700;
        }

        .status-disetujui {
            background: #dcfce7;
            color: #15803d;
        }

        .status-ditolak {
            background: #fee2e2;
            color: #dc2626;
        }

        /* =========================
           EMPTY
        ========================= */

        .empty {
            text-align: center;
            padding: 35px 10px;
            color: #9ca3af;
            font-size: 12px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 750px) {

            .sidebar {
                width: 70px;
                padding: 20px 8px;
            }

            .logo h2,
            .logo p,
            .menu-title,
            .menu a span,
            .logout span {
                display: none;
            }

            .logo-icon {
                width: 45px;
                height: 45px;
            }

            .menu a {
                justify-content: center;
            }

            .logout {
                left: 8px;
                right: 8px;
            }

            .main {
                margin-left: 70px;
            }

            .content {
                padding: 20px;
            }

            .welcome {
                padding: 22px;
            }

            .welcome h1 {
                font-size: 20px;
            }

            .welcome-button {
                display: none;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .profile-info {
                display: none;
            }

            .topbar {
                padding: 0 20px;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🔬
        </div>

        <h2>LABORATORIUM</h2>

        <p>Sistem Peminjaman Alat</p>

    </div>

    <div class="menu">

        <div class="menu-title">
            Menu Utama
        </div>

        <a href="index.php?page=dashboard" class="active">

            <div class="menu-icon">🏠</div>

            <span>Dashboard</span>

        </a>

        <?php if ($role === 'laboran'): ?>

            <a href="index.php?page=alat">

                <div class="menu-icon">🔧</div>

                <span>Data Alat</span>

            </a>

            <a href="index.php?page=peminjaman">

                <div class="menu-icon">📋</div>

                <span>Peminjaman</span>

            </a>

            <a href="index.php?page=pengembalian">

                <div class="menu-icon">↩️</div>

                <span>Pengembalian</span>

            </a>

            <a href="index.php?page=laporan">

                <div class="menu-icon">📊</div>

                <span>Laporan</span>

            </a>

        <?php else: ?>

            <a href="index.php?page=alat_siswa">

                <div class="menu-icon">🔧</div>

                <span>Daftar Alat</span>

            </a>

            <a href="index.php?page=pinjam">

                <div class="menu-icon">📝</div>

                <span>Ajukan Peminjaman</span>

            </a>

            <a href="index.php?page=riwayat">

                <div class="menu-icon">📚</div>

                <span>Riwayat Peminjaman</span>

            </a>

        <?php endif; ?>

        <div class="menu-title">
            Akun
        </div>

    </div>

    <div class="logout">

        <a href="login.php?logout=1">

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>

</aside>


<!-- =========================
     MAIN
========================= -->

<main class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="topbar-left">

            <h3>Dashboard</h3>

            <p>
                Sistem Peminjaman Alat Laboratorium
            </p>

        </div>

        <div class="profile">

            <div class="profile-icon">

                <?= strtoupper(substr($nama, 0, 1)) ?>

            </div>

            <div class="profile-info">

                <strong>
                    <?= htmlspecialchars($nama) ?>
                </strong>

                <span>
                    <?= htmlspecialchars(ucfirst($role)) ?>
                </span>

            </div>

        </div>

    </div>


    <div class="content">

        <!-- WELCOME -->

        <div class="welcome">

            <div>

                <h1>
                    Selamat Datang, <?= htmlspecialchars($nama) ?>! 👋
                </h1>

                <p>
                    Kelola peminjaman alat laboratorium dengan mudah
                    dan cepat.
                </p>

            </div>

            <div class="welcome-button">

                <?php if ($role === 'laboran'): ?>

                    <a href="index.php?page=alat">
                        Kelola Alat →
                    </a>

                <?php else: ?>

                    <a href="index.php?page=alat_siswa">
                        Lihat Alat →
                    </a>

                <?php endif; ?>

            </div>

        </div>


        <!-- STATISTIK -->

        <div class="section-title">

            <h2>Ringkasan Sistem</h2>

            <p>
                Informasi data laboratorium saat ini
            </p>

        </div>


        <div class="stats">

            <div class="stat-card">

                <div class="stat-info">

                    <span>Total Alat</span>

                    <h2>
                        <?= number_format($total_alat) ?>
                    </h2>

                </div>

                <div class="stat-icon">
                    🔧
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">

                    <span>Total Siswa</span>

                    <h2>
                        <?= number_format($total_siswa) ?>
                    </h2>

                </div>

                <div class="stat-icon">
                    👨‍🎓
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">

                    <span>Total Peminjaman</span>

                    <h2>
                        <?= number_format($total_peminjaman) ?>
                    </h2>

                </div>

                <div class="stat-icon">
                    📋
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">

                    <span>Sedang Dipinjam</span>

                    <h2>
                        <?= number_format($total_dipinjam) ?>
                    </h2>

                </div>

                <div class="stat-icon">
                    📦
                </div>

            </div>

        </div>


        <!-- BOTTOM -->

        <div class="bottom-grid">


            <!-- MENU CEPAT -->

            <div class="panel">

                <div class="panel-header">

                    <h3>Menu Cepat</h3>

                </div>

                <div class="quick-menu">

                    <?php if ($role === 'laboran'): ?>

                        <a href="index.php?page=alat">

                            <div class="quick-icon">
                                🔧
                            </div>

                            <strong>
                                Data Alat
                            </strong>

                            <span>
                                Kelola alat
                            </span>

                        </a>


                        <a href="index.php?page=peminjaman">

                            <div class="quick-icon">
                                📋
                            </div>

                            <strong>
                                Peminjaman
                            </strong>

                            <span>
                                Kelola peminjaman
                            </span>

                        </a>


                        <a href="index.php?page=pengembalian">

                            <div class="quick-icon">
                                ↩️
                            </div>

                            <strong>
                                Pengembalian
                            </strong>

                            <span>
                                Proses pengembalian
                            </span>

                        </a>


                        <a href="index.php?page=laporan">

                            <div class="quick-icon">
                                📊
                            </div>

                            <strong>
                                Laporan
                            </strong>

                            <span>
                                Lihat laporan
                            </span>

                        </a>

                    <?php else: ?>

                        <a href="index.php?page=alat_siswa">

                            <div class="quick-icon">
                                🔧
                            </div>

                            <strong>
                                Lihat Alat
                            </strong>

                            <span>
                                Cek alat tersedia
                            </span>

                        </a>


                        <a href="index.php?page=pinjam">

                            <div class="quick-icon">
                                📝
                            </div>

                            <strong>
                                Pinjam Alat
                            </strong>

                            <span>
                                Ajukan peminjaman
                            </span>

                        </a>


                        <a href="index.php?page=riwayat">

                            <div class="quick-icon">
                                📚
                            </div>

                            <strong>
                                Riwayat
                            </strong>

                            <span>
                                Lihat peminjaman
                            </span>

                        </a>


                        <a href="index.php?page=dashboard">

                            <div class="quick-icon">
                                ℹ️
                            </div>

                            <strong>
                                Informasi
                            </strong>

                            <span>
                                Informasi sistem
                            </span>

                        </a>

                    <?php endif; ?>

                </div>

            </div>


            <!-- PEMINJAMAN TERBARU -->

            <div class="panel">

                <div class="panel-header">

                    <h3>Peminjaman Terbaru</h3>

                    <?php if ($role === 'laboran'): ?>

                        <a href="index.php?page=peminjaman">
                            Lihat Semua →
                        </a>

                    <?php else: ?>

                        <a href="index.php?page=riwayat">
                            Lihat Riwayat →
                        </a>

                    <?php endif; ?>

                </div>


                <?php if (count($peminjaman_terbaru) > 0): ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Nama</th>

                                    <th>Alat</th>

                                    <th>Jumlah</th>

                                    <th>Status</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($peminjaman_terbaru as $p): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($p['nama_alat']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($p['jumlah']) ?>
                                        </td>

                                        <td>

                                            <?php

                                            $statusClass = 'status-menunggu';

                                            if ($p['status'] === 'Disetujui') {
                                                $statusClass = 'status-disetujui';
                                            }

                                            if ($p['status'] === 'Ditolak') {
                                                $statusClass = 'status-ditolak';
                                            }

                                            ?>

                                            <span class="status <?= $statusClass ?>">

                                                <?= htmlspecialchars($p['status']) ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty">

                        📭

                        <br><br>

                        Belum ada data peminjaman.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

</body>

</html>