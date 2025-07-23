<?php
//DB接続内容
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// JSONデータ受け取り
$data = json_decode(file_get_contents("php://input"), true);
$customerId = $data['customerId'];
$items = $data['items'];

// order_idを "OR000001" 形式で生成
$stmt = $pdo->query("SELECT MAX(order_id) AS max_id FROM `order` WHERE order_id LIKE 'OR%'");
$maxOrderId = $stmt->fetchColumn();
$nextNumber = $maxOrderId ? intval(substr($maxOrderId, 2)) + 1 : 1;
$orderId = 'OR' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

// 注文を order テーブルへ登録
$stmt = $pdo->prepare("INSERT INTO `order` (order_id, customer_id, order_date, order_state) VALUES (?, ?, NOW(), false)");
$stmt->execute([$orderId, $customerId]);

// 注文明細を order_detail テーブルに登録（order_product_numberは1から）
$stmtDetail = $pdo->prepare("
  INSERT INTO order_detail (
    order_product_number,
    order_id,
    product_name,
    product_quantity,
    undelivered_quantity,
    product_price,
    product_abstract,
    product_state
  ) VALUES (?, ?, ?, ?, ?, ?, ?, false)
");

$orderProductNumber = 1;
foreach ($items as $item) {
    $stmtDetail->execute([
        $orderProductNumber,
        $orderId,
        $item['name'],
        intval($item['quantity']),
        intval($item['quantity']), // 未納品数 = 数量
        intval($item['price']),
        $item['remark']
    ]);
    $orderProductNumber++;
}

echo "注文ID {$orderId} を登録しました。";
?>