<?php
require_once 'dbConnect.php';

$orderId = $_GET['order-id'] ?? '';
$orderDate = $_GET['order-date'] ?? '';
$customerId = $_GET['customer-id'] ?? '';
$customerName = $_GET['customer-name'] ?? '';

// LEFT JOINとGROUP BYを使って、各注文の未納品数量の合計(total_undelivered)を取得
$sql = "
    SELECT 
        o.order_id, 
        o.customer_id, 
        c.customer_name, 
        o.order_date, 
        o.order_state,
        COALESCE(SUM(od.undelivered_quantity), 0) AS total_undelivered
    FROM `order` o
    JOIN customer c ON o.customer_id = c.customer_id
    LEFT JOIN order_detail od ON o.order_id = od.order_id
    WHERE 1
";
$params = [];

if ($orderId !== '') {
    $sql .= " AND o.order_id = ?";
    $params[] = $orderId;
}
if ($orderDate !== '') {
    $sql .= " AND DATE(o.order_date) = ?";
    try {
        $formattedDate = new DateTime($orderDate);
        $params[] = $formattedDate->format('Y-m-d');
    } catch (Exception $e) {
        $params[] = '0000-00-00';
    }
}
if ($customerId !== '') {
    $sql .= " AND o.customer_id = ?";
    $params[] = $customerId;
}
if ($customerName !== '') {
    $sql .= " AND c.customer_name LIKE ?";
    $params[] = "%$customerName%";
}

$sql .= " GROUP BY o.order_id, o.customer_id, c.customer_name, o.order_date, o.order_state";
$sql .= " ORDER BY o.order_date DESC";

