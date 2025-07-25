<?php
session_start();
require_once 'dbConnect.php';

// POSTリクエストでなければ、ホーム画面に戻す
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['delivery_id'])) {
    header('Location: deliveryHome.php');
    exit;
}

// フォームからデータを受け取る
$delivery_id = $_POST['delivery_id'];
$delivery_date = $_POST['delivery_date'];
$customer_id = $_POST['customer_id'];
$items_from_form = $_POST['items'] ?? [];

$pdo->beginTransaction();

try {
    // 1. 納品日を更新
    $stmt_date = $pdo->prepare("UPDATE delivery SET delivery_date = ? WHERE delivery_id = ?");
    $stmt_date->execute([$delivery_date, $delivery_id]);
    
    // 2. 顧客の累計売上を再計算するために、変更「前」の納品合計額を取得
    $sql_old_total = "
        SELECT SUM(dd.delivery_quantity * od.product_price)
        FROM delivery_detail dd
        JOIN order_detail od ON dd.order_id = od.order_id AND dd.order_product_number = od.order_product_number
        WHERE dd.delivery_id = ?
    ";
    $stmt_old_total = $pdo->prepare($sql_old_total);
    $stmt_old_total->execute([$delivery_id]);
    $old_total_amount = $stmt_old_total->fetchColumn();

    $new_total_amount = 0;
    $affected_order_ids = [];

    // 3. 各明細の数量を更新
    $stmt_update_dd = $pdo->prepare("UPDATE delivery_detail SET delivery_quantity = :new_quantity WHERE delivery_id = :delivery_id AND order_product_number = :opn AND order_id = :order_id");
    $stmt_update_od = $pdo->prepare("UPDATE order_detail SET undelivered_quantity = undelivered_quantity + :diff WHERE order_product_number = :opn AND order_id = :order_id");

    foreach ($items_from_form as $opn => $item) {
        $new_quantity = (int)($item['new_quantity'] ?? 0);
        $original_quantity = (int)($item['original_quantity'] ?? 0);
        $order_id = $item['order_id'];
        
        // 数量の差分を計算（例：元々5個で、3個に修正した場合、差分は2。未納品数量に2を戻す）
        $quantity_diff = $original_quantity - $new_quantity;

        // 3-1. order_detailの未納品数量を更新（差分だけ戻す/減らす）
        $stmt_update_od->execute([
            ':diff' => $quantity_diff,
            ':opn' => $opn,
            ':order_id' => $order_id
        ]);
        
        // 3-2. delivery_detailの納品数量を更新
        $stmt_update_dd->execute([
            ':new_quantity' => $new_quantity,
            ':delivery_id' => $delivery_id,
            ':opn' => $opn,
            ':order_id' => $order_id
        ]);

        // 3-3. 新しい納品合計額を計算
        $new_total_amount += $new_quantity * (float)($item['price'] ?? 0);
        
        // 影響のあった注文IDを保存
        if (!in_array($order_id, $affected_order_ids)) {
            $affected_order_ids[] = $order_id;
        }
    }
    
    // 4. 顧客の累計売上を更新
    $sales_diff = $new_total_amount - $old_total_amount;
    if ($sales_diff != 0) {
        $stmt_sales = $pdo->prepare("UPDATE customer SET customer_sales = customer_sales + ? WHERE customer_id = ?");
        $stmt_sales->execute([$sales_diff, $customer_id]);
    }
    
    // 5. 注文の完了状態を再チェック
    $sql_check_undelivered = "SELECT SUM(undelivered_quantity) AS total_undelivered FROM order_detail WHERE order_id = ?";
    $stmt_check = $pdo->prepare($sql_check_undelivered);
    $sql_update_order_state = "UPDATE `order` SET order_state = ? WHERE order_id = ?";
    $stmt_update_state = $pdo->prepare($sql_update_order_state);

    foreach ($affected_order_ids as $order_id_to_check) {
        $stmt_check->execute([$order_id_to_check]);
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // 未納品数量が0以下なら「納品済(1)」、そうでなければ「未納品(0)」に設定
        $new_state = ($result && $result['total_undelivered'] <= 0) ? 1 : 0;
        $stmt_update_state->execute([$new_state, $order_id_to_check]);
    }

    $pdo->commit();
    $_SESSION['success_message'] = "納品書 (ID: {$delivery_id}) を更新しました。";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "更新中にエラーが発生しました: " . $e->getMessage();
}

// 処理が終わったら、一覧画面へ戻る
header("Location: deliveryHome.php");
exit;
?>