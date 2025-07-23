<?php
require_once 'dbConnect.php';

// order_idがGETで渡されているか確認
if (!isset($_GET['order_id'])) {
    header('Location: orderHome.php');
    exit;
}


$order_id = $_GET['order_id'];
// order_idが空や不正な場合は削除しない
if (empty($order_id) || !preg_match('/^[0-9A-Za-z_-]+$/', $order_id)) {
    header('Location: orderHome.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. delivery_detailテーブルから削除
    $sql1 = 'DELETE FROM delivery_detail WHERE order_id = :order_id';
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->bindParam(':order_id', $order_id, PDO::PARAM_STR);
    $stmt1->execute();

    // 2. orderテーブルから削除
    $sql2 = 'DELETE FROM `order` WHERE order_id = :order_id';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':order_id', $order_id, PDO::PARAM_STR);
    $stmt2->execute();

    $pdo->commit();
    header('Location: orderHome.php?deleted=1');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo '削除処理中にエラーが発生しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