$errorMsg = '';
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文書選択</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .search-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; display: inline-block; }
        .search-row { display: flex; align-items: center; margin-bottom: 10px; }
        .search-box label { display: block; margin-bottom: 5px; }
        .search-box .input-group { margin-right: 15px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        .order-link { color: blue; text-decoration: underline; cursor: pointer; }
        .order-id-completed { color: #999; text-decoration: line-through; }
        .button { padding: 10px 15px; font-size: 14px; border-radius: 8px; cursor: pointer; border: 1px solid #ccc; }
        .search-button { background-color: cornflowerblue; color: white; border-color: cornflowerblue; }
        .back_button { background-color: whitesmoke; }
        .create_button { background-color: cornflowerblue; color: white; border-color: cornflowerblue; }
        .create_button:disabled { background-color: #ccc; border-color: #ccc; cursor: not-allowed; }
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: white; padding: 25px; border-radius: 10px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        #popup-product-list { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 15px 0; }
        .product-item { display: block; margin: 8px 0; cursor: pointer; padding: 5px; border-radius: 4px; }
        .product-item:hover { background-color: #f0f0f0; }
        .product-item input[disabled] + span { color: #999; text-decoration: line-through; }
    </style>
</head>
<body>

    <h1>注文書選択</h1>
    <form class="search-box" method="get" id="searchForm">
        <div class="search-row">
            <div class="input-group">
                <label for="order-id">注文ID</label>
                <input type="text" id="order-id" name="order-id" value="<?= htmlspecialchars($orderId) ?>">
            </div>
            <div class="input-group">
                <label for="order-date">注文日</label>
                <input type="date" id="order-date" name="order-date" value="<?= htmlspecialchars($orderDate) ?>">
            </div>
        </div>
        <div class="search-row">
            <div class="input-group">
                <label for="customer-id">顧客ID</label>
                <input type="text" id="customer-id" name="customer-id" value="<?= htmlspecialchars($customerId) ?>">
            </div>
            <div class="input-group">
                <label for="customer-name">顧客名</label>
                <input type="text" id="customer-name" name="customer-name" value="<?= htmlspecialchars($customerName) ?>">
            </div>
            <button class="button search-button" type="submit">検索</button>
        </div>
    </form>

    <?php if ($errorMsg): ?>
        <div style="color:red; font-weight:bold; margin-bottom:20px;">SQLエラー: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr><th>注文ID</th><th>顧客ID</th><th>顧客名</th><th>注文日</th><th>状態</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td>
                    <?php if ($row['total_undelivered'] > 0): ?>
                        <a class="order-link" 
                           data-order-id="<?= htmlspecialchars($row['order_id']) ?>"
                           data-customer-id="<?= htmlspecialchars($row['customer_id']) ?>"
                           data-customer-name="<?= htmlspecialchars($row['customer_name']) ?>">
                           <?= htmlspecialchars($row['order_id']) ?>
                        </a>
                    <?php else: ?>
                        <span class="order-id-completed"><?= htmlspecialchars($row['order_id']) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['customer_id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['order_date']) ?></td>
                <td><?= $row['order_state'] ? '納品済' : '未納品' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div id="product-popup" class="popup-overlay">
        <div class="popup-content">
            <h2 id="popup-title">商品選択</h2>
            <div id="popup-product-list"></div>
            <div style="text-align:right; margin-top:15px;">
                <button id="close-popup-btn" class="button back_button">閉じる</button>
            </div>
        </div>
    </div>

    <form id="create-delivery-form" action="deliveryInsert.php" method="post">
        <button class="button back_button" type="button" onclick="location.href='deliveryHome.php'">戻る</button>
        <button class="button create_button" id="createBtn" type="submit" disabled>作成</button>
    </form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let selectedCustomerId = null;
    let selectedCustomerName = null;
    
    const orderLinks = document.querySelectorAll('.order-link');
    const popup = document.getElementById('product-popup');
    const popupTitle = document.getElementById('popup-title');
    const productListDiv = document.getElementById('popup-product-list');
    const closePopupBtn = document.getElementById('close-popup-btn');
    const createBtn = document.getElementById('createBtn');
    const createForm = document.getElementById('create-delivery-form');
    
    const selectedProducts = new Map();

    orderLinks.forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();
            const targetLink = e.currentTarget;
            const orderId = targetLink.dataset.orderId;
            
            // ★★★ ここから修正 ★★★
            // 顧客チェックのロジックをここからは削除
            // ★★★ ここまで修正 ★★★

            try {
                const response = await fetch(`get_order_details.php?order-id=${orderId}`);
                if (!response.ok) throw new Error('サーバーからの応答が正常ではありません。');
                const products = await response.json(); 
                
                // ★★★ ここから修正 ★★★
                // populateProductListに顧客情報も渡す
                const customerId = targetLink.dataset.customerId;
                const customerName = targetLink.dataset.customerName;
                populateProductList(products, customerId, customerName);
                // ★★★ ここまで修正 ★★★

                popupTitle.textContent = `商品選択 (注文ID: ${orderId})`;
                popup.style.display = 'flex';
            } catch (error) {
                console.error('商品データの取得に失敗しました:', error);
                alert('商品データの取得に失敗しました。');
            }
        });
    });
    
    closePopupBtn.addEventListener('click', () => { popup.style.display = 'none'; });
    popup.addEventListener('click', (e) => { if (e.target === popup) { popup.style.display = 'none'; } });

    createForm.addEventListener('submit', (e) => {
        const existingInputs = createForm.querySelectorAll('input[type="hidden"]');
        existingInputs.forEach(input => input.remove());

        if (selectedCustomerId) {
            const customerIdInput = document.createElement('input');
            customerIdInput.type = 'hidden';
            customerIdInput.name = 'customer_id';
            customerIdInput.value = selectedCustomerId;
            createForm.appendChild(customerIdInput);

            const customerNameInput = document.createElement('input');
            customerNameInput.type = 'hidden';
            customerNameInput.name = 'customer_name';
            customerNameInput.value = selectedCustomerName;
            createForm.appendChild(customerNameInput);
        }

        selectedProducts.forEach((productData, uniqueKey) => {
            const undeliveredInput = document.createElement('input');
            undeliveredInput.type = 'hidden';
            undeliveredInput.name = 'undelivered_quantities[]';
            undeliveredInput.value = productData.undelivered;
            createForm.appendChild(undeliveredInput);
            
            const orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.name = 'order_ids[]';
            orderIdInput.value = productData.orderId;
            createForm.appendChild(orderIdInput);

            const opnInput = document.createElement('input');
            opnInput.type = 'hidden';
            opnInput.name = 'order_product_numbers[]';
            opnInput.value = productData.opn;
            createForm.appendChild(opnInput);
            
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'product_names[]';
            nameInput.value = productData.name;
            createForm.appendChild(nameInput);

            const priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = 'product_prices[]';
            priceInput.value = productData.price;
            createForm.appendChild(priceInput);
        });
    });

    // ★★★ ここから修正 ★★★
    // populateProductListが顧客情報を受け取るように変更
    function populateProductList(products, customerId, customerName) {
        productListDiv.innerHTML = '';
        if (products.length === 0) {
            productListDiv.innerHTML = '<p>この注文に紐づく商品はありません。</p>';
            return;
        }
        products.forEach(product => {
            const uniqueKey = `${product.order_id}-${product.order_product_number}`;
            const isSelected = selectedProducts.has(uniqueKey);

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = product.order_product_number;
            checkbox.dataset.productName = product.product_name;
            checkbox.dataset.price = product.product_price;
            checkbox.dataset.orderId = product.order_id;
            checkbox.dataset.undelivered = product.undelivered_quantity;
            // ★★★ ここから修正 ★★★
            // checkboxに顧客情報を保持させる
            checkbox.dataset.customerId = customerId;
            checkbox.dataset.customerName = customerName;
            // ★★★ ここまで修正 ★★★
            checkbox.checked = isSelected;

            if (!isSelected && product.undelivered_quantity <= 0) {
                checkbox.disabled = true;
            }

            const label = document.createElement('label');
            label.className = 'product-item';
            label.appendChild(checkbox);
            const span = document.createElement('span');
            span.textContent = ` ${product.product_name} (未納品: ${product.undelivered_quantity})`;
            label.appendChild(span);
            
            checkbox.addEventListener('change', handleCheckboxChange);
            productListDiv.appendChild(label);
        });
    }

    function handleCheckboxChange(e) {
        const checkbox = e.target;
        const customerId = checkbox.dataset.customerId;
        const customerName = checkbox.dataset.customerName;

        // ★★★ ここから修正 ★★★
        // チェックを入れる瞬間に顧客をチェック・設定する
        if (checkbox.checked) {
            if (selectedCustomerId === null) {
                // 最初の選択なら、この顧客で確定
                selectedCustomerId = customerId;
                selectedCustomerName = customerName;
            } else if (selectedCustomerId !== customerId) {
                // 違う顧客を選ぼうとしたら、アラートを出してチェックを元に戻す
                alert('一度に選択できるのは、同じ顧客の注文のみです。');
                checkbox.checked = false;
                return; // 処理を中断
            }
        }
        // ★★★ ここまで修正 ★★★

        const orderId = checkbox.dataset.orderId;
        const orderProductNumber = checkbox.value;
        const productName = checkbox.dataset.productName;
        const price = checkbox.dataset.price;
        const undelivered = checkbox.dataset.undelivered;
        const uniqueKey = `${orderId}-${orderProductNumber}`;

        if (checkbox.checked) {
            selectedProducts.set(uniqueKey, { 
                opn: orderProductNumber, 
                orderId: orderId,
                name: productName, 
                price: price,
                undelivered: undelivered
            });
        } else {
            selectedProducts.delete(uniqueKey);
        }
        
        // 選択が0になったら顧客情報もリセット
        if (selectedProducts.size === 0) {
            selectedCustomerId = null;
            selectedCustomerName = null;
        }
        updateCreateButtonState();
    }

    function updateCreateButtonState() {
        createBtn.disabled = selectedProducts.size === 0;
    }
});
</script>
</body>
</html>
