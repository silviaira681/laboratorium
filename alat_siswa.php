<?php
require_once 'auth.php';

if ($role !== 'siswa') {
    header("Location: index.php?page=dashboard");
    exit;
}

$stmt = $pdo->query("
    SELECT *
    FROM alat
    ORDER BY nama_alat ASC
");

$alat = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Alat - Laboratorium</title>

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

        .student-layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: linear-gradient(180deg, #2563eb, #1e40af);
            color: white;
            padding: 25px 18px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
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
            font-size: 20px;
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
            transition: .2s;
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
            margin: 6px 0 0;
            color: #64748b;
        }

        .date-box {
            background: white;
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(15,23,42,.06);
            color: #475569;
            font-size: 13px;
        }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 28px;
            border-radius: 20px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .hero::after {
            content: "🔬";
            position: absolute;
            right: 40px;
            top: 10px;
            font-size: 100px;
            opacity: .12;
        }

        .hero h2 {
            margin: 0 0 8px;
            font-size: 23px;
        }

        .hero p {
            margin: 0;
            opacity: .9;
            max-width: 650px;
        }

        /* FILTER */
        .toolbar {
            background: white;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 5px 20px rgba(15,23,42,.06);
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: space-between;
        }

        .search {
            position: relative;
            flex: 1;
        }

        .search input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            padding: 13px 15px 13px 42px;
            outline: none;
            font-size: 14px;
        }

        .search input:focus {
            border-color: #2563eb;
        }

        .search span {
            position: absolute;
            left: 15px;
            top: 12px;
        }

        /* CARDS */
        .alat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .alat-card {
            background: white;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 7px 25px rgba(15,23,42,.07);
            border: 1px solid #eef2f7;
            transition: .25s;
            position: relative;
            overflow: hidden;
        }

        .alat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 35px rgba(37,99,235,.13);
        }

        .alat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .alat-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 29px;
        }

        .status {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-tersedia {
            background: #dcfce7;
            color: #15803d;
        }

        .status-habis {
            background: #fee2e2;
            color: #dc2626;
        }

        .alat-card h3 {
            margin: 0 0 7px;
            font-size: 18px;
            color: #0f172a;
        }

        .kategori {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .detail-box {
            background: #f8fafc;
            padding: 11px;
            border-radius: 10px;
        }

        .detail-box small {
            display: block;
            color: #94a3b8;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .detail-box strong {
            font-size: 13px;
        }

        .deskripsi {
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
            min-height: 40px;
            margin-bottom: 18px;
        }

        .btn-pinjam {
            display: block;
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 600;
            transition: .2s;
        }

        .btn-pinjam:hover {
            background: #1d4ed8;
        }

        .btn-disabled {
            display: block;
            width: 100%;
            padding: 12px;
            background: #e2e8f0;
            color: #94a3b8;
            text-align: center;
            border-radius: 11px;
            font-size: 13px;
            cursor: not-allowed;
        }

        .empty {
            background: white;
            border-radius: 18px;
            padding: 60px 20px;
            text-align: center;
            grid-column: 1 / -1;
            box-shadow: 0 5px 20px rgba(15,23,42,.05);
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty h3 {
            margin: 0 0 8px;
        }

        .empty p {
            color: #64748b;
            margin: 0;
        }

        @media(max-width: 1100px) {
            .alat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 800px) {
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
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .student-layout {
                display: block;
            }

            .content {
                margin-left: 0;
                width: 100%;
            }

            .alat-grid {
                grid-template-columns: 1fr;
            }

            .topbar,
            .toolbar {
                display: block;
            }

            .date-box {
                margin-top: 15px;
                display: inline-block;
            }

            .search {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

<div class="student-layout">

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

        <div class="menu-title">Menu Utama</div>

        <div class="menu">

            <a href="index.php?page=dashboard">
                <span class="icon">🏠</span>
                Dashboard
            </a>

            <a href="index.php?page=alat_siswa" class="active">
                <span class="icon">🔧</span>
                Daftar Alat
            </a>

            <a href="index.php?page=riwayat">
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
                <h1>Daftar Alat</h1>
                <p>Pilih alat laboratorium yang ingin kamu pinjam.</p>
            </div>

            <div class="date-box">
                📅 <?= date('d F Y') ?>
            </div>
        </div>

        <div class="hero">
            <div>
                <h2>🔬 Alat Laboratorium</h2>
                <p>
                    Lihat alat yang tersedia dan ajukan peminjaman
                    sesuai kebutuhan praktikum.
                </p>
            </div>
        </div>

        <div class="toolbar">

            <div class="search">
                <span>🔎</span>
                <input
                    type="text"
                    id="searchAlat"
                    placeholder="Cari nama atau kategori alat..."
                    onkeyup="cariAlat()"
                >
            </div>

        </div>

        <div class="alat-grid" id="alatGrid">

            <?php if (empty($alat)): ?>

                <div class="empty">
                    <div class="empty-icon">📦</div>
                    <h3>Belum Ada Alat</h3>
                    <p>Belum ada data alat laboratorium.</p>
                </div>

            <?php else: ?>

                <?php foreach ($alat as $a): ?>

                    <?php
                    $tersedia =
                        $a['jumlah'] > 0 &&
                        strtolower($a['kondisi']) === 'baik';
                    ?>

                    <div
                        class="alat-card"
                        data-search="<?= strtolower(e($a['nama_alat'] . ' ' . $a['kategori'])) ?>"
                    >

                        <div class="alat-top">

                            <div class="alat-icon">
                                🔧
                            </div>

                            <?php if ($tersedia): ?>

                                <span class="status status-tersedia">
                                    ● Tersedia
                                </span>

                            <?php else: ?>

                                <span class="status status-habis">
                                    ● Tidak Tersedia
                                </span>

                            <?php endif; ?>

                        </div>

                        <h3><?= e($a['nama_alat']) ?></h3>

                        <div class="kategori">
                            📁 <?= e($a['kategori']) ?>
                        </div>

                        <div class="detail">

                            <div class="detail-box">
                                <small>JUMLAH TERSEDIA</small>
                                <strong><?= e($a['jumlah']) ?> unit</strong>
                            </div>

                            <div class="detail-box">
                                <small>KONDISI</small>
                                <strong><?= e($a['kondisi']) ?></strong>
                            </div>

                        </div>

                        <div class="deskripsi">
                            <?= !empty($a['deskripsi'])
                                ? e($a['deskripsi'])
                                : 'Tidak ada deskripsi alat.' ?>
                        </div>

                        <?php if ($tersedia): ?>

                            <a
                                href="index.php?page=pinjam&id=<?= $a['id_alat'] ?>"
                                class="btn-pinjam"
                            >
                                📥 Pinjam Alat
                            </a>

                        <?php else: ?>

                            <div class="btn-disabled">
                                Alat Tidak Tersedia
                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </main>

</div>

<script>
function cariAlat() {

    const input = document
        .getElementById("searchAlat")
        .value
        .toLowerCase();

    const cards = document.querySelectorAll(".alat-card");

    cards.forEach(function(card) {

        const text = card.dataset.search;

        if (text.includes(input)) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }

    });
}
</script>

</body>
</html>