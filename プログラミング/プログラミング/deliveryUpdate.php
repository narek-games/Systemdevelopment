<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>納品書編集画面</title>
  <style>
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
  </style>
</head>
 
<body>
 
 
  <div class="header">
    <div class="header-title">納品書編集画面</div>
    <div class="nav-links">
      <a href="./home.html">HOME</a>
 
    </div>
  </div>
 
 
 
  <div class="grid">
    <div class="form-group">
      <label>納品ID</label>
      <input type="text" class="readonly" value="DE000001" readonly>
    </div>
    <div class="form-group">
      <label>日付</label>
      <input type="date" id="deliveryDate">
    </div>
 
    <script>
      window.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('deliveryDate').value = `${yyyy}-${mm}-${dd}`;
      });
    </script>
 
    <div class="form-group">
      <label>顧客ID</label>
      <input type="text" class="readonly" value="CU000001" readonly>
    </div>
    <div class="form-group">
      <label>顧客名</label>
      <input type="text" value="大阪情報専門学校">
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
    <button onclick="location.href='orderOption.html'">商品を追加</button>
  </div>
 
  <div class="button-group">
    <a href="./deliveryHome.html"><button class="back-button">戻る</button></a>
    <button class="save-button">保存</button>
  </div>

  <script>
function updateUnDelivered(input) {
  const tr = input.closest('tr');
  const undelivered = tr.querySelector('.undelivered');
  if (!undelivered.dataset.original) {
    undelivered.dataset.original = undelivered.value;
  }
  let qty = parseInt(input.value) || 0;
  if (qty < 0) qty = 0;
  const original = parseInt(undelivered.dataset.original) || 0;
  let result = original - qty;
  if (result < 0) result = 0;
  undelivered.value = result;
  // max属性の更新
  input.max = original;
  if (qty > original) {
    input.value = original;
    undelivered.value = 0;
  }
}
window.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('tr').forEach(tr => {
    const qty = tr.querySelector('.qty');
    const undelivered = tr.querySelector('.undelivered');
    if (qty && undelivered) {
      if (!undelivered.dataset.original) {
        undelivered.dataset.original = undelivered.value;
      }
      let qtyVal = parseInt(qty.value) || 0;
      if (qtyVal < 0) qtyVal = 0;
      const original = parseInt(undelivered.dataset.original) || 0;
      let result = original - qtyVal;
      if (result < 0) result = 0;
      undelivered.value = result;
      // max属性の初期設定
      qty.max = original;
      if (qtyVal > original) {
        qty.value = original;
        undelivered.value = 0;
      }
    }
  });
});

// 保存ボタンでテーブル内容をlocalStorageに保存
const saveBtn = document.querySelector('.save-button');
if(saveBtn){
  saveBtn.addEventListener('click', function(){
    const rows = Array.from(document.querySelectorAll('tbody tr'));
    const data = rows.map(tr => {
      return {
        product: tr.querySelector('td:nth-child(1) a')?.textContent || '',
        qty: tr.querySelector('.qty')?.value || '',
        undelivered: tr.querySelector('.undelivered')?.value || '',
        price: tr.querySelector('td:nth-child(4)')?.textContent || ''
      };
    });
    localStorage.setItem('deliveryUpdateData', JSON.stringify(data));
    window.location.href = 'deliveryHome.html';
  });
}
// ページ読み込み時にlocalStorageから復元
window.addEventListener('DOMContentLoaded', function() {
  const saved = localStorage.getItem('deliveryUpdateData');
  if(saved){
    const data = JSON.parse(saved);
    const rows = Array.from(document.querySelectorAll('tbody tr'));
    data.forEach((item, i) => {
      if(rows[i]){
        rows[i].querySelector('td:nth-child(1) a').textContent = item.product;
        rows[i].querySelector('.qty').value = item.qty;
        rows[i].querySelector('.undelivered').value = item.undelivered;
        rows[i].querySelector('td:nth-child(4)').textContent = item.price;
      }
    });
  }
});
  </script>
 
</body>
</html>
