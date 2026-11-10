<?php
require_once 'auth.php';

if ($role !== 'siswa') {
    header("Location: index.php?page=dashboard");
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        a.nama_alat,
        a.kategori
    FROM peminjaman p
    JOIN alat a ON a.id_alat = p.id_alat
    WHERE p.id_siswa = ?
    ORDER BY p.id_peminjaman DESC
");

$stmt->execute([$id_siswa]);

$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Peminjaman - Laboratorium</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            background: linear-gradient(180deg, #2563eb, #1e40af);
            color: white;
            padding: 25px 18px;
        }

        .logo {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(255,255,255,.2);
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            margin: auto;
            border-radius: 18px;
            background: rgba(255,255,255,.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .logo h2 {
            margin: 12px 0 3px;
            font-size: 20px;
        }

        .logo p {
            margin: 0;
            font-size: 12px;
            opacity: .8;
        }

        .profile {
            margin: 22px 0;
            padding: 15px;
            background: rgba(255,255,255,.12);
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-icon {
            width: 42px;
            height: 42px;
            background: white;
            color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile strong {
            display: block;
            font-size: 14px;
        }

        .profile span {
            font-size: 12px;
            opacity: .8;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            opacity: .65;
            margin: 25px 10px 10px;
            letter-spacing: 1px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            border-radius: 12px;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .menu a:hover,
        .menu a.active {
            background: rgba(255,255,255,.18);
        }

        .menu .icon {
            width: 22px;
            text-align: center;
        }

        .logout {
            margin-top: 25px;
            border-top: 1px solid rgba(255,255,255,.2);
            padding-top: 20px;
        }

        /* CONTENT */
        .content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px 35px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 28px;
            color: #0f172a;
        }

        .topbar p {
            color: #64748b;
            margin: 6px 0 0;
        }

        .date-box {
            background: white;
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(15,23,42,.06);
            font-size: 13px;
        }

        /* SUMMARY */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 20px rgba(15,23,42,.06);
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .summary strong {
            display: block;
            font-size: 23px;
            color: #0f172a;
        }

        .summary span {
            color: #64748b;
            font-size: 12px;
        }

        /* TABLE CARD */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 7px 25px rgba(15,23,42,.07);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h2 {
            margin: 0;
            font-size: 19px;
        }

        .table-header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .search {
            position: relative;
        }

        .search input {
            width: 220px;
            padding: 10px 12px 10px 36px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            outline: none;
        }

        .search span {
            position: absolute;
            left: 12px;
            top: 9px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 16px 14px;
            border-bottom: 1px solid #eef2f7;
            font-size: 13px;
        }

        tr:hover td {
            background: #f8fbff;
        }

        .no {
            width: 45px;
            color: #94a3b8;
            font-weight: 600;
        }

        .alat-name {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .alat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .alat-name strong {
            display: block;
            color: #0f172a;
        }

        .alat-name small {
            color: #94a3b8;
            font-size: 11px;
        }

        .jumlah {
            font-weight: 700;
        }

        .tanggal {
            color: #475569;
            white-space: nowrap;
        }

        .keperluan {
            max-width: 180px;
            color: #64748b;
        }

        /* STATUS */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .menunggu {
            background: #fef3c7;
            color: #b45309;
        }

        .disetujui {
            background: #dcfce7;
            color: #15803d;
        }

        .ditolak {
            background: #fee2e2;
            color: #dc2626;
        }

        .dikembalikan {
            background: #e0e7ff;
            color: #4338ca;
        }

        .catatan {
            color: #64748b;
            font-size: 11px;
            max-width: 150px;
        }

        .empty {
            text-align: center;
            padding: 70px 20px;
        }

        .empty-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .empty h3 {
            margin: 0 0 8px;
            font-size: 19px;
        }

        .empty p {
            margin: 0;
            color: #64748b;
        }

        @media(max-width: 900px) {

            .summary {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 210px;
            }

            .content {
                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 22px;
            }
        }

        @media(max-width: 650px) {

            .layout {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .topbar {
                display: block;
            }

            .date-box {
                display: inline-block;
                margin-top: 15px;
            }

            .table-header {
                display: block;
            }

            .search {
                margin-top: 15px;
            }

            .search input {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="logo">
            <div class="logo-icon">🔬</div>
            <h2>LABORATORIUM</h2>
            <p>Sistem Peminjaman Alat</p>
        </div>

        <div class="profile">
            <div class="profile-icon">👤</div>

            <div>
                <strong><?= e($nama) ?></strong>
                <span>Siswa</span>
            </div>
        </div>

        <div class="menu-title">
            Menu Utama
        </div>

        <div class="menu">

            <a href="index.php?page=dashboard">
                <span class="icon">🏠</span>
                Dashboard
            </a>

            <a href="index.php?page=alat_siswa">
                <span class="icon">🔧</span>
                Daftar Alat
            </a>

            <a href="index.php?page=riwayat" class="active">
                <span class="icon">📋</span>
                Riwayat Peminjaman
            </a>

            <div class="logout">
                <a href="index.php?logout=1">
                    <span class="icon">🚪</span>
                    Logout
                </a>
            </div>

        </div>

    </aside>

    <!-- CONTENT -->
    <main class="content">

        <div class="topbar">

            <div>
                <h1>Riwayat Peminjaman</h1>
                <p>Lihat semua aktivitas peminjaman alat laboratorium.</p>
            </div>

            <div class="date-box">
                📅 <?= date('d F Y') ?>
            </div>

        </div>

        <?php

        $total = count($data);
        $menunggu = 0;
        $disetujui = 0;
        $selesai = 0;

        foreach ($data as $d) {

            if ($d['status'] === 'Menunggu') {
                $menunggu++;
            }

            if ($d['status'] === 'Disetujui') {
                $disetujui++;
            }

            if ($d['status'] === 'Dikembalikan') {
                $selesai++;
            }
        }

        ?>

        <div class="summary">

            <div class="summary-card">
                <div class="summary-icon">📋</div>

                <div>
                    <strong><?= $total ?></strong>
                    <span>Total Peminjaman</span>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">⏳</div>

                <div>
                    <strong><?= $menunggu ?></strong>
                    <span>Menunggu Persetujuan</span>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">✅</div>

                <div>
                    <strong><?= $selesai ?></strong>
                    <span>Sudah Dikembalikan</span>
                </div>
            </div>

        </div>

        <div class="table-card">

            <div class="table-header">

                <div>
                    <h2>Daftar Riwayat</h2>
                    <p>Data peminjaman alat yang pernah kamu ajukan.</p>
                </div>

                <div class="search">
                    <span>🔎</span>

                    <input
                        type="text"
                        id="searchRiwayat"
                        placeholder="Cari alat..."
                        onkeyup="cariRiwayat()"
                    >
                </div>

            </div>

            <?php if (empty($data)): ?>

                <div class="empty">

                    <div class="empty-icon">
                        📭
                    </div>

                    <h3>Belum Ada Riwayat</h3>

                    <p>
                        Kamu belum pernah melakukan peminjaman alat.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>No</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>

                        </thead>

                        <tbody id="riwayatTable">

                        <?php $no = 1; ?>

                        <?php foreach ($data as $d): ?>

                            <?php

                            $statusClass = '';

                            switch ($d['status']) {

                                case 'Menunggu':
                                    $statusClass = 'menunggu';
                                    break;

                                case 'Disetujui':
                                    $statusClass = 'disetujui';
                                    break;

                                case 'Ditolak':
                                    $statusClass = 'ditolak';
                                    break;

                                case 'Dikembalikan':
                                    $statusClass = 'dikembalikan';
                                    break;

                                default:
                                    $statusClass = 'menunggu';
                            }

                            ?>

                            <tr class="riwayat-row">

                                <td class="no">
                                    <?= $no++ ?>
                                </td>

                                <td>

                                    <div class="alat-name">

                                        <div class="alat-icon">
                                            🔧
                                        </div>

                                        <div>
                                            <strong>
                                                <?= e($d['nama_alat']) ?>
                                            </strong>

                                            <small>
                                                <?= e($d['kategori']) ?>
                                            </small>
                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <span class="jumlah">
                                        <?= e($d['jumlah']) ?> unit
                                    </span>
                                </td>

                                <td class="tanggal">
                                    <?= date('d-m-Y', strtotime($d['tanggal_pinjam'])) ?>
                                </td>

                                <td class="tanggal">
                                    <?= date('d-m-Y', strtotime($d['tanggal_kembali'])) ?>
                                </td>

                                <td class="keperluan">
                                    <?= e($d['keperluan']) ?>
                                </td>

                                <td>

                                    <span class="badge <?= $statusClass ?>">

                                        <?php if ($d['status'] === 'Menunggu'): ?>
                                            ⏳
                                        <?php elseif ($d['status'] === 'Disetujui'): ?>
                                            ✓
                                        <?php elseif ($d['status'] === 'Ditolak'): ?>
                                            ✕
                                        <?php else: ?>
                                            ↩
                                        <?php endif; ?>

                                        <?= e($d['status']) ?>

                                    </span>

                                </td>

                                <td class="catatan">
                                    <?= !empty($d['catatan'])
                                        ? e($d['catatan'])
                                        : '-' ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

<script>
function cariRiwayat() {

    const input = document
        .getElementById("searchRiwayat")
        .value
        .toLowerCase();

    const rows = document.querySelectorAll(".riwayat-row");

    rows.forEach(function(row) {

        const text = row.innerText.toLowerCase();

        if (text.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });
}
</script>

</body>
</html>