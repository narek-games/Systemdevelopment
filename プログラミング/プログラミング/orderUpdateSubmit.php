<?php
$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$order_id = $_POST['order_id'];
$order_state = $_POST['order_state'];
$order_delivered_date = $_POST['order_delivered_date'] ?: null;
$details = json_decode($_POST['details_json'], true);

// 注文ヘッダー更新
$stmt = $pdo->prepare("UPDATE `order` SET order_state = ?, order_delivered_date = ? WHERE order_id = ?");
$stmt->execute([$order_state, $order_delivered_date, $order_id]);

// 旧明細削除
$stmt = $pdo->prepare("DELETE FROM order_detail WHERE order_id = ?");
$stmt->execute([$order_id]);

// 明細再登録
$stmt = $pdo->prepare("
  INSERT INTO order_detail (order_id, order_product_number, product_name, product_quantity, undelivered_quantity, product_price, product_abstract)
  VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($details as $i => $d) {
  $stmt->execute([
    $order_id,
    $i + 1,
    $d['name'],
    (int)$d['qty'],
    (int)$d['remain'],
    (int)$d['price'],
    $d['note']
  ]);
}

header("Location: orderHome.php?updated=1");
exit;
