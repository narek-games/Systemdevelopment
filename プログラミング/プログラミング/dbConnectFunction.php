<?php
// dbConnectFunction.php
 
function getStatistics($keyword = '')
{
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';
 
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
        // SQLクエリでCOALESCE関数を使用し、NULLを空の文字列に変換
        // ROUND関数による計算結果もNULLになる可能性があるため、COALESCEで囲みます
        $base_sql = "SELECT
                            customer_id,
                            COALESCE(customer_name, '') AS customer_name,
                            COALESCE(customer_sales, '') AS customer_sales,
                            COALESCE(ROUND(customer_leadtime / NULLIF(customer_delivery_count, 0), 1), '') AS customer_average_leadtime
                        FROM customer";
 
        // NULLIF(customer_delivery_count, 0) は、customer_delivery_count が 0 の場合に NULL を返します。
        // これにより「0による除算」を避けることができます。
        // その結果が NULL になる場合も考慮して、外側の COALESCE で '' に変換します。
 
 
        // 検索キーワードがある場合は WHERE 句を追加
        if (!empty($keyword)) {
            $sql = $base_sql . " WHERE customer_id LIKE :keyword OR customer_name LIKE :keyword ORDER BY customer_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = $base_sql . " ORDER BY customer_id";
            $stmt = $pdo->query($sql);
        }
 
        return $stmt;
 
    } catch (PDOException $e) {
        // エラーをログに記録するか、よりユーザーフレンドリーなメッセージを表示することを検討
        error_log("データベース接続エラーまたはクエリ実行エラー: " . $e->getMessage());
        // 開発中はエラーを表示しても良いですが、本番環境ではセキュリティ上非推奨
        echo "データベースエラーが発生しました。システム管理者にお問い合わせください。";
        exit; // エラー時は明示的に終了
    }
}
 
// checkDB関数やgetAllDeliveries関数、deleteDeliveryById関数は変更なし
// ただし、checkDB関数内でも表示されるNULLをなくしたい場合は、同様にCOALESCE関数を適用してください
function checkDB(){
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';
 
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
        echo "<h2>顧客</h2>";
        // COALESCE を適用
        $stmt = $pdo->query("SELECT
                                customer_id,
                                COALESCE(customer_name, '') AS customer_name,
                                COALESCE(customer_person, '') AS customer_person,
                                COALESCE(address, '') AS address,
                                COALESCE(phone_number, '') AS phone_number,
                                COALESCE(delivery_notes, '') AS delivery_notes,
                                COALESCE(customer_notes, '') AS customer_notes,
                                COALESCE(registration_date, '') AS registration_date,
                                COALESCE(customer_sales, '') AS customer_sales,
                                COALESCE(customer_leadtime, '') AS customer_leadtime,
                                COALESCE(customer_delivery_count, '') AS customer_delivery_count
                            FROM customer");
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
 
        // 他のテーブル (delivery, delivery_detail, order, order_detail) も同様に
        // COALESCE を適用したいカラムがあれば追加してください。
        // 例えば、delivery_detail の product_abstract など。
 
        echo "<h2>納品書管理テーブル</h2>";
        $stmt = $pdo->query("SELECT delivery_id, delivery_date, customer_id, COALESCE(tax_rate, '') AS tax_rate FROM delivery");
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
        $stmt = $pdo->query("SELECT delivery_product_number, delivery_id, order_product_number, order_id FROM delivery_detail");
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
        $stmt = $pdo->query("SELECT order_id, customer_id, order_date, order_delivered_date, COALESCE(order_state, '') AS order_state FROM `order`");
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
        $stmt = $pdo->query("SELECT
                                order_product_number,
                                order_id,
                                COALESCE(product_name, '') AS product_name,
                                COALESCE(product_quantity, '') AS product_quantity,
                                COALESCE(undelivered_quantity, '') AS undelivered_quantity,
                                COALESCE(product_price, '') AS product_price,
                                COALESCE(product_abstract, '') AS product_abstract,
                                COALESCE(product_state, '') AS product_state
                            FROM order_detail");
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
        error_log("データベース接続エラー: " . $e->getMessage());
        echo "データベースエラーが発生しました。システム管理者にお問い合わせください。";
    }
}
 
/**
 * 納品データ一覧を取得する関数
 * @param PDO $pdo DB接続済みPDOインスタンス
 * @return array 納品データ配列
 */
function getAllDeliveries($pdo) {
    $sql = "
        SELECT
            d.delivery_id,             -- 納品ID
            d.customer_id,             -- 顧客ID
            DATE(d.delivery_date) AS delivery_date, -- YYYY-MM-DD形式
            IFNULL(DATE_FORMAT(d.delivery_date, '%Y年%m月%d日'), '') AS formatted_date, -- 表示用形式(NULLの場合は空文字)
            c.customer_name            -- 顧客名
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
        error_log("削除失敗: " . $e->getMessage());
        echo "削除失敗: " . $e->getMessage(); // 開発用
        return false;
    }
}
?>
 