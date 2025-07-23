<?php
session_start();
require_once 'dbConnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['items'])) {
    $_SESSION['error_message'] = "送信されたデータが空か、不正なリクエストです。";
    header('Location: deliveryInsert.php');
    exit;
}

$delivery_date = $_POST['delivery_date'];
$customer_id = $_POST['customer_id'];
$items_from_form = $_POST['items'];

if (empty($items_from_form[0]['order_product_number'])) {
    $_SESSION['error_message'] = "商品データが正しく送信されませんでした。";
    header('Location: deliveryInsert.php');
    exit;
}

$pdo->beginTransaction();

try {
    // 1. delivery_id をPHP側で採番
    $sql_max_id = "SELECT MAX(delivery_id) AS max_id FROM delivery";
    $stmt_max_id = $pdo->query($sql_max_id);
    $max_id_row = $stmt_max_id->fetch(PDO::FETCH_ASSOC);
    $next_delivery_num = 1; 
    if ($max_id_row && $max_id_row['max_id']) {
        $current_number = intval(substr($max_id_row['max_id'], 2));
        $next_delivery_num = $current_number + 1;
    }
    $new_delivery_id = 'DE' . str_pad($next_delivery_num, 6, '0', STR_PAD_LEFT);

    // 2. `delivery` テーブルにINSERT
    $sql_delivery = "INSERT INTO delivery (delivery_id, delivery_date, customer_id) VALUES (?, ?, ?)";
    $stmt_delivery = $pdo->prepare($sql_delivery);
    $stmt_delivery->execute([$new_delivery_id, $delivery_date, $customer_id]);


    // ★★★ ここからが今回の修正 ★★★

    // 3. delivery_product_number の現在最大値を取得
    $sql_max_dpn = "SELECT MAX(delivery_product_number) AS max_dpn FROM delivery_detail";
    $stmt_max_dpn = $pdo->query($sql_max_dpn);
    $max_dpn_row = $stmt_max_dpn->fetch(PDO::FETCH_ASSOC);
    $next_dpn = 1; // デフォルトは1
    if ($max_dpn_row && $max_dpn_row['max_dpn']) {
        $next_dpn = $max_dpn_row['max_dpn'] + 1;
    }

    // 4. delivery_detailへのINSERT と order_detailのUPDATE をループで実行
    // INSERT文に delivery_product_number を追加
    $sql_detail_insert = "INSERT INTO delivery_detail (delivery_product_number, delivery_id, order_id, order_product_number) VALUES (?, ?, ?, ?)";
    $stmt_detail_insert = $pdo->prepare($sql_detail_insert);
    
    $sql_order_update = "UPDATE order_detail SET undelivered_quantity = undelivered_quantity - ? WHERE order_product_number = ?";
    $stmt_order_update = $pdo->prepare($sql_order_update);

    $affected_order_ids = [];

    foreach ($items_from_form as $item) {
        $order_id = $item['order_id'];
        $opn = $item['order_product_number'];
        $quantity = $item['quantity'] ?? 0;

        if ($quantity <= 0) continue;
        
        // INSERT文に、計算した次の明細ID ($next_dpn) を含める
        $stmt_detail_insert->execute([$next_dpn, $new_delivery_id, $order_id, $opn]);
        
        $stmt_order_update->execute([$quantity, $opn]);

        if (!in_array($order_id, $affected_order_ids)) {
            $affected_order_ids[] = $order_id;
        }

        // 次のループのために明細IDを1増やす
        $next_dpn++;
    }
    // ★★★ ここまでが今回の修正 ★★★

    // 5. 注文の完了状態をチェック・更新
    $sql_check_undelivered = "SELECT SUM(undelivered_quantity) AS total_undelivered FROM order_detail WHERE order_id = ?";
    $stmt_check = $pdo->prepare($sql_check_undelivered);
    $sql_update_order_state = "UPDATE `order` SET order_state = 1 WHERE order_id = ?";
    $stmt_update_state = $pdo->prepare($sql_update_order_state);

    foreach ($affected_order_ids as $order_id_to_check) {
        $stmt_check->execute([$order_id_to_check]);
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
        if ($result && $result['total_undelivered'] <= 0) {
            $stmt_update_state->execute([$order_id_to_check]);
        }
    }

    $pdo->commit();

    $_SESSION['success_message'] = "納品書 (ID: {$new_delivery_id}) が正常に保存されました。";
    header('Location: deliveryHome.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "保存中にデータベースエラーが発生しました: " . $e->getMessage();
    header('Location: deliveryInsert.php');
    exit;
}
?>
