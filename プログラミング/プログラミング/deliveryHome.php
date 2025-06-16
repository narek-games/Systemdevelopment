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
      font-family: "Hiragino Kaku Gothic ProN", sans-serif;
      background: #f4f4f4;
      background-color: white;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    header {
      position: sticky;
      top: 0;
      color: white;
      background-color: forestgreen;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      border-radius: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
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
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-bottom: 40px;
    }

    .reset-btn,
    .submit-btn {
      width: 150px;
      padding: 12px;
      font-size: 16px;
      background-color: cornflowerblue;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
      color: black;
    }

    .reset-btn {
      background-color: whitesmoke;
    }

    .reset-btn:hover {
      background: gray;
    }

    .submit-btn:hover {
      background: blue;
      color: white;
    }

    .edit-btn,
    .delete-btn,
    .print-btn {
      color: white;
      padding: 4px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
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

  <header>
    <h1>納品書管理画面</h1>
  </header>

  <main>
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
              <td><a href="deliveryUpdate.php" class="edit-btn">編集</a></td>
              <td><a href="deliveryDelete.php" class="delete-btn">削除</a></td>
              <td><a href="deliveryPrint.php" class="print-btn">印刷</a></td>
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
      <a href="home.html"><button class="reset-btn">戻る</button></a>
      <a href="deliveryInsert.php"><button class="submit-btn">新規納品書作成</button></a>
    </div>
  </main>

</body>

</html>