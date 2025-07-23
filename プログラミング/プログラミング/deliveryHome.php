<?php
// =============================
// データベース接続・関数読み込み
// =============================
require_once 'dbConnect.php';
require_once 'dbConnectFunction.php'; // DB操作関数を利用

// =============================
// 削除処理
// =============================
// 削除ボタンが押された場合、該当レコードをDBから削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $delete_id = $_POST['delete_id'];
  // 共通関数で削除処理
  deleteDeliveryById($pdo, $delete_id);
}

// =============================
// 納品データの取得
// =============================
// 共通関数で納品データ一覧を取得
$deliveries = getAllDeliveries($pdo);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>納品書管理画面</title>
  <style>
    /* =============================
       画面全体のスタイル
    ============================= */
    body {
      font-family: "Hiragino Kaku Gothic ProN", sans-serif;
      background: #f4f4f4;
      background-color: white;
    }

    /* ヘッダー部分 */
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

    /* テーブル部分 */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      table-layout: fixed;
    }

    th,
    td {
      border: 1px solid #999;
      padding: 8px;
      text-align: center;
      word-break: break-all;
    }

    th {
      background-color: #e0e0e0;
      position: sticky;
      top: 0;
      z-index: 2;
    }

    /* ボタン・操作部分 */
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

  <!-- =============================
       ヘッダー
  ============================= -->
  <header>
    <h1>納品書管理画面</h1>
  </header>

  <main>
    <!-- =============================
         納品データ一覧テーブル
         列名は固定、データのみスクロール
    ============================= -->
    <div style="max-height: 500px; overflow-y: auto; width: 100%;">
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
                <!-- htmlspecialcharsでXSS対策しつつ各項目を表示（納品ID、顧客ID、日付、顧客名） -->
                <td><?= htmlspecialchars($delivery['delivery_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($delivery['customer_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($delivery['formatted_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($delivery['customer_name'] ?? '') ?></td>
                <!-- 編集ボタン：該当納品データの編集画面へ遷移（GETパラメータで情報を渡す） -->
                <td><a href="deliveryUpdate.php?delivery_id=<?= urlencode($delivery['delivery_id'] ?? '') ?>&amp;customer_id=<?= urlencode($delivery['customer_id'] ?? '') ?>&amp;delivery_date=<?= urlencode($delivery['delivery_date'] ?? '') ?>&amp;customer_name=<?= urlencode($delivery['customer_name'] ?? '') ?>" class="edit-btn">編集</a></td>
                <!-- 削除ボタン：押下時に確認ダイアログを表示し、OKなら該当データをDBから削除 -->
                <td>
                  <form method="post" action="" style="display:inline;" onsubmit="return confirm('本当に削除しますか？');">
                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($delivery['delivery_id'] ?? '') ?>">
                    <button type="submit" class="delete-btn">削除</button>
                  </form>
                </td>
                <!-- 印刷ボタン：納品書印刷画面へ遷移（GETパラメータで情報を渡す） -->
                <td><a href="deliveryPrint.php?delivery_id=<?= urlencode($delivery['delivery_id'] ?? '') ?>&amp;customer_name=<?= urlencode($delivery['customer_name'] ?? '') ?>&amp;delivery_date=<?= urlencode($delivery['formatted_date'] ?? '') ?>" class="print-btn">印刷</a></td>
              </tr>
            <?php endforeach;
          else: // データが1件もない場合の表示 
            ?>
            <tr>
              <!-- データが存在しない場合は1行でメッセージ表示 -->
              <td colspan="7">納品データが存在しません。</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- =============================
         下部の操作ボタン
    ============================= -->
    <div class="btn-container">
      <a href="home.html"><button class="reset-btn">戻る</button></a>
      <a href="orderOption.php"><button class="submit-btn">新規納品書作成</button></a>
    </div>
  </main>

</body>

</html>
