<?php
$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$order_id = $_POST['order_id'];
$order_state = $_POST['order_state'];
$delivery_date = $_POST['order_delivered_date'] ?: null;  // フォーム側の name はそのままでもOK
$details = json_decode($_POST['details_json'], true);

// 注文ヘッダー更新
$stmt = $pdo->prepare("UPDATE `order` SET order_state = ?, delivery_date = ? WHERE order_id = ?");
$stmt->execute([$order_state, $delivery_date, $order_id]);

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

// 納品済ステータスで保存された場合 → リードタイム加算
if ($order_state === '納品済' && $delivery_date) {
  // 注文日と顧客IDを取得
  $stmt = $pdo->prepare("SELECT customer_id, order_date FROM `order` WHERE order_id = ?");
  $stmt->execute([$order_id]);
  $order = $stmt->fetch();

  if ($order) {
    $order_date = new DateTime($order['order_date']);
    $delivered_date = new DateTime($delivery_date);

    // 差分（時間→日にち、小数点第1位まで）
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

header("Location: orderHome.php?updated=1");
exit;
