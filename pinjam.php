<?php
require_once 'auth.php';

if ($role !== 'siswa') {
    header("Location: index.php?page=dashboard");
    exit;
}

$id_alat = $_GET['id'] ?? '';

if (!$id_alat) {
    header("Location: index.php?page=alat_siswa");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM alat
    WHERE id_alat = ?
");
$stmt->execute([$id_alat]);

$alat = $stmt->fetch();

if (!$alat) {
    echo "<script>
        alert('Data alat tidak ditemukan.');
        window.location='index.php?page=alat_siswa';
    </script>";
    exit;
}

$tersedia =
    $alat['jumlah'] > 0 &&
    strtolower($alat['kondisi']) === 'baik';
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pinjam Alat - Laboratorium</title>

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

        /* =========================
           LAYOUT
        ========================= */

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            width: 250px;
            min-height: 100vh;

            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;

            background: linear-gradient(
                180deg,
                #2563eb,
                #1e40af
            );

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

            font-size: 19px;
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

        /* =========================
           CONTENT
        ========================= */

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

            box-shadow:
                0 5px 20px rgba(15,23,42,.06);

            color: #475569;

            font-size: 13px;
        }

        /* =========================
           BREADCRUMB
        ========================= */

        .breadcrumb {
            margin-bottom: 20px;

            font-size: 13px;

            color: #64748b;
        }

        .breadcrumb a {
            color: #2563eb;

            text-decoration: none;

            font-weight: 600;
        }

        /* =========================
           MAIN FORM AREA
        ========================= */

        .borrow-wrapper {
            display: grid;

            grid-template-columns:
                minmax(280px, 360px)
                1fr;

            gap: 25px;

            align-items: start;
        }

        /* =========================
           TOOL CARD
        ========================= */

        .tool-card {
            background: white;

            border-radius: 22px;

            padding: 25px;

            box-shadow:
                0 8px 30px rgba(15,23,42,.07);

            border: 1px solid #eef2f7;
        }

        .tool-icon {
            width: 85px;
            height: 85px;

            border-radius: 24px;

            background: linear-gradient(
                135deg,
                #eff6ff,
                #dbeafe
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 42px;

            margin-bottom: 20px;
        }

        .tool-card h2 {
            margin: 0 0 7px;

            font-size: 22px;

            color: #0f172a;
        }

        .category {
            color: #64748b;

            font-size: 13px;

            margin-bottom: 22px;
        }

        .availability {
            display: flex;

            align-items: center;

            gap: 8px;

            background: #dcfce7;

            color: #15803d;

            padding: 10px 13px;

            border-radius: 10px;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 20px;
        }

        .availability.not {
            background: #fee2e2;

            color: #dc2626;
        }

        .tool-info {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 12px;

            margin-bottom: 20px;
        }

        .info-box {
            background: #f8fafc;

            border-radius: 12px;

            padding: 13px;
        }

        .info-box small {
            display: block;

            color: #94a3b8;

            font-size: 10px;

            margin-bottom: 5px;

            text-transform: uppercase;
        }

        .info-box strong {
            color: #334155;

            font-size: 14px;
        }

        .description-title {
            font-size: 12px;

            font-weight: 700;

            color: #475569;

            margin-bottom: 8px;
        }

        .description {
            background: #f8fafc;

            padding: 13px;

            border-radius: 12px;

            color: #64748b;

            font-size: 12px;

            line-height: 1.6;
        }

        /* =========================
           FORM CARD
        ========================= */

        .form-card {
            background: white;

            border-radius: 22px;

            padding: 30px;

            box-shadow:
                0 8px 30px rgba(15,23,42,.07);

            border: 1px solid #eef2f7;
        }

        .form-header {
            padding-bottom: 20px;

            margin-bottom: 25px;

            border-bottom: 1px solid #eef2f7;
        }

        .form-header h2 {
            margin: 0 0 6px;

            font-size: 21px;

            color: #0f172a;
        }

        .form-header p {
            margin: 0;

            color: #64748b;

            font-size: 13px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-size: 13px;

            font-weight: 700;

            color: #334155;
        }

        .required {
            color: #ef4444;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 14px;

            top: 12px;

            font-size: 16px;
        }

        .form-control {
            width: 100%;

            padding: 13px 14px 13px 43px;

            border: 1px solid #dbe2ea;

            border-radius: 11px;

            outline: none;

            font-family: inherit;

            font-size: 13px;

            background: white;

            transition: .2s;
        }

        .form-control:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37,99,235,.1);
        }

        textarea.form-control {
            min-height: 110px;

            resize: vertical;

            padding-top: 13px;
        }

        .date-row {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;
        }

        .helper {
            margin-top: 6px;

            color: #94a3b8;

            font-size: 11px;
        }

        /* =========================
           NOTICE
        ========================= */

        .notice {
            background: #eff6ff;

            border: 1px solid #dbeafe;

            color: #1e40af;

            padding: 14px;

            border-radius: 12px;

            font-size: 12px;

            line-height: 1.6;

            margin-bottom: 22px;
        }

        .notice strong {
            display: block;

            margin-bottom: 4px;

            font-size: 13px;
        }

        /* =========================
           BUTTON
        ========================= */

        .form-actions {
            display: flex;

            gap: 12px;

            margin-top: 25px;
        }

        .btn {
            border: none;

            border-radius: 11px;

            padding: 13px 20px;

            font-family: inherit;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

            text-decoration: none;

            text-align: center;

            transition: .2s;
        }

        .btn-primary {
            background: #2563eb;

            color: white;

            flex: 1;
        }

        .btn-primary:hover {
            background: #1d4ed8;

            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f1f5f9;

            color: #475569;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .disabled-box {
            background: #fff1f2;

            border: 1px solid #fecdd3;

            color: #be123c;

            border-radius: 14px;

            padding: 18px;

            text-align: center;

            font-size: 13px;

            font-weight: 600;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media(max-width: 900px) {

            .sidebar {
                width: 210px;
            }

            .content {
                margin-left: 210px;

                width: calc(100% - 210px);

                padding: 22px;
            }

            .borrow-wrapper {
                grid-template-columns: 1fr;
            }

            .tool-card {
                display: grid;

                grid-template-columns: 90px 1fr;

                gap: 20px;
            }

            .tool-icon {
                margin: 0;
            }

            .tool-card h2,
            .category,
            .availability,
            .tool-info,
            .description-title,
            .description {
                grid-column: 1 / -1;
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

            .date-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }

    </style>

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                🔬
            </div>

            <h2>LABORATORIUM</h2>

            <p>
                Sistem Peminjaman Alat
            </p>

        </div>


        <div class="profile">

            <div class="profile-icon">
                👤
            </div>

            <div>

                <strong>
                    <?= e($nama) ?>
                </strong>

                <span>
                    Siswa
                </span>

            </div>

        </div>


        <div class="menu-title">
            Menu Utama
        </div>


        <div class="menu">

            <a href="index.php?page=dashboard">

                <span class="icon">
                    🏠
                </span>

                Dashboard

            </a>


            <a href="index.php?page=alat_siswa"
               class="active">

                <span class="icon">
                    🔧
                </span>

                Daftar Alat

            </a>


            <a href="index.php?page=riwayat">

                <span class="icon">
                    📋
                </span>

                Riwayat Peminjaman

            </a>


            <div class="logout">

                <a href="index.php?logout=1">

                    <span class="icon">
                        🚪
                    </span>

                    Logout

                </a>

            </div>

        </div>

    </aside>


    <!-- CONTENT -->

    <main class="content">


        <div class="topbar">

            <div>

                <h1>
                    Pinjam Alat
                </h1>

                <p>
                    Isi formulir berikut untuk mengajukan peminjaman alat.
                </p>

            </div>


            <div class="date-box">

                📅 <?= date('d F Y') ?>

            </div>

        </div>


        <!-- BREADCRUMB -->

        <div class="breadcrumb">

            <a href="index.php?page=alat_siswa">
                Daftar Alat
            </a>

            &nbsp; / &nbsp;

            Pinjam Alat

        </div>


        <div class="borrow-wrapper">


            <!-- ======================
                 INFORMASI ALAT
            ======================= -->

            <div class="tool-card">


                <div class="tool-icon">
                    🔧
                </div>


                <h2>
                    <?= e($alat['nama_alat']) ?>
                </h2>


                <div class="category">

                    📁
                    <?= e($alat['kategori']) ?>

                </div>


                <?php if ($tersedia): ?>

                    <div class="availability">

                        ●

                        Alat tersedia untuk dipinjam

                    </div>

                <?php else: ?>

                    <div class="availability not">

                        ●

                        Alat tidak tersedia

                    </div>

                <?php endif; ?>


                <div class="tool-info">


                    <div class="info-box">

                        <small>
                            Stok
                        </small>

                        <strong>
                            <?= e($alat['jumlah']) ?> unit
                        </strong>

                    </div>


                    <div class="info-box">

                        <small>
                            Kondisi
                        </small>

                        <strong>
                            <?= e($alat['kondisi']) ?>
                        </strong>

                    </div>


                </div>


                <div class="description-title">
                    DESKRIPSI ALAT
                </div>


                <div class="description">

                    <?= !empty($alat['deskripsi'])
                        ? e($alat['deskripsi'])
                        : 'Tidak ada deskripsi alat.' ?>

                </div>


            </div>


            <!-- ======================
                 FORM PEMINJAMAN
            ======================= -->

            <div class="form-card">


                <div class="form-header">

                    <h2>
                        📝 Form Peminjaman
                    </h2>

                    <p>
                        Lengkapi data peminjaman dengan benar.
                    </p>

                </div>


                <?php if (!$tersedia): ?>


                    <div class="disabled-box">

                        ⚠️

                        Alat ini sedang tidak tersedia,
                        sehingga tidak dapat dipinjam.

                    </div>


                    <div class="form-actions">

                        <a
                            href="index.php?page=alat_siswa"
                            class="btn btn-secondary"
                        >
                            ← Kembali ke Daftar Alat
                        </a>

                    </div>


                <?php else: ?>


                    <div class="notice">

                        <strong>
                            💡 Informasi Peminjaman
                        </strong>

                        Setelah pengajuan dikirim, peminjaman akan
                        berstatus <b>Menunggu</b> sampai disetujui
                        oleh laboran.

                    </div>


                    <form
                        action="proses/ajukan_peminjaman.php"
                        method="POST"
                    >


                        <input
                            type="hidden"
                            name="id_alat"
                            value="<?= $alat['id_alat'] ?>"
                        >


                        <!-- JUMLAH -->

                        <div class="form-group">

                            <label>
                                Jumlah Alat
                                <span class="required">*</span>
                            </label>


                            <div class="input-wrapper">

                                <span class="input-icon">
                                    🔢
                                </span>

                                <input
                                    type="number"
                                    name="jumlah"
                                    class="form-control"
                                    min="1"
                                    max="<?= $alat['jumlah'] ?>"
                                    value="1"
                                    required
                                >

                            </div>


                            <div class="helper">

                                Maksimal
                                <?= e($alat['jumlah']) ?>
                                unit.

                            </div>

                        </div>


                        <!-- TANGGAL -->

                        <div class="date-row">


                            <div class="form-group">

                                <label>
                                    Tanggal Pinjam
                                    <span class="required">*</span>
                                </label>


                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        📅
                                    </span>

                                    <input
                                        type="date"
                                        name="tanggal_pinjam"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d') ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="form-group">

                                <label>
                                    Tanggal Kembali
                                    <span class="required">*</span>
                                </label>


                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        📅
                                    </span>

                                    <input
                                        type="date"
                                        name="tanggal_kembali"
                                        class="form-control"
                                        value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                        min="<?= date('Y-m-d') ?>"
                                        required
                                    >

                                </div>

                            </div>


                        </div>


                        <!-- KEPERLUAN -->

                        <div class="form-group">

                            <label>
                                Keperluan Peminjaman
                                <span class="required">*</span>
                            </label>


                            <div class="input-wrapper">

                                <span class="input-icon">
                                    📝
                                </span>

                                <textarea
                                    name="keperluan"
                                    class="form-control"
                                    placeholder="Contoh: Praktikum jaringan komputer, tugas kelompok, atau kegiatan laboratorium..."
                                    required
                                ></textarea>

                            </div>

                            <div class="helper">

                                Jelaskan tujuan penggunaan alat
                                agar laboran dapat mengetahui
                                keperluan peminjaman.

                            </div>

                        </div>


                        <!-- BUTTON -->

                        <div class="form-actions">


                            <a
                                href="index.php?page=alat_siswa"
                                class="btn btn-secondary"
                            >
                                ← Batal
                            </a>


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                📥 Ajukan Peminjaman
                            </button>


                        </div>


                    </form>


                <?php endif; ?>


            </div>


        </div>


    </main>

</div>

</body>

</html>