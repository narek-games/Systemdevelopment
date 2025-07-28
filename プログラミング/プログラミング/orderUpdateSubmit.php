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
$stmt = $pdo->prepare("UPDATE `order` SET order_state = ? WHERE order_id = ?");
$stmt->execute([$order_state, $order_id]);

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

// 「納品済」で保存された場合にのみ、リードタイム加算処理
if ($order_state === '納品済') {
  // 注文情報の取得
  $stmt = $pdo->prepare("SELECT customer_id, order_date FROM `order` WHERE order_id = ?");
  $stmt->execute([$order_id]);
  $order = $stmt->fetch();

  if ($order) {
    // 納品日取得（delivery テーブル）
    $stmt = $pdo->prepare("SELECT delivery_date FROM delivery WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $delivery = $stmt->fetch();

    if ($delivery && $delivery['delivery_date']) {
      $order_date = new DateTime($order['order_date']);
      $delivered_date = new DateTime($delivery['delivery_date']);

      // 差分（時間 → 日数（小数））
      $interval_hours = ($delivered_date->getTimestamp() - $order_date->getTimestamp()) / 3600;
      $leadtime = round($interval_hours / 24, 1);

      // 累計リードタイムを加算（NULLなら0として扱う）
      $stmt = $pdo->prepare("
        UPDATE customer
        SET customer_leadtime = IFNULL(customer_leadtime, 0) + ?
        WHERE customer_id = ?
      ");
      $stmt->execute([$leadtime, $order['customer_id']]);
    }
  }
}

header("Location: orderHome.php?updated=1");
exit;
