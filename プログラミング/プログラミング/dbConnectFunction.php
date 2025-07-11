<?php
function getStatistics($keyword = '')
{
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';
 
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
        // 検索キーワードがある場合は WHERE 句を追加
        if (!empty($keyword)) {
            $sql = "SELECT customer_id, customer_name, customer_sales,
                           ROUND(customer_leadtime / customer_delivery_count, 1) AS customer_average_leadtime
                    FROM customer
                    WHERE customer_id LIKE :keyword OR customer_name LIKE :keyword
                    ORDER BY customer_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $pdo->query("SELECT customer_id, customer_name, customer_sales,
                                        ROUND(customer_leadtime / customer_delivery_count, 1) AS customer_average_leadtime
                                 FROM customer
                                 ORDER BY customer_id");
        }
 
        return $stmt;
 
    } catch (PDOException $e) {
        echo "接続エラー: " . $e->getMessage();
        exit; // エラー時は明示的に終了
    }
}
 
function checkDB(){
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';
   
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        echo "<h2>顧客</h2>";
        $stmt = $pdo->query("SELECT * FROM customer");
        echo "<table border='1'>
            <tr><th>顧客ID</th><th>顧客名</th><th>担当者名</th><th>住所</th><th>電話番号</th><th>配達先条件等</th><th>備考</th><th>顧客登録日</th><th>累計売上</th><th>累計リードタイム</th><th>納品回数</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['customer_id']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['customer_person']}</td>
                <td>{$row['address']}</td>
                <td>{$row['phone_number']}</td>
                <td>{$row['delivery_notes']}</td>
                <td>{$row['customer_notes']}</td>
                <td>{$row['registration_date']}</td>
                <td>{$row['customer_sales']}</td>
                <td>{$row['customer_leadtime']}</td>
                <td>{$row['customer_delivery_count']}</td>
            </tr>";
        }
        echo "</table><br>";
   
        echo "<h2>納品書管理テーブル</h2>";
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
            <tr><th>納品商品連番</th><th>納品ID</th><th>注文商品連番</th><th>注文ID</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['delivery_product_number']}</td>
                <td>{$row['delivery_id']}</td>
                <td>{$row['order_product_number']}</td>
                <td>{$row['order_id']}</td>
            </tr>";
        }
        echo "</table><br>";
   
        echo "<h2>注文書管理</h2>";
        $stmt = $pdo->query("SELECT * FROM `order`");
        echo "<table border='1'>
            <tr><th>注文ID</th><th>顧客ID</th><th>作成日</th><th>納品日</th><th>状態</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['order_id']}</td>
                <td>{$row['customer_id']}</td>
                <td>{$row['order_date']}</td>
                <td>{$row['order_delivered_date']}</td>
                <td>{$row['order_state']}</td>
            </tr>";
        }
        echo "</table><br>";
   
        echo "<h2>注文明細</h2>";
        $stmt = $pdo->query("SELECT * FROM order_detail");
        echo "<table border='1'>
            <tr><th>注文商品連番</th><th>注文ID</th><th>品名</th><th>数量</th><th>未納品数量</th><th>単価</th><th>摘要</th><th>状態</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['order_product_number']}</td>
                <td>{$row['order_id']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['product_quantity']}</td>
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
}
 
// =============================
// 納品データ取得・削除用関数
// =============================
 
/**
 * 納品データ一覧を取得する関数
 * @param PDO $pdo DB接続済みPDOインスタンス
 * @return array 納品データ配列
 */
function getAllDeliveries($pdo) {
    $sql = "
        SELECT
            d.delivery_id,              -- 納品ID
            d.customer_id,              -- 顧客ID
            DATE(d.delivery_date) AS delivery_date, -- YYYY-MM-DD形式
            IFNULL(DATE_FORMAT(d.delivery_date, '%Y年%m月%d日'), '') AS formatted_date, -- 表示用形式(NULLの場合は空文字)
            c.customer_name             -- 顧客名
        FROM delivery d
        INNER JOIN customer c ON d.customer_id = c.customer_id -- 顧客IDで結合
        ORDER BY d.delivery_date DESC   -- 納品日が新しい順
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
 
/**
 * 指定した納品IDのレコードを削除する関数
 * @param PDO $pdo DB接続済みPDOインスタンス
 * @param string|int $delivery_id 削除対象の納品ID
 * @return bool 成功時true、失敗時false
 */
function deleteDeliveryById($pdo, $delivery_id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM delivery WHERE delivery_id = ?');
        return $stmt->execute([$delivery_id]);
    } catch (PDOException $e) {
        // エラー内容を表示（本番運用時はログ出力推奨）
        echo "削除失敗: " . $e->getMessage();
        return false;
    }
}

/**
 * 納品明細データを取得する関数
 * @param PDO $pdo
 * @param string|int $delivery_id
 * @return array
 */
function getDeliveryItems($pdo, $delivery_id) {
    $sql = "SELECT DISTINCT od.product_name, od.undelivered_quantity, od.product_price, od.product_quantity, od.order_product_number FROM delivery_detail AS dd INNER JOIN order_detail AS od ON dd.order_product_number = od.order_product_number WHERE dd.delivery_id = :delivery_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':delivery_id' => $delivery_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 納品明細の分納・数量更新処理
 * @param PDO $pdo
 * @param array $post POSTデータ
 */
function updateDeliveryDetails($pdo, $post) {
    if (!empty($post['order_product_number'])) {
        $count = count($post['order_product_number']);
        for ($i = 0; $i < $count; $i++) {
            $order_product_number = $post['order_product_number'][$i];
            $product_name = isset($post['product_name'][$i]) ? $post['product_name'][$i] : null;
            $delivery_qty = intval($post['product_quantity'][$i]);
            $original_qty = isset($post['original_product_quantity'][$i]) ? intval($post['original_product_quantity'][$i]) : null;
            $original_undelivered = isset($post['original_undelivered_quantity'][$i]) ? intval($post['original_undelivered_quantity'][$i]) : null;
            if ($original_qty !== null && $original_undelivered !== null && $product_name !== null && $delivery_qty > 0 && $delivery_qty <= $original_undelivered) {
                $new_delivered = $original_qty + $delivery_qty;
                $new_undelivered = $original_undelivered - $delivery_qty;
                $sql2 = "UPDATE order_detail SET product_quantity = :product_quantity, undelivered_quantity = :undelivered_quantity WHERE order_product_number = :order_product_number AND product_name = :product_name";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([
                    ':product_quantity' => $new_delivered,
                    ':undelivered_quantity' => $new_undelivered,
                    ':order_product_number' => $order_product_number,
                    ':product_name' => $product_name
                ]);
            }
        }
    }
}
?>