<?php
    // ここからDB接続お決まりフレーズ
    $host = '10.15.153.12';
    $port = 3306;
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    global $pdo;

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "接続失敗: " . $e->getMessage();
    }
    // ここまでDB接続お決まりフレーズ
?>