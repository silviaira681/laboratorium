<?php

session_start();

require_once "config.php";
require_once "fungsi.php";


if (isset($_SESSION['id_user'])) {

    redirect("index.php");
}


$register_error = "";
$register_success = "";


if (isset($_POST['register'])) {

    $nama =
        trim($_POST['nama']);

    $username =
        trim($_POST['username']);

    $password =
        $_POST['password'];

    $nis =
        trim($_POST['nis']);

    $kelas =
        trim($_POST['kelas']);

    $no_hp =
        trim($_POST['no_hp']);


    if (
        $nama == "" ||
        $username == "" ||
        $password == "" ||
        $nis == "" ||
        $kelas == ""
    ) {

        $register_error =
            "Semua data wajib diisi.";

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | INSERT USER
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO users
                (
                    nama,
                    username,
                    password,
                    role
                )
                VALUES
                (?, ?, ?, 'siswa')
            ";


            $stmt =
                $pdo->prepare($sql);


            $stmt->execute([
                $nama,
                $username,
                md5($password)
            ]);


            $id_user =
                $pdo->lastInsertId();


            /*
            |--------------------------------------------------------------------------
            | INSERT SISWA
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO siswa
                (
                    id_user,
                    nis,
                    kelas,
                    no_hp
                )
                VALUES
                (?, ?, ?, ?)
            ";


            $stmt =
                $pdo->prepare($sql);


            $stmt->execute([
                $id_user,
                $nis,
                $kelas,
                $no_hp
            ]);


            $pdo->commit();


            $register_success =
                "Registrasi berhasil. Silakan login.";


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }


            $register_error =
                "Username atau NIS sudah digunakan.";
        }
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Register - Laboratorium
</title>

<link
    rel="stylesheet"
    href="assets/style.css"
>

</head>

<body>

<div class="login-container">

<div class="login-box">

<div class="logo">
⚗️ LABORATORIUM
</div>

<div class="subtitle">
Sistem Peminjaman Alat Laboratorium
</div>


<h2>
Daftar Siswa
</h2>


<?php if ($register_error): ?>

<div class="error">

<?= e($register_error) ?>

</div>

<?php endif; ?>


<?php if ($register_success): ?>

<div class="success">

<?= e($register_success) ?>

</div>

<?php endif; ?>


<form method="POST">


<label>
Nama Lengkap
</label>

<input
    type="text"
    name="nama"
    required
>


<label>
Username
</label>

<input
    type="text"
    name="username"
    required
>


<label>
NIS
</label>

<input
    type="text"
    name="nis"
    required
>


<label>
Kelas
</label>

<input
    type="text"
    name="kelas"
    placeholder="Contoh: XII RPL 2"
    required
>


<label>
No. HP
</label>

<input
    type="text"
    name="no_hp"
>


<label>
Password
</label>

<input
    type="password"
    name="password"
    required
>


<button
    type="submit"
    name="register"
>
DAFTAR
</button>


</form>


<div class="link">

<a href="login.php">
Kembali ke Login
</a>

</div>


</div>

</div>

</body>

</html>