<?php
// =================================================================
// ▼▼▼ PHPの処理（ここから） ▼▼▼
// この部分は、主に画面が表示される前の準備をしています。
// =================================================================

// 前の画面（deliveryHome.php）から送られてきた情報を、変数という名前の箱に入れています。
// ?? '' は、もし情報が何も送られてこなかった場合にエラーが出ないようにするためのお守りのようなものです。
$delivery_id = $_GET['delivery_id'] ?? '';         // 納品ID
$delivery_date_raw = $_GET['delivery_date'] ?? ''; // 日付（"2024-01-01" のような形で受け取ります）
$customer_id = $_GET['customer_id'] ?? '';         // 顧客ID
$customer_name = $_GET['customer_name'] ?? '';     // 顧客名

// 受け取った日付データを、日付入力欄で正しく表示できるように準備します。
// deliveryHome.phpから "YYYY-MM-DD" 形式で渡ってくるので、基本的にはそのまま使います。
$delivery_date_for_input = '';
if ($delivery_date_raw) {
    // もし "YYYY-MM-DD HH:MM:SS" のように時刻が含まれていた場合でも、
    // substr() を使って先頭10文字だけを切り出すことで、日付入力欄に対応させます。
    $delivery_date_for_input = substr($delivery_date_raw, 0, 10);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>納品書編集画面</title>
  <style>
    /* ================================================================= */
    /* ▼▼▼ CSS（画面の見た目を整える設定）▼▼▼ */
    /* ここでは、文字の大きさ、色、配置などを決めています。 */
    /* ================================================================= */
    body {
      font-family: "Hiragino Kaku Gothic ProN", sans-serif;
      margin: 0;
    }
 
    /* ▼▼▼ ヘッダースタイル ▼▼▼ */
    .header {
      background-color: green;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      border-radius: 0 0 20px 20px;
    }
 
    .header-title {
      font-size: 20px;
      font-weight: bold;
    }
 
    .nav-links a {
      color: white;
      text-decoration: none;
      margin-left: 30px;
      font-size: 16px;
    }
 
    .nav-links a:hover {
      text-decoration: underline;
    }
    /* ▲▲▲ ヘッダーここまで ▲▲▲ */
 
    h1 {
      font-size: 20px;
      margin: 20px;
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
 
    table, th, td {
      border: 1px solid black;
    }
 
    th, td {
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

    input[readonly], .undelivered[readonly] {
      background-color: #e0e0e0 !important;
    }
  </style>
</head>
 
<body>
 
  <!-- ================================================================= -->
  <!-- ▼▼▼ HTML（画面の骨組み）▼▼▼ -->
  <!-- ここでは、画面に表示される文字や入力欄などを配置しています。 -->
  <!-- ================================================================= -->

  <div class="header">
    <div class="header-title">納品書編集画面</div>
    <div class="nav-links">
      <a href="./home.html">HOME</a>
      <!-- 他のナビゲーションリンクが必要な場合はここに追加します -->
 
    </div>
  </div>
 
 
 
  <div class="grid">
    <div class="form-group">
      <label>納品ID</label> <!-- 「納品ID」という見出し -->
      <!-- 納品IDを表示する入力欄。readonlyなので、ユーザーは編集できません。 -->
      <input type="text" class="readonly" value="<?= htmlspecialchars($delivery_id) ?>" readonly>
    </div>
    <div class="form-group">
      <label>日付</label> <!-- 「日付」という見出し -->
      <!-- 日付を入力するための特別な入力欄。PHPで準備した日付が最初から入っています。 -->
      <input type="date" id="deliveryDate" value="<?= htmlspecialchars($delivery_date_for_input) ?>">
    </div>
 
    <div class="form-group">
      <label>顧客ID</label> <!-- 「顧客ID」という見出し -->
      <!-- 顧客IDを表示する入力欄。readonlyなので、ユーザーは編集できません。 -->
      <input type="text" class="readonly" value="<?= htmlspecialchars($customer_id) ?>" readonly>
    </div>
    <div class="form-group">
      <label>顧客名</label> <!-- 「顧客名」という見出し -->
      <!-- 顧客名を表示する入力欄。readonlyなので、ユーザーは編集できません。 -->
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
      <!-- この部分は現在、固定のサンプルデータが表示されています。将来的にはデータベースから取得した明細を表示します。 -->
      <tr>
        <td><a href="#">週刊BCN 10/17</a></td>
        <td><input type="number" value="1" class="qty" oninput="updateUnDelivered(this)" min="0"></td>
        <td><input type="number" value="1" class="undelivered" readonly min="0"></td>
        <td>363</td>
      </tr>
      <tr>
        <td><a href="#">日経コンピュータ 11月号</a></td>
        <td><input type="number" value="1" class="qty" oninput="updateUnDelivered(this)" min="0"></td>
        <td><input type="number" value="1" class="undelivered" readonly min="0"></td>
        <td>1,300</td>
      </tr>
      <tr>
        <td><a href="#">SoftwareDesign 11月号</a></td>
        <td><input type="number" value="1" class="qty" oninput="updateUnDelivered(this)" min="0"></td>
        <td><input type="number" value="1" class="undelivered" readonly min="0"></td>
        <td>1,342</td>
      </tr>
      <tr>
        <td><a href="#">医療情報 第7版 医学・医療編</a></td>
        <td><input type="number" value="11" class="qty" oninput="updateUnDelivered(this)" min="0"></td>
        <td><input type="number" value="11" class="undelivered" readonly min="0"></td>
        <td>3,740</td>
      </tr>
    </tbody>
  </table>
 
  <div class="add-product">
    <button onclick="location.href='orderOption.php'">商品を追加</button>
  </div>
 
  <!-- 画面下部の操作ボタン -->
  <div class="button-group">
    <a href="./deliveryHome.php"><button class="back-button">戻る</button></a>
    <button class="save-button">保存</button>
  </div>

  <script>
    /* ================================================================= */
    /* ▼▼▼ JavaScript（画面に動きをつけるプログラム）▼▼▼ */
    /* ================================================================= */

    /**
     * 「数量」が変更されたときに、「未納品数量」を自動で計算し直すためのプログラムです。
     * @param {object} input - 変更があった「数量」の入力欄そのもの
     */
    function updateUnDelivered(input) {
      // １．変更があった行（tr）全体を取得します。
      const tr = input.closest('tr');
      // ２．その行にある「未納品数量」の入力欄を取得します。
      const undelivered = tr.querySelector('.undelivered');

      // ３．もし、元の未納品数量をまだ覚えていなければ、こっそり覚えておきます。
      if (!undelivered.dataset.original) {
        undelivered.dataset.original = undelivered.value;
      }

      // ４．入力された「数量」と、元々の「未納品数量」を取得します。
      let qty = parseInt(input.value) || 0; // 入力された数量
      const original = parseInt(undelivered.dataset.original) || 0; // 元の未納品数量

      // ５．数量が元の数を超えないように、またマイナスにならないように調整します。
      if (qty > original) {
        qty = original;
        input.value = original; // 入力欄の数字も正しい値に直す
      }
      if (qty < 0) {
        qty = 0;
        input.value = 0;
      }

      // ６．「元の未納品数量」から「数量」を引いて、新しい未納品数量を計算します。
      let result = original - qty;

      // ７．計算結果を「未納品数量」の入力欄に表示します。
      undelivered.value = result;

      // ８．入力できる数量の上限（max属性）を元の未納品数量に設定します。
      input.max = original;
    }

    /**
     * ページが読み込まれた時に、自動的に実行されるプログラムです。
     * 主に、最初の表示を整えたり、イベント（クリックなど）の準備をしたりします。
     */
    window.addEventListener('DOMContentLoaded', function() {
      // ページにある全ての行に対して、最初の「未納品数量」を計算する準備をします。
      document.querySelectorAll('tbody tr').forEach(tr => {
        const qtyInput = tr.querySelector('.qty');
        if (qtyInput) {
          // 最初の表示のために、一度計算処理を呼び出します。
          updateUnDelivered(qtyInput);
        }
      });

      // 「保存」ボタンがクリックされたときの処理を準備します。
      const saveBtn = document.querySelector('.save-button');
      if (saveBtn) {
        saveBtn.addEventListener('click', function() {
          // 現在はまだデータベースに保存する機能がないため、
          // 入力内容をブラウザに一時的に記憶させて、一覧画面に戻る動きをします。
          alert('保存処理は現在準備中です。\n入力内容は一時的にブラウザに記憶されます。');

          // 表（tbody）の全ての行（tr）からデータを集めます。
          const rows = Array.from(document.querySelectorAll('tbody tr'));
          const dataToSave = rows.map(tr => {
            return {
              product: tr.querySelector('td:nth-child(1) a')?.textContent || '',
              qty: tr.querySelector('.qty')?.value || '',
              undelivered: tr.querySelector('.undelivered')?.value || '',
              price: tr.querySelector('td:nth-child(4)')?.textContent || ''
            };
          });

          // 集めたデータをブラウザの記憶領域（localStorage）に保存します。
          localStorage.setItem('deliveryUpdateData', JSON.stringify(dataToSave));

          // 納品一覧画面に戻ります。
          window.location.href = 'deliveryHome.php';
        });
      }

      // ページ読み込み時に、もしブラウザに一時保存したデータがあれば、それを復元します。
      const savedData = localStorage.getItem('deliveryUpdateData');
      if (savedData) {
        const data = JSON.parse(savedData);
        const rows = Array.from(document.querySelectorAll('tbody tr'));
        // 保存されていたデータを、表の各行にセットしていきます。
        data.forEach((item, index) => {
          if (rows[index]) {
            rows[index].querySelector('td:nth-child(1) a').textContent = item.product;
            rows[index].querySelector('.qty').value = item.qty;
            rows[index].querySelector('.undelivered').value = item.undelivered;
            rows[index].querySelector('td:nth-child(4)').textContent = item.price;
          }
        });
        // 一度使ったデータは消しておきます。
        localStorage.removeItem('deliveryUpdateData');
      }
    });
  </script>
 
</body>
</html>
