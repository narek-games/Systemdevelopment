<?php
    // ここからDB接続お決まりフレーズ
    $host = 'localhost';
    $dbname = 'mbs';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "接続失敗: " . $e->getMessage();
    }
    // ここまでDB接続お決まりフレーズ
?>