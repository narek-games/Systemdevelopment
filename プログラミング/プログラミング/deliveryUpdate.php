<?php
// =============================
// データベース接続
// =============================
require_once 'dbConnect.php';
require_once 'dbConnectFunction.php';
// =============================
// 納品書編集：保存処理
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id'], $_POST['delivery_date'])) {
  // DB更新処理を関数でまとめて実行
  updateDeliveryDetails($pdo, $_POST);
  // 保存後は一覧画面へリダイレクト
  header("Location: deliveryHome.php");
  exit;
}
// =============================
// パラメータ取得と日付整形
// =============================
$delivery_id = $_GET['delivery_id'] ?? '';
$delivery_date_raw = $_GET['delivery_date'] ?? '';
$customer_id = $_GET['customer_id'] ?? '';
$customer_name = $_GET['customer_name'] ?? '';
$delivery_date_for_input = $delivery_date_raw ? substr($delivery_date_raw, 0, 10) : '';
// =============================
// 明細データ取得
// =============================
$items = [];
if (!empty($delivery_id)) {
  $sql = "SELECT DISTINCT od.product_name, od.undelivered_quantity, od.product_price, od.product_quantity, od.order_product_number FROM delivery_detail AS dd INNER JOIN order_detail AS od ON dd.order_product_number = od.order_product_number WHERE dd.delivery_id = :delivery_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':delivery_id' => $delivery_id]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>納品書編集画面</title>
  <style>
    body {
      font-family: "Hiragino Kaku Gothic ProN", sans-serif;
      background: #f4f4f4;
      background-color: white;
      margin: 0;
      padding: 0;
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
      margin-bottom: 20px;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    h1 {
      font-size: 20px;
      margin: 0;
    }

    main {
      max-width: 1200px;
      margin: 0 auto;
    }

    .date,
    .readonly-field {
      margin-bottom: 10px;
    }

    label,
    .readonly-label {
      font-weight: bold;
    }

    .readonly-value {
      display: inline-block;
      padding: 4px 8px;
      background-color: #f0f0f0;
      border: 1px solid #ccc;
      width: 300px;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px 40px;
      margin: 0 20px 20px 20px;
    }

    input[type="text"],
    input[type="date"],
    input[type="number"] {
      width: 100%;
      padding: 6px;
      box-sizing: border-box;
      font-family: "Hiragino Kaku Gothic ProN", sans-serif;
    }

    table {
      border-collapse: collapse;
      margin: 0 20px 20px 20px;
      width: calc(100% - 40px);
    }

    table,
    th,
    td {
      border: 1px solid black;
    }

    th,
    td {
      padding: 8px;
      text-align: center;
      border: 1px solid #999;
    }

    .button-group {
      margin: 20px;
    }

    button {
      font-size: 16px;
      padding: 10px 20px;
      margin-right: 10px;
    }

    .back-button {
      background-color: whitesmoke;
      width: 150px;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
    }

    .delete-button {
      background-color: #ff9999;
      width: 150px;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
    }

    .save-button {
      background-color: cornflowerblue;
      width: 150px;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
    }

    .add-product {
      margin: 0 20px 10px 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .save-button:hover {
      background-color: blue;
    }

    .back-button:hover {
      background-color: gray;
    }

    input[readonly],
    .undelivered[readonly] {
      background-color: #e0e0e0 !important;
    }
  </style>
</head>

<body>
  <header>
    <h1>納品書編集画面</h1>
  </header>
  <main>
    <form method="post">
      <div class="grid">
        <div class="form-group">
          <label>納品ID</label>
          <input type="text" class="readonly" value="<?= htmlspecialchars($delivery_id) ?>" readonly>
          <input type="hidden" name="delivery_id" value="<?= htmlspecialchars($delivery_id) ?>">
        </div>
        <div class="form-group">
          <label>日付</label>
          <input type="date" id="deliveryDate" name="delivery_date" value="<?= htmlspecialchars($delivery_date_for_input) ?>">
        </div>
        <div class="form-group">
          <label>顧客ID</label>
          <input type="text" class="readonly" value="<?= htmlspecialchars($customer_id) ?>" readonly>
        </div>
        <div class="form-group">
          <label>顧客名</label>
          <input type="text" value="<?= htmlspecialchars($customer_name) ?>" readonly>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>品名</th>
            <th>数量</th>
            <th>未納品数量</th>
            <th>単価</th>
          </tr>
        </thead>
        <tbody>
          <!-- ここからPHPのプログラムで、データベースから取得した商品の数だけ表の行を繰り返し作ります -->
          <?php if (!empty($items)): // もし商品データが1件以上見つかったら 
          ?>
            <?php foreach ($items as $item): // 商品データを1件ずつ取り出して、行（<tr>）を作ります 
            ?>
              <tr>
                <!-- 品名：データベースから取得した品名を表示します -->
                <td>
                  <a href="#"><?= htmlspecialchars($item['product_name']) ?></a>
                  <input type="hidden" name="order_product_number[]" value="<?= htmlspecialchars($item['order_product_number']) ?>">
                  <input type="hidden" name="product_name[]" value="<?= htmlspecialchars($item['product_name']) ?>">
                </td>
                <!-- 数量：初期値はDB値（product_quantity） -->
                <td>
                  <input type="number" name="product_quantity[]" value="0" class="qty" oninput="updateUnDelivered(this)" min="0" max="<?= htmlspecialchars($item['undelivered_quantity']) ?>">
                  <input type="hidden" name="original_product_quantity[]" value="<?= htmlspecialchars($item['product_quantity']) ?>">
                  <input type="hidden" name="original_undelivered_quantity[]" value="<?= htmlspecialchars($item['undelivered_quantity']) ?>">
                </td>
                <!-- 未納品数量：DB値。name属性は付与するが、サーバー側では使わず、DB値から計算 -->
                <td><input type="number" name="undelivered_quantity[]" value="<?= htmlspecialchars($item['undelivered_quantity']) ?>" class="undelivered" readonly min="0"></td>
                <!-- 単価：データベースから取得した単価を、読みやすいように3桁区切りのカンマ付きで表示します -->
                <td><?= htmlspecialchars(number_format($item['product_price'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: // もし商品データが1件も見つからなかったら 
          ?>
            <tr>
              <!-- 「明細がありません」というメッセージを4つの列を結合して表示します -->
              <td colspan="4">表示できる明細がありません。</td>
            </tr>
          <?php endif; ?>
          <!-- PHPの繰り返し処理はここまでです -->
        </tbody>
      </table>

      <div class="add-product">
        <button onclick="location.href='orderOption.php'">商品を追加</button><!--クリックすると何故かdeliveryHome.phpに飛ばされる(7/11現在)-->
      </div>

      <!-- 画面下部の操作ボタン -->
      <div class="button-group">
        <a href="./deliveryHome.php"><button type="button" class="back-button">戻る</button></a>
        <button type="submit" class="save-button">保存</button>
      </div>
    </form>
  </main>

  <script>
    /**
     * 「数量」が変更されたときに、「未納品数量」を自動で計算し直すためのプログラムです。
     * @param {object} input - 変更があった「数量」の入力欄そのもの
     */
    function updateUnDelivered(input) {
      const tr = input.closest('tr');
      const undelivered = tr.querySelector('.undelivered');
      // hiddenのoriginal_product_quantityは絶対に触らない
      if (!undelivered.dataset.original) undelivered.dataset.original = undelivered.value;
      let qty = parseInt(input.value) || 0;
      const original = parseInt(undelivered.dataset.original) || 0;
      if (qty > original) {
        qty = original;
        input.value = original;
      }
      if (qty < 0) {
        qty = 0;
        input.value = 0;
      }
      undelivered.value = original - qty;
      input.max = original;
      // hiddenのoriginal_product_quantityは絶対に書き換えない
    }
  </script>
</body>

</html>