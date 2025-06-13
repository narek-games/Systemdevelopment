<?php
 
// DB接続情報
 
$host = 'localhost';
 
$dbname = 'customer';
 
$username = 'user';
 
$password = 'password';
 
try {<?php

$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>顧客リスト</h2>";
    $stmt = $pdo->query("SELECT * FROM customer");
    echo "<table border='1'>
        <tr><th>顧客ID</th><th>顧客名</th><th>担当者</th><th>住所</th><th>電話番号</th><th>備考</th><th>登録日</th><th>累計売上</th><th>リードタイム</th><th>納品回数</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['customer_id']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['contact_person']}</td>
            <td>{$row['address']}</td>
            <td>{$row['phone_number']}</td>
            <td>{$row['customer_notes']}</td>
            <td>{$row['registration_date']}</td>
            <td>{$row['customer_sales']}</td>
            <td>{$row['customer_leadtime']}</td>
            <td>{$row['customer_delivery_count']}</td>
        </tr>";
    }
    echo "</table><br>";

    echo "<h2>納品情報</h2>";
    $stmt = $pdo->query("SELECT * FROM delivery");
    echo "<table border='1'>
        <tr><th>納品ID</th><th>納品日</th><th>顧客ID</th><th>税率</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['delivery_id']}</td>
            <td>{$row['delivery_date']}</td>
            <td>{$row['customer_id']}</td>
            <td>{$row['tax_rate']}</td>
        </tr>";
    }
    echo "</table><br>";

    echo "<h2>納品明細</h2>";
    $stmt = $pdo->query("SELECT * FROM delivery_detail");
    echo "<table border='1'>
        <tr><th>納品商品番号</th><th>納品ID</th><th>注文商品番号</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['delivery_product_number']}</td>
            <td>{$row['delivery_id']}</td>
            <td>{$row['order_product_number']}</td>
        </tr>";
    }
    echo "</table><br>";

    echo "<h2>注文情報</h2>";
    $stmt = $pdo->query("SELECT * FROM `order`");
    echo "<table border='1'>
        <tr><th>注文ID</th><th>顧客ID</th><th>作成日</th><th>状態</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['order_id']}</td>
            <td>{$row['customer_id']}</td>
            <td>{$row['order_date']}</td>
            <td>{$row['order_state']}</td>
        </tr>";
    }
    echo "</table><br>";

    echo "<h2>注文明細</h2>";
    $stmt = $pdo->query("SELECT * FROM order_detail");
    echo "<table border='1'>
        <tr><th>注文商品番号</th><th>注文ID</th><th>品名</th><th>未納品数量</th><th>単価</th><th>摘要</th><th>状態</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['order_product_number']}</td>
            <td>{$row['order_id']}</td>
            <td>{$row['product_name']}</td>
            <td>{$row['undelivered_quantity']}</td>
            <td>{$row['product_price']}</td>
            <td>{$row['product_abstract']}</td>
            <td>{$row['product_state']}</td>
        </tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
}
?>

 
    // PDOで接続
 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
    // SQL実行（全顧客情報を取得）
 
    $stmt = $pdo->query("SELECT * FROM customer");
 
    // 結果を表示
 
    echo "<h2>顧客リスト</h2>";
 
    echo "<table border='1'>";
 
    echo "<tr>
<th>ID</th><th>顧客名</th><th>担当者</th><th>住所</th><th>電話番号</th>
<th>備考</th><th>登録日</th><th>累計売上</th><th>リードタイム</th><th>納品回数</th>
</tr>";
 
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 
        echo "<tr>";
 
        echo "<td>" . htmlspecialchars($row['customer_id']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['contact_person']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['customer_notes']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['registration_date']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['customer_sales']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['customer_leadtime']) . "</td>";
 
        echo "<td>" . htmlspecialchars($row['customer_delivery_count']) . "</td>";
 
        echo "</tr>";
 
    }
 
    echo "</table>";
 
} catch (PDOException $e) {
 
    echo "接続エラー: " . $e->getMessage();
 
}
 
?>
 
 
 