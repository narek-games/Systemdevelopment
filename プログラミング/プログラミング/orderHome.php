<?php
require_once 'dbConnect.php';

$sql = "
    SELECT 
        o.order_id,
        o.customer_id,
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

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #888;
            padding: 8px;
        }

        th {
            background-color: #f0f0f0;
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

        .edit-link.disabled {
            color: #ccc;
            pointer-events: none;
        }

        .delete-link.disabled {
            color: #f99;
            pointer-events: none;
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
            margin: 0;
        }

        .btn:hover {
            background-color: #a5c6ed;
        }

        .search-button {
            font-size: 20px;
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #45a049;
        }

        .table-wrapper {
            width: 90%;
            margin: auto;
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
        }
    </style>
</head>

<body>
    <h2>注文書管理画面</h2>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="顧客名または注文ID">
        <button class="search-button">🔍 検索</button>
    </div>
    <div class="table-wrapper">
        <table id="orderTable">
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
                        <tr>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= date('Y年m月d日', strtotime($row['order_date'])) ?></td>
                            <td class="<?= $row['order_state'] ? 'status-complete' : 'status-pending' ?>">
                                <?= $row['order_state'] ? '納品済' : '未納品' ?>
                            </td>
                            <td>
                                <a class="edit-link" href="orderUpdate.php?order_id=<?= urlencode($row['order_id']) ?>">編集</a>
                            </td>
                            <td>
                                <a class="delete-link" href="orderDelete.php?order_id=<?= urlencode($row['order_id']) ?>">削除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">注文データが存在しません。</td>
                    </tr>
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

            const input = document.getElementById("searchInput");
            input.addEventListener("input", function () {
                const keyword = input.value.trim().toLowerCase();
                const rows = document.querySelectorAll("#orderTable tbody tr");

                rows.forEach(row => {
                    const cells = row.querySelectorAll("td");
                    const orderId = cells[0]?.textContent?.toLowerCase() || "";
                    const customerName = cells[2]?.textContent?.toLowerCase() || "";

                    const match = orderId.includes(keyword) || customerName.includes(keyword);
                    row.style.display = match ? "" : "none";
                });
            });
        });
    </script>
</body>

</html>
