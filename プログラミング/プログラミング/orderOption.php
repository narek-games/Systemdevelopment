<?php
require_once 'dbConnect.php';

// 1. パラメータの受け取り
$orderId = $_GET['order-id'] ?? '';
$orderDate = $_GET['order-date'] ?? '';
$customerId = $_GET['customer-id'] ?? '';
$customerName = $_GET['customer-name'] ?? '';
$sortable_columns = ['order_id', 'customer_name', 'order_date'];
$sort_column = $_GET['sort'] ?? 'order_date';
$sort_direction = $_GET['dir'] ?? 'DESC';

// パラメータの検証
if (!in_array($sort_column, $sortable_columns)) {
    $sort_column = 'order_date';
}
if (!in_array(strtoupper($sort_direction), ['ASC', 'DESC'])) {
    $sort_direction = 'DESC';
}

// 2. SQLの組み立て
$sql = "
    SELECT 
        o.order_id, o.customer_id, c.customer_name, o.order_date, o.order_state,
        COALESCE(SUM(od.undelivered_quantity), 0) AS total_undelivered
    FROM `order` o
    JOIN customer c ON o.customer_id = c.customer_id
    LEFT JOIN order_detail od ON o.order_id = od.order_id
    WHERE 1
";
$params = [];

if ($orderId !== '') { $sql .= " AND o.order_id = ?"; $params[] = $orderId; }
if ($orderDate !== '') { $sql .= " AND DATE(o.order_date) = ?"; try { $d = new DateTime($orderDate); $params[] = $d->format('Y-m-d'); } catch (Exception $e) { $params[] = '0000-00-00'; } }
if ($customerId !== '') { $sql .= " AND o.customer_id = ?"; $params[] = $customerId; }
if ($customerName !== '') { $sql .= " AND c.customer_name LIKE ?"; $params[] = "%$customerName%"; }

$sql .= " GROUP BY o.order_id, o.customer_id, c.customer_name, o.order_date, o.order_state";
$sql .= " ORDER BY " . $sort_column . " " . $sort_direction;

// 3. DB実行と結果の取得
$errorMsg = '';
$results = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}

