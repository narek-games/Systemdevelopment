<?php
require_once 'dbConnect.php';

$delivery_id = $_GET['delivery_id'] ?? '';
$items = [];
$customer_name = '';
$customer_id = '';
$delivery_date_for_input = '';

if (!empty($delivery_id)) {
    // delivery_detailを主役にして、関連するテーブルを正しく結合する
    $sql = "
        SELECT 
            d.delivery_date,
            d.customer_id,
            c.customer_name,
            dd.delivery_quantity,        -- この納品での「納品数量」
            od.product_name, 
            od.undelivered_quantity,     -- 注文明細の「現在の未納品数量」
            od.product_price, 
            od.order_product_number,
            od.order_id
        FROM 
            delivery_detail AS dd
        INNER JOIN 
            delivery AS d ON dd.delivery_id = d.delivery_id
        INNER JOIN 
            customer AS c ON d.customer_id = c.customer_id
        INNER JOIN 
            order_detail AS od ON dd.order_id = od.order_id AND dd.order_product_number = od.order_product_number
        WHERE 
            dd.delivery_id = :delivery_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':delivery_id' => $delivery_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($items)) {
        // ヘッダー情報を取得（どの行も同じはずなので最初の行から取得）
        $customer_id = $items[0]['customer_id'];
        $customer_name = $items[0]['customer_name'];
        $delivery_date_for_input = substr($items[0]['delivery_date'], 0, 10);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>納品書編集画面</title>
    <style>
        body { font-family: "Hiragino Kaku Gothic ProN", sans-serif; background-color: white; margin: 0; padding: 0; }
        header { position: sticky; top: 0; color: white; background-color: forestgreen; padding: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; border-radius: 16px; margin-bottom: 20px; }
        header h1 { margin: 0; font-size: 20px; }
        main { max-width: 1200px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; margin: 0 20px 20px 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], input[type="number"] { width: 100%; padding: 6px; box-sizing: border-box; font-family: "Hiragino Kaku Gothic ProN", sans-serif; }
        table { border-collapse: collapse; margin: 0 20px 20px 20px; width: calc(100% - 40px); }
        th, td { padding: 8px; text-align: center; border: 1px solid #999; }
        .button-group { margin: 20px; display: flex; justify-content: center; gap: 20px; }
        button { font-size: 16px; padding: 12px; width: 150px; border: 1px solid #ccc; border-radius: 8px; cursor: pointer; }
        .back-button { background-color: whitesmoke; }
        .save-button { background-color: cornflowerblue; color: white; }
        .save-button:hover { background-color: blue; }
        .back-button:hover { background-color: gray; }
        .readonly { background-color: #e0e0e0 !important; }
    </style>
</head>
<body>
    <header><h1>納品書編集画面</h1></header>
    <main>
        <form method="post" action="update_delivery.php">
            <div class="grid">
                <div class="form-group">
                    <label>納品ID</label>
                    <input type="text" class="readonly" value="<?= htmlspecialchars($delivery_id) ?>" readonly>
                    <input type="hidden" name="delivery_id" value="<?= htmlspecialchars($delivery_id) ?>">
                </div>
                <div class="form-group">
                    <label>日付</label>
                    <input type="date" name="delivery_date" value="<?= htmlspecialchars($delivery_date_for_input) ?>">
                </div>
                <div class="form-group">
                    <label>顧客ID</label>
                    <input type="text" name="customer_id" class="readonly" value="<?= htmlspecialchars($customer_id) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>顧客名</label>
                    <input type="text" class="readonly" value="<?= htmlspecialchars($customer_name) ?>" readonly>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>品名</th>
                        <th>納品数量</th>
                        <th>未納品数量</th>
                        <th>単価</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['product_name']) ?>
                                    <input type="hidden" name="items[<?= $item['order_product_number'] ?>][order_product_number]" value="<?= htmlspecialchars($item['order_product_number']) ?>">
                                    <input type="hidden" name="items[<?= $item['order_product_number'] ?>][order_id]" value="<?= htmlspecialchars($item['order_id']) ?>">
                                    <input type="hidden" name="items[<?= $item['order_product_number'] ?>][price]" value="<?= htmlspecialchars($item['product_price']) ?>">
                                    <input type="hidden" name="items[<?= $item['order_product_number'] ?>][original_quantity]" value="<?= htmlspecialchars($item['delivery_quantity']) ?>">
                                </td>
                                <td>
                                    <input type="number" name="items[<?= $item['order_product_number'] ?>][new_quantity]" value="<?= htmlspecialchars($item['delivery_quantity']) ?>" min="0">
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['undelivered_quantity']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(number_format($item['product_price'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">表示できる明細がありません。</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="button-group">
                <a href="./deliveryHome.php"><button type="button" class="back-button">戻る</button></a>
                <button type="submit" class="save-button">保存</button>
            </div>
        </form>
    </main>
</body>
</html>
