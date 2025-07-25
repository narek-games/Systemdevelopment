<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>注文書作成画面</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 10px;
    }
    th, td {
      text-align: center;
      border: 1px solid #999;
      padding: 8px;
    }
    input[type="text"], input[type="number"] {
      width: 100%;
      padding: 6px;
      box-sizing: border-box;
    }
    .button-container {
      margin-top: 20px;
    }
    button {
      width: 150px;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
    }
    .grid.grid-3col {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px 40px;
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .readonly-gray {
      background-color: #ccc;
      color: #333;
      cursor: not-allowed;
    }
    ul#suggestions {
      border: 1px solid #ccc;
      max-height: 150px;
      overflow-y: auto;
      list-style: none;
      padding: 0;
      margin: 0;
      width: 100%;
      position: absolute;
      background-color: white;
      z-index: 999;
    }
    ul#suggestions li {
      padding: 5px 10px;
      cursor: pointer;
    }
    ul#suggestions li:hover {
      background-color: #eee;
    }
  </style>
</head>
<body>

<h2>注文書作成画面</h2>
<div class="grid grid-3col">
  <div class="form-group">
    <label>顧客ID</label>
    <input type="text" id="customerId" name="customer_id" oninput="fetchCustomerInfoById()">
  </div>
  <div class="form-group" style="position:relative;">
    <label>顧客名</label>
    <input type="text" id="customerName" autocomplete="off" oninput="suggestCustomerName()">
    <ul id="suggestions"></ul>
  </div>
  <div class="form-group">
    <label>電話番号</label>
    <input type="text" id="phoneNumber" readonly class="readonly-gray">
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>品名</th><th>数量</th><th>単価</th><th>摘要</th>
    </tr>
  </thead>
  <tbody>
    <tr><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
  </tbody>
</table>

<div>
  <button class="add-product" onclick="addProductRow()">商品を追加</button>
</div>

<div class="button-container">
  <a href="./orderHome.php"><button class="back-button">戻る</button></a>
  <button onclick="submitForm()" class="Add-button">作成</button>
</div>

<script>
const suggestions = document.getElementById("suggestions");

function fetchCustomerInfoById() {
  const customerId = document.getElementById("customerId").value.trim();
  if (customerId === "") return;

  fetch("getCustomerInfo.php?customer_id=" + encodeURIComponent(customerId))
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById("customerName").value = data.name;
        document.getElementById("phoneNumber").value = data.phone;
      } else {
        document.getElementById("customerName").value = "";
        document.getElementById("phoneNumber").value = "";
      }
    });
}

function suggestCustomerName() {
  const keyword = document.getElementById("customerName").value.trim();
  if (keyword.length === 0) {
    suggestions.innerHTML = "";
    return;
  }

  fetch("getCustomerInfo.php?customer_name=" + encodeURIComponent(keyword))
    .then(response => response.json())
    .then(data => {
      suggestions.innerHTML = "";
      data.forEach(customer => {
        const li = document.createElement("li");
        li.textContent = `${customer.customer_name} (${customer.phone_number})`;
        li.onclick = () => {
          document.getElementById("customerName").value = customer.customer_name;
          document.getElementById("customerId").value = customer.customer_id;
          document.getElementById("phoneNumber").value = customer.phone_number;
          suggestions.innerHTML = "";
        };
        suggestions.appendChild(li);
      });
    });
}


// 数値欄に整数のみ入力可（整数以外を入力しようとしたらエラー表示）
function enforceNumericInput(td) {
  // 直接入力
  td.addEventListener('keydown', function(e) {
    // 数字、Backspace, Delete, Tab, 矢印、Enter, Home, End, Ctrl+A/C/V/Xは許可
    if (
      !(
        (e.key >= '0' && e.key <= '9') ||
        ["Backspace","Delete","Tab","ArrowLeft","ArrowRight","ArrowUp","ArrowDown","Enter","Home","End"].includes(e.key) ||
        (e.ctrlKey && ["a","c","v","x","A","C","V","X"].includes(e.key))
      )
    ) {
      alert('数量・単価欄には整数のみ入力できます');
      e.preventDefault();
    }
  });
  // beforeinput: IMEやドラッグ&ドロップ、貼り付け対応
  td.addEventListener('beforeinput', function(e) {
    if (e.inputType === 'insertFromPaste' || e.inputType === 'insertFromDrop' || e.inputType === 'insertText') {
      let data = e.data;
      if (typeof data !== 'string') {
        // 貼り付けやドラッグ時はdataがundefinedなので、clipboardData/dataTransferから取得
        data = (e.clipboardData && e.clipboardData.getData('text')) || (e.dataTransfer && e.dataTransfer.getData('text')) || '';
      }
      if (/\D/.test(data)) {
        alert('数量・単価欄には整数のみ入力できます');
        e.preventDefault();
      }
    }
  });
}

function applyNumericEnforcementToTable() {
  const rows = document.querySelectorAll('tbody tr');
  rows.forEach(row => {
    const tds = row.querySelectorAll('td');
    if (tds[1]) enforceNumericInput(tds[1]); // 数量
    if (tds[2]) enforceNumericInput(tds[2]); // 単価
  });
}

function submitForm() {
  const rows = document.querySelectorAll("tbody tr");
  const items = [];
  for (const row of rows) {
    const cells = row.querySelectorAll("td");
    if (cells.length < 4) continue;
    const name = cells[0].innerText.trim();
    const quantity = cells[1].innerText.trim();
    const price = cells[2].innerText.trim();
    const remark = cells[3].innerText.trim();
    // 商品名・数量・単価がすべて空ならスキップ
    if (!(name || quantity || price || remark)) continue;
    // 商品名・数量・単価は必須
    if (!name) {
      alert("商品名を入力してください。");
      return;
    }
    if (!quantity) {
      alert("数量を入力してください。");
      return;
    }
    if (!price) {
      alert("単価を入力してください。");
      return;
    }
    // 数量・単価が整数かチェック
    if (!/^\d+$/.test(quantity)) {
      alert("数量欄には整数のみ入力してください。");
      return;
    }
    if (!/^\d+$/.test(price)) {
      alert("単価欄には整数のみ入力してください。");
      return;
    }
    items.push({ name, quantity, price, remark });
  }

  const customerId = document.getElementById("customerId").value.trim();
  if (!customerId || items.length === 0) {
    alert("顧客IDと商品情報を入力してください。");
    return;
  }

  const payload = { customerId, items };

  fetch("orderInsertSubmit.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(response => response.text())
  .then(result => {
    alert("注文書が作成されました。");
    window.location.href = "orderHome.php";
  })
  .catch(error => {
    alert("送信エラー: " + error);
  });
}

function addProductRow() {
  const tbody = document.querySelector("table tbody");
  const newRow = document.createElement("tr");
  for (let i = 0; i < 4; i++) {
    const td = document.createElement("td");
    td.contentEditable = "true";
    newRow.appendChild(td);
  }
  tbody.appendChild(newRow);
  // 数量・単価欄に数値制限
  enforceNumericInput(newRow.children[1]);
  enforceNumericInput(newRow.children[2]);
}
</script>

</script>
<script>
// 初期行にも数値制限
window.onload = applyNumericEnforcementToTable;
</script>
</body>
</html>
