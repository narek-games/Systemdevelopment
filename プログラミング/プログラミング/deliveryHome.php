<?php
require_once 'dbConnect.php';

try {
  $sql = "
        SELECT 
            d.delivery_id,
            d.customer_id,
            DATE_FORMAT(d.delivery_date, '%Y年%m月%d日') AS formatted_date,
            c.customer_name
        FROM delivery d
        INNER JOIN customer c ON d.customer_id = c.customer_id
        ORDER BY d.delivery_date DESC
    ";
  $stmt = $pdo->query($sql);
  $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "データ取得失敗: " . $e->getMessage();
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>納品書管理画面</title>
  <style>
    body {
      font-family: sans-serif;
    }

    h2 {
      text-align: center;
      margin-top: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th,
    td {
      border: 1px solid #999;
      padding: 8px;
      text-align: center;
    }

    th {
      background-color: #e0e0e0;
    }

    .btn-container {
      text-align: center;
      margin-bottom: 40px;
      display: flex;
      justify-content: center;
      gap: 40px;
    }

    /* home.htmlのボタンデザインを反映 */
    .main-btn {
      display: inline-block;
      width: 250px;
      margin: 0;
      padding: 15px;
      font-size: 20px;
      background-color: #c7dbf3;
      border: 1px solid #888;
      border-radius: 8px;
      box-shadow: 2px 2px 3px #888;
      color: black;
      cursor: pointer;
    }

    .main-btn:hover {
      background-color: #a5c6ed;
    }

    .edit-btn,
    .delete-btn,
    .print-btn {
      color: white;
      padding: 4px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .edit-btn {
      background-color: #3b82f6;
    }

    .delete-btn {
      background-color: #ef4444;
    }

    .print-btn {
      background-color: #f59e0b;
    }
  </style>
</head>

<body>

  <h2>納品書管理画面</h2>

  <table>
    <thead>
      <tr>
        <th>納品ID</th>
        <th>顧客ID</th>
        <th>更新日</th>
        <th>顧客名</th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($deliveries) > 0): ?>
        <?php foreach ($deliveries as $delivery): ?>
          <tr>
            <td><?= htmlspecialchars($delivery['delivery_id']) ?></td>
            <td><?= htmlspecialchars($delivery['customer_id']) ?></td>
            <td><?= htmlspecialchars($delivery['formatted_date']) ?></td>
            <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
            <td><button class="edit-btn">編集</button></td>
            <td><button class="delete-btn">削除</button></td>
            <td><button class="print-btn">印刷</button></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7">納品データが存在しません。</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="btn-container">
    <button class="main-btn" onclick="location.href='home.html'">戻る</button>
    <button class="main-btn" onclick="location.href='deliveryInsert.php'">新規納品書作成</button>
  </div>

</body>

</html>