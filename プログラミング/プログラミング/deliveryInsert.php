<?php
$customer_id = $_POST['customer_id'] ?? '';
$customer_name = $_POST['customer_name'] ?? '';

$order_ids = $_POST['order_ids'] ?? [];
$order_product_numbers = $_POST['order_product_numbers'] ?? [];
$product_names = $_POST['product_names'] ?? [];
$product_prices = $_POST['product_prices'] ?? [];
$undelivered_quantities = $_POST['undelivered_quantities'] ?? [];

$items = [];
if (!empty($order_product_numbers)) {
    foreach ($order_product_numbers as $index => $opn) {
        $items[] = [
            'order_id'             => $order_ids[$index] ?? '',
            'order_product_number' => $opn,
            'name'                 => $product_names[$index] ?? '名称不明',
            'price'                => $product_prices[$index] ?? 0,
            'quantity'             => 1, // 初期表示の数量
            'undelivered'          => $undelivered_quantities[$index] ?? 0
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>納品書作成画面</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; }
        .main-content { padding: 20px; max-width: 900px; margin: auto; }
        h1 { margin-bottom: 20px; border-bottom: 2px solid cornflowerblue; padding-bottom: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .readonly { background-color: #f0f0f0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table th, table td { border: 1px solid #999; padding: 10px; text-align: center; }
        table td:first-child { text-align: left; }
        .tax-options { margin-bottom: 20px; font-size: 16px; }
        .buttons { display: flex; justify-content: center; gap: 40px; margin-top: 30px; }
        .button { width: 150px; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; text-decoration: none; text-align: center; }
        .reset-btn { background-color: #f0f0f0; border: 1px solid #ccc; color: black; }
        .submit-btn { background-color: cornflowerblue; border: 1px solid #ccc; color: white; }
    </style>
</head>
<body>
    <main class="main-content">
        <h1>納品書作成画面</h1>
        
        <?php if (empty($items)): ?>
            <p style="color:red; border:1px solid red; padding:15px; border-radius:8px;">商品が選択されていません。前のページに戻って商品を選択してください。</p>
            <div class="buttons">
                <a href="orderOption.php" class="button reset-btn">戻る</a>
            </div>
        <?php else: ?>
            <form id="deliveryForm" action="save_delivery.php" method="post">
                <div class="grid">
                    <div class="form-group">
                        <label>納品ID</label>
                        <input type="text" class="readonly" value="(自動採番)" readonly>
                    </div>
                    <div class="form-group">
                        <label>日付</label>
                        <input type="date" id="deliveryDate" name="delivery_date" required>
                    </div>
                    <div class="form-group">
                        <label>顧客ID</label>
                        <input type="text" name="customer_id" class="readonly" value="<?= htmlspecialchars($customer_id) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>顧客名</label>
                        <input type="text" name="customer_name" class="readonly" value="<?= htmlspecialchars($customer_name) ?>" readonly>
                    </div>
                </div>

                <table id="itemTable">
                    <thead>
                        <tr><th>品名</th><th>数量</th><th>単価</th><th>金額</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr class="item-row">
                                <input type="hidden" name="items[<?= $index ?>][order_id]" value="<?= htmlspecialchars($item['order_id']) ?>">
                                <input type="hidden" name="items[<?= $index ?>][order_product_number]" value="<?= htmlspecialchars($item['order_product_number']) ?>">
                                <input type="hidden" name="items[<?= $index ?>][price]" value="<?= htmlspecialchars($item['price']) ?>">
                                
                                <td><input type="text" name="items[<?= $index ?>][name]" value="<?= htmlspecialchars($item['name']) ?>" class="readonly" readonly></td>
                                <td>
                                    <input type="number" 
                                           name="items[<?= $index ?>][quantity]" 
                                           value="<?= htmlspecialchars($item['quantity']) ?>" 
                                           min="1" 
                                           max="<?= htmlspecialchars($item['undelivered']) ?>" 
                                           class="qty" 
                                           oninput="validateQuantity(this)" 
                                           required>
                                </td>
                                <td><input type="number" value="<?= htmlspecialchars($item['price']) ?>" class="price readonly" readonly></td>
                                <td><input type="number" class="total" value="<?= htmlspecialchars($item['quantity'] * $item['price']) ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="tax-options">
                    <label><input type="radio" name="tax_option" value="excluded" checked onclick="updateAll()"> 税抜</label>
                    <label><input type="radio" name="tax_option" value="included" onclick="updateAll()"> 税込</label>
                </div>

                <div class="buttons">
                    <a href="orderOption.php" class="button reset-btn">戻る</a>
                    <button type="submit" class="button submit-btn">保存</button>
                </div>
            </form>
        <?php endif; ?>
    </main>

<script>
    const taxRate = 0.1;

    function updateRow(input) {
        const row = input.closest('.item-row');
        const qty = parseFloat(input.value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const taxOption = document.querySelector('input[name="tax_option"]:checked').value;
        let total = qty * price;
        if (taxOption === 'included') {
            total *= (1 + taxRate);
        }
        row.querySelector('.total').value = Math.round(total);
    }

    function validateQuantity(input) {
        const max = parseInt(input.max, 10);
        const value = parseInt(input.value, 10);

        if (value > max) {
            alert(`数量は未納品数量（${max}）を超えることはできません。`);
            input.value = max;
        }
        if (value < 0) {
            input.value = 1;
        }
        updateRow(input);
    }

    function updateAll() {
        document.querySelectorAll('.qty').forEach(input => {
            if (input.value) {
                updateRow(input);
            }
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const dateField = document.getElementById('deliveryDate');
        if (dateField) {
            dateField.value = `${yyyy}-${mm}-${dd}`;
        }
    });
</script>
</body>
</html>
