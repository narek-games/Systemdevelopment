<?php
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['customer_id'])) {
    echo json_encode(['success' => false, 'message' => '顧客IDが指定されていません']);
    exit;
}

$customerId = $_GET['customer_id'];

// データベース接続設定
$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $stmt = $pdo->prepare("SELECT customer_name, phone_number FROM customer WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        echo json_encode([
            'success' => true,
            'name' => $customer['customer_name'],
            'phone' => $customer['phone_number']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '顧客が見つかりません']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DBエラー: ' . $e->getMessage()]);
}
?>