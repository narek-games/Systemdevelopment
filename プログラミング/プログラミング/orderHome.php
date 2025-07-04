<?php
require_once 'dbConnect.php';

$sql = "
    SELECT 
        o.order_id,
        c.customer_id,
        c.customer_name,
        o.order_date,
        o.order_state
    FROM 
        `order` o
    JOIN 
        customer c ON o.customer_id = c.customer_id
";

$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文書管理画面</title>
    <style>
        body {
            font-family: "メイリオ", sans-serif;
            text-align: center;
        }

        h2 {
            margin-top: 20px;
        }

        input[type="text"] {
            width: 300px;
            height: 30px;
            font-size: 16px;
            padding: 5px;
            margin: 10px;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .table-wrapper {
            width: 90%;
            margin: auto;
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #888;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .status-pending {
            color: red;
            font-weight: bold;
        }

        .status-complete {
            color: green;
            font-weight: bold;
        }

        .edit-link {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }

        .delete-link {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 40px;
        }

        .btn {
            display: inline-block;
            width: 250px;
            padding: 15px;
            font-size: 20px;
            background-color: #c7dbf3;
            border: 1px solid #888;
            border-radius: 8px;
            box-shadow: 2px 2px 3px #888;
            color: black;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #a5c6ed;
        }
    </style>
</head>
<body>
    <h2>注文書管理画面</h2>

    <div class="search-container">
        <input type="text" id="searchBox" placeholder="顧客ID・顧客名・注文IDで検索">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>注文ID</th>
                    <th>顧客ID</th>
                    <th>顧客名</th>
                    <th>作成日</th>
                    <th>状態</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr 
                            data-order-id="<?= htmlspecialchars($row['order_id']) ?>" 
                            data-customer-id="<?= htmlspecialchars($row['customer_id']) ?>"
                            data-customer-name="<?= htmlspecialchars($row['customer_name']) ?>"
                        >
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= date('Y年m月d日', strtotime($row['order_date'])) ?></td>
                            <td class="<?= $row['order_state'] ? 'status-complete' : 'status-pending' ?>">
                                <?= $row['order_state'] ? '納品済' : '未納品' ?>
                            </td>
                            <td><a class="edit-link" href="orderUpdate.html">編集</a></td>
                            <td><a class="delete-link" href="orderDelete.php">削除</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">注文データが存在しません。</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="btn-container">
        <button class="btn" onclick="location.href='home.html'">戻る</button>
        <button class="btn" onclick="location.href='orderInsert.php'">新規注文書作成</button>
    </div>

    <script>
        window.addEventListener("DOMContentLoaded", function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get("created") === "1") {
                alert("注文書が作成されました");
            }

            const searchBox = document.getElementById("searchBox");
            const rows = document.querySelectorAll("tbody tr[data-order-id]");

            searchBox.addEventListener("input", function () {
                const keyword = searchBox.value.toLowerCase();

                rows.forEach(row => {
                    const orderId = row.dataset.orderId.toLowerCase();
                    const customerId = row.dataset.customerId.toLowerCase();
                    const customerName = row.dataset.customerName.toLowerCase();

                    if (
                        orderId.includes(keyword) ||
                        customerId.includes(keyword) ||
                        customerName.includes(keyword)
                    ) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>
</body>
</html>
