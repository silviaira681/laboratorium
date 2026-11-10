<?php

session_start();

require_once 'config.php';
require_once 'fungsi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Username dan password wajib diisi.';

    } else {

        $password_md5 = md5($password);

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            AND password = ?
            LIMIT 1
        ");

        $stmt->execute([
            $username,
            $password_md5
        ]);

        $user = $stmt->fetch();

        if ($user) {

            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php?page=dashboard");
            exit;

        } else {

            $error = 'Username atau password salah.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Laboratorium</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: #1769e0;
        }

        .login-box {
            width: 400px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #1769e0;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0d56bd;
        }

        .error {
            background: #ffe5e5;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .register {
            text-align: center;
            margin-top: 20px;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h2>Login Laboratorium</h2>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

    <div class="register">
        Belum punya akun?
        <a href="register.php">Daftar</a>
    </div>

</div>

</body>

</html>