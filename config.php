<?php

$host = "localhost";
$db   = "db_laboratorium";
$user = "root";
$pass = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Database Error</title>
        </head>

        <body>

        <div style='
            font-family:Arial;
            max-width:700px;
            margin:50px auto;
            padding:30px;
            background:#ffe5e5;
            color:#900;
            border-radius:12px;
        '>

            <h2>❌ Database Belum Terhubung</h2>

            <p>Pastikan:</p>

            <ol>
                <li>Apache XAMPP sudah Start</li>
                <li>MySQL XAMPP sudah Start</li>
                <li>Database <b>db_laboratorium</b> sudah dibuat</li>
                <li>Nama database sudah benar</li>
            </ol>

            <hr>

            <b>Error:</b>

            <p>
                " . htmlspecialchars($e->getMessage()) . "
            </p>

        </div>

        </body>
        </html>
    ");
}