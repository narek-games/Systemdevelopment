<?php
require_once 'dbConnect.php';

$orderId = $_GET['order-id'] ?? '';
$orderDate = $_GET['order-date'] ?? '';
$customerId = $_GET['customer-id'] ?? '';
$customerName = $_GET['customer-name'] ?? '';

$sql = "
    SELECT o.order_id, o.customer_id, c.customer_name, o.order_date, o.order_state
    FROM `order` o
    JOIN customer c ON o.customer_id = c.customer_id
    WHERE 1
";
$params = [];

if ($orderId !== '') {
    $sql .= " AND o.order_id = ?";
    $params[] = $orderId;
}
if ($orderDate !== '') {
    $sql .= " AND o.order_date = ?";
    $params[] = $orderDate;
}
if ($customerId !== '') {
    $sql .= " AND o.customer_id = ?";
    $params[] = $customerId;
}
if ($customerName !== '') {
    $sql .= " AND c.customer_name LIKE ?";
    $params[] = "%$customerName%";
}

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
        .search-box label { margin-right: 5px; }
        .search-box input { margin-right: 15px; padding: 5px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        .order-link { color: blue; text-decoration: underline; cursor: pointer; }
        .button { padding: 10px 15px; font-size: 14px; border-radius: 8px; cursor: pointer; border: 1px solid #ccc; }
        .search-button { background-color: cornflowerblue; color: white; }
        .back_button { background-color: whitesmoke; }
        .create_button { background-color: cornflowerblue; color: white; }
        .create_button:disabled { background-color: #ccc; cursor: not-allowed; }
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: white; padding: 25px; border-radius: 10px; width: 90%; max-width: 500px; }
        #popup-product-list { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 15px 0; }
        .product-item { display: block; margin: 8px 0; cursor: pointer; }
    </style>
</head>
<body>

    <h1>注文書選択</h1>
    <form class="search-box" method="get" id="searchForm">
        <div class="search-row">
            <label for="order-id">注文ID</label>
            <input type="text" id="order-id" name="order-id" value="<?= htmlspecialchars($orderId) ?>">
            <label for="order-date">注文日</label>
            <input type="date" id="order-date" name="order-date" value="<?= htmlspecialchars($orderDate) ?>">
        </div>
        <div class="search-row">
            <label for="customer-id">顧客ID</label>
            <input type="text" id="customer-id" name="customer-id" value="<?= htmlspecialchars($customerId) ?>">
            <label for="customer-name">顧客名</label>
            <input type="text" id="customer-name" name="customer-name" value="<?= htmlspecialchars($customerName) ?>">
            <button class="button search-button" type="submit">検索</button>
        </div>
    </form>

    <?php if ($errorMsg): ?>
        <div style="color:red; font-weight:bold; margin-bottom:20px;">SQLエラー: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>注文ID</th>
                <th>顧客ID</th>
                <th>顧客名</th>
                <th>注文日</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td>
                    <a class="order-link" 
                       data-order-id="<?= htmlspecialchars($row['order_id']) ?>"
                       data-customer-id="<?= htmlspecialchars($row['customer_id']) ?>"
                       data-customer-name="<?= htmlspecialchars($row['customer_name']) ?>">
                       <?= htmlspecialchars($row['order_id']) ?>
                    </a>
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
        <button class="button back_button" type="button" onclick="location.href='deliveryHome.html'">戻る</button>
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
    
    // キーを複合キー（orderId-orderProductNumber）にして商品を管理
    const selectedProducts = new Map();

    orderLinks.forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();
            const targetLink = e.currentTarget;
            const orderId = targetLink.dataset.orderId;
            const customerId = targetLink.dataset.customerId;
            const customerName = targetLink.dataset.customerName;

            if (selectedCustomerId === null) {
                selectedCustomerId = customerId;
                selectedCustomerName = customerName;
            } else if (selectedCustomerId !== customerId) {
                alert('一度に選択できるのは、同じ顧客の注文のみです。');
                return;
            }

            try {
                const response = await fetch(`get_order_details.php?order-id=${orderId}`);
                if (!response.ok) throw new Error('サーバーからの応答が正常ではありません。');
                const products = await response.json(); 
                
                // ★修正: populateProductList に orderId を渡す
                populateProductList(products, orderId);
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

        // ★修正: productDataから元のIDを取り出して送信
        selectedProducts.forEach((productData, uniqueKey) => {
            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'order_product_numbers[]';
            inputId.value = productData.opn; // 元の注文商品番号
            createForm.appendChild(inputId);
            
            const inputName = document.createElement('input');
            inputName.type = 'hidden';
            inputName.name = 'product_names[]';
            inputName.value = productData.name;
            createForm.appendChild(inputName);

            const inputPrice = document.createElement('input');
            inputPrice.type = 'hidden';
            inputPrice.name = 'product_prices[]';
            inputPrice.value = productData.price;
            createForm.appendChild(inputPrice);
        });
    });

    // ★修正: populateProductList が orderId を受け取るように変更
    function populateProductList(products, orderId) {
        productListDiv.innerHTML = '';
        if (products.length === 0) {
            productListDiv.innerHTML = '<p>この注文に紐づく商品はありません。</p>';
            return;
        }
        products.forEach(product => {
            // ★修正: 複合キーを作成して選択状態を確認
            const uniqueKey = `${orderId}-${product.order_product_number}`;

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = product.order_product_number;
            checkbox.dataset.productName = product.product_name;
            checkbox.dataset.price = product.product_price;
            checkbox.dataset.orderId = orderId; // ★修正: checkboxにorderIdを保持させる
            checkbox.checked = selectedProducts.has(uniqueKey); // ★修正: 複合キーで確認

            const label = document.createElement('label');
            label.className = 'product-item';
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(` ${product.product_name}`));
            
            checkbox.addEventListener('change', handleCheckboxChange);
            productListDiv.appendChild(label);
        });
    }

    // ★修正: handleCheckboxChange で複合キーを使用
    function handleCheckboxChange(e) {
        const checkbox = e.target;
        const orderId = checkbox.dataset.orderId;
        const orderProductNumber = checkbox.value;
        const productName = checkbox.dataset.productName;
        const price = checkbox.dataset.price;
        
        // ★修正: 複合キーを作成
        const uniqueKey = `${orderId}-${orderProductNumber}`;

        if (checkbox.checked) {
            // ★修正: 複合キーでセットし、元のIDもオブジェクト内に保持
            selectedProducts.set(uniqueKey, { 
                opn: orderProductNumber, 
                name: productName, 
                price: price 
            });
        } else {
            selectedProducts.delete(uniqueKey);
        }
        
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