// 4. AJAXリクエストの場合、JSONを返して終了
if (isset($_GET['json']) && $_GET['json'] == '1') {
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
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
        th[data-sort] { cursor: pointer; user-select: none; }
        th[data-sort]:hover { background-color: #f0f0f0; }
        .sort-asc::after { content: ' ▲'; font-size: 0.8em; }
        .sort-desc::after { content: ' ▼'; font-size: 0.8em; }
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
            <div class="input-group"><label for="order-id">注文ID</label><input type="text" id="order-id" name="order-id" value="<?= htmlspecialchars($orderId) ?>"></div>
            <div class="input-group"><label for="order-date">注文日</label><input type="date" id="order-date" name="order-date" value="<?= htmlspecialchars($orderDate) ?>"></div>
        </div>
        <div class="search-row">
            <div class="input-group"><label for="customer-id">顧客ID</label><input type="text" id="customer-id" name="customer-id" value="<?= htmlspecialchars($customerId) ?>"></div>
            <div class="input-group"><label for="customer-name">顧客名</label><input type="text" id="customer-name" name="customer-name" value="<?= htmlspecialchars($customerName) ?>"></div>
            <button class="button search-button" type="submit">検索</button>
        </div>
    </form>

    <?php if ($errorMsg): ?>
        <div style="color:red; font-weight:bold; margin-bottom:20px;">SQLエラー: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th data-sort="order_id">注文ID</th>
                <th>顧客ID</th>
                <th data-sort="customer_name">顧客名</th>
                <th data-sort="order_date" data-dir="DESC" class="sort-desc">注文日</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody id="order-table-body">
            </tbody>
    </table>

    <div id="product-popup" class="popup-overlay">
        <div class="popup-content">
            <h2 id="popup-title">商品選択</h2>
            <div id="popup-product-list"></div>
            <div style="text-align:right; margin-top:15px;"><button id="close-popup-btn" class="button back_button">閉じる</button></div>
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
    const selectedProducts = new Map();

    const orderTableBody = document.getElementById('order-table-body');
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    const createForm = document.getElementById('create-delivery-form');
    const createBtn = document.getElementById('createBtn');
    
    const initialOrders = <?= json_encode($results) ?>;
    
    // --- メイン関数 ---

    function renderTable(orders) {
        orderTableBody.innerHTML = '';
        orders.forEach(row => {
            const tr = document.createElement('tr');
            let orderIdCellContent;
            if (row.total_undelivered > 0) {
                orderIdCellContent = `<a class="order-link" href="#" data-order-id="${row.order_id}" data-customer-id="${row.customer_id}" data-customer-name="${row.customer_name}">${row.order_id}</a>`;
            } else {
                orderIdCellContent = `<span class="order-id-completed">${row.order_id}</span>`;
            }
            tr.innerHTML = `
                <td>${orderIdCellContent}</td>
                <td>${row.customer_id}</td>
                <td>${row.customer_name}</td>
                <td>${row.order_date}</td>
                <td>${row.order_state == 1 ? '納品済' : '未納品'}</td>
            `;
            orderTableBody.appendChild(tr);
        });
        addEventListenersToOrderLinks();
    }
    
    async function handleSort(e) {
        const header = e.currentTarget;
        const sortColumn = header.dataset.sort;
        const currentDir = header.dataset.dir || 'ASC';
        const nextDir = (currentDir === 'ASC') ? 'DESC' : 'ASC';

        const searchParams = new URLSearchParams(new FormData(document.getElementById('searchForm')));
        searchParams.set('sort', sortColumn);
        searchParams.set('dir', nextDir);
        searchParams.set('json', '1');

        try {
            const response = await fetch(`?${searchParams.toString()}`);
            const sortedOrders = await response.json();
            renderTable(sortedOrders);
            updateSortHeaders(header, nextDir);
        } catch (error) {
            console.error('並び替えデータの取得に失敗しました:', error);
            alert('並び替えに失敗しました。');
        }
    }
    
    function updateSortHeaders(activeHeader, direction) {
        sortableHeaders.forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
            delete th.dataset.dir;
        });
        activeHeader.dataset.dir = direction;
        activeHeader.classList.add(direction === 'ASC' ? 'sort-asc' : 'sort-desc');
    }

    function addEventListenersToOrderLinks() {
        document.querySelectorAll('.order-link').forEach(link => {
            link.addEventListener('click', handleOrderLinkClick);
        });
    }

    async function handleOrderLinkClick(e) {
        e.preventDefault();
        const targetLink = e.currentTarget;
        const orderId = targetLink.dataset.orderId;
        try {
            const response = await fetch(`get_order_details.php?order-id=${orderId}`);
            if (!response.ok) throw new Error('サーバーからの応答が正常ではありません。');
            const products = await response.json(); 
            const customerId = targetLink.dataset.customerId;
            const customerName = targetLink.dataset.customerName;
            populateProductList(products, customerId, customerName);
            document.getElementById('popup-title').textContent = `商品選択 (注文ID: ${orderId})`;
            document.getElementById('product-popup').style.display = 'flex';
        } catch (error) {
            console.error('商品データの取得に失敗しました:', error);
            alert('商品データの取得に失敗しました。');
        }
    }

    // --- ポップアップ関連の関数 (変更なし) ---
    function populateProductList(products, customerId, customerName) {
        const productListDiv = document.getElementById('popup-product-list');
        productListDiv.innerHTML = '';
        if (products.length === 0) { productListDiv.innerHTML = '<p>この注文に紐づく商品はありません。</p>'; return; }
        products.forEach(product => {
            const uniqueKey = `${product.order_id}-${product.order_product_number}`;
            const isSelected = selectedProducts.has(uniqueKey);
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = product.order_product_number;
            Object.assign(checkbox.dataset, {
                productName: product.product_name, price: product.product_price,
                orderId: product.order_id, undelivered: product.undelivered_quantity,
                customerId: customerId, customerName: customerName
            });
            checkbox.checked = isSelected;
            if (!isSelected && product.undelivered_quantity <= 0) checkbox.disabled = true;
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
        const { customerId, customerName, orderId, undelivered, price, productName } = checkbox.dataset;
        if (checkbox.checked) {
            if (selectedCustomerId === null) {
                selectedCustomerId = customerId;
                selectedCustomerName = customerName;
            } else if (selectedCustomerId !== customerId) {
                alert('一度に選択できるのは、同じ顧客の注文のみです。');
                checkbox.checked = false; return;
            }
        }
        const uniqueKey = `${orderId}-${checkbox.value}`;
        if (checkbox.checked) {
            selectedProducts.set(uniqueKey, { opn: checkbox.value, orderId, name: productName, price, undelivered });
        } else {
            selectedProducts.delete(uniqueKey);
        }
        if (selectedProducts.size === 0) {
            selectedCustomerId = null;
            selectedCustomerName = null;
        }
        createBtn.disabled = selectedProducts.size === 0;
    }

    // --- フォーム送信・ポップアップ閉じるイベント ---
    document.getElementById('close-popup-btn').addEventListener('click', () => { document.getElementById('product-popup').style.display = 'none'; });
    document.getElementById('product-popup').addEventListener('click', (e) => { if (e.target === e.currentTarget) e.currentTarget.style.display = 'none'; });
    createForm.addEventListener('submit', (e) => {
        const existingInputs = createForm.querySelectorAll('input[type="hidden"]');
        existingInputs.forEach(input => input.remove());
        if (selectedCustomerId) {
            const customerIdInput = document.createElement('input'); customerIdInput.type = 'hidden'; customerIdInput.name = 'customer_id'; customerIdInput.value = selectedCustomerId; createForm.appendChild(customerIdInput);
            const customerNameInput = document.createElement('input'); customerNameInput.type = 'hidden'; customerNameInput.name = 'customer_name'; customerNameInput.value = selectedCustomerName; createForm.appendChild(customerNameInput);
        }
        selectedProducts.forEach((productData, uniqueKey) => {
            for (const key in productData) {
                const input = document.createElement('input'); input.type = 'hidden';
                const nameMap = { opn: 'order_product_numbers', name: 'product_names', price: 'product_prices', undelivered: 'undelivered_quantities', orderId: 'order_ids' };
                input.name = `${nameMap[key]}[]`; input.value = productData[key]; createForm.appendChild(input);
            }
        });
    });

    // --- 初期化実行 ---
    renderTable(initialOrders);
    sortableHeaders.forEach(header => header.addEventListener('click', handleSort));
});
</script>
</body>
</html>
