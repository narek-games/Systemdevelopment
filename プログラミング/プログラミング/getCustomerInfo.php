<?php
header("Content-Type: application/json; charset=UTF-8");

$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['customer_id'])) {
    $stmt = $pdo->prepare("SELECT customer_name, phone_number FROM customer WHERE customer_id = ?");
    $stmt->execute([$_GET['customer_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        echo json_encode(['success' => true, 'name' => $customer['customer_name'], 'phone' => $customer['phone_number']]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if (isset($_GET['customer_name'])) {
    $name = '%' . $_GET['customer_name'] . '%';
    $stmt = $pdo->prepare("SELECT customer_id, customer_name, phone_number FROM customer WHERE customer_name LIKE ?");
    $stmt->execute([$name]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'message' => 'パラメータが不足しています']);
