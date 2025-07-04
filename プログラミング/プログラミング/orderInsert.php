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
  <a href="./orderHome.html"><button class="back-button">戻る</button></a>
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

function submitForm() {
  const rows = document.querySelectorAll("tbody tr");
  const items = [];
  rows.forEach(row => {
    const cells = row.querySelectorAll("td");
    if (cells.length < 4) return;
    const name = cells[0].innerText.trim();
    const quantity = cells[1].innerText.trim();
    const price = cells[2].innerText.trim();
    const remark = cells[3].innerText.trim();
    if (name || quantity || price || remark) {
      items.push({ name, quantity, price, remark });
    }
  });

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
}
</script>

</body>
</html>
