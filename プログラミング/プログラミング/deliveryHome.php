<?php
// データベース接続用ファイルを読み込む
require_once 'dbConnect.php';

// データベースから納品データを取得する処理
try {
  // SQL文を作成（納品テーブルと顧客テーブルを結合し、納品日が新しい順に並べる）
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
  // SQLを実行
  $stmt = $pdo->query($sql);
  // 結果を連想配列で取得
  $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // エラーが発生した場合はメッセージを表示して終了
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
    <div style="max-height: 500px; overflow-y: auto;">
      <table style="min-width: 900px;">
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
          <?php // データが1件以上ある場合の表示
          if (count($deliveries) > 0):
            // 取得した納品データを1件ずつ表示するループ
            foreach ($deliveries as $delivery): ?>
              <tr>
                <!-- htmlspecialcharsでXSS対策しつつ各項目を表示 -->
                <td><?= htmlspecialchars($delivery['delivery_id']) ?></td>
                <td><?= htmlspecialchars($delivery['customer_id']) ?></td>
                <td><?= htmlspecialchars($delivery['formatted_date']) ?></td>
                <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                <!-- 編集・削除・印刷ボタン（各納品データごとに表示） -->
                <td><a href="deliveryUpdate.php" class="edit-btn">編集</a></td>
                <td><a href="deliveryDelete.php" class="delete-btn">削除</a></td>
                <td><a href="deliveryPrint.php" class="print-btn">印刷</a></td>
              </tr>
            <?php endforeach;
          else: // データが1件もない場合の表示 
            ?>
            <tr>
              <td colspan="7">納品データが存在しません。</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="btn-container">
      <a href="home.html"><button class="reset-btn">戻る</button></a>
      <a href="deliveryInsert.php"><button class="submit-btn">新規納品書作成</button></a>
    </div>
  </main>

</body>

</html>