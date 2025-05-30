<?php
require_once 'プログラミング/プログラミング/dbConnect.php';

$sql = "
    SELECT 
        o.order_id,
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
        th, td {
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
        .edit-link, .delete-link {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }
        .edit-link.disabled, .delete-link.disabled {
            color: #ccc;
            pointer-events: none;
        }
        .btn-container {
            margin-top: 30px;
        }
        .btn {
            font-size: 18px;
            padding: 10px 30px;
            margin: 0 10px;
            background-color: #d6e4ff;
            border: 1px solid #aaa;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #aacbff;
        }
    </style>
</head>
<body>
    <h2>注文書管理画面</h2>

    <div class="search-container">
        <input type="text" placeholder="顧客名または注文ID">
        <button style="font-size: 18px;">🔍</button>
    </div>

    <table>
        <tr>
            <th>注文ID</th>
            <th>顧客名</th>
            <th>作成日</th>
            <th>状態</th>
            <th></th>
            <th></th>
        </tr>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['order_id']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= date('Y年m月d日', strtotime($row['order_date'])) ?></td>
            <td class="<?= $row['order_state'] ? 'status-complete' : 'status-pending' ?>">
                <?= $row['order_state'] ? '納品済' : '未納品' ?>
            </td>
            <td>
                <a class="edit-link <?= $row['order_state'] ? 'disabled' : '' ?>" href="orderUpdate.html">編集</a>
            </td>
            <td>
                <a class="delete-link <?= $row['order_state'] ? 'disabled' : '' ?>" href="orderDelete.php">削除</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <!-- 空行を数行分追加 -->
        <?php for ($i = 0; $i < 5; $i++): ?>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <?php endfor; ?>
    </table>

    <div class="btn-container">
        <button class="btn" onclick="location.href='home.html'">戻る</button>
        <button class="btn" onclick="location.href='orderInsert.html'">新規注文書作成</button>
    </div>
</body>
</html>