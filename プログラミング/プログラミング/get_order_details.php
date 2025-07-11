<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'dbConnect.php';

$orderId = $_GET['order-id'] ?? null;
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => '注文IDが指定されていません。']);
    exit;
}

// 修正: 正しいカラム名(order_product_number, product_price)でデータを取得
$sql = "
    SELECT order_product_number, product_name, product_price 
    FROM order_detail
    WHERE order_id = ?
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($products);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'データベースエラー: ' . $e->getMessage()]);
}
?>