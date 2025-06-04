<?php
 
// DB接続情報
 
$host = 'localhost';
 
$dbname = 'customer';
 
$username = 'user';
 
$password = 'password';
 
try {
 
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
 
 
 