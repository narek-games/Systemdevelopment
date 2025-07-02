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

    th,
    td {

      text-align: center;
      border: 1px solid #999;
      padding: 8px;
    }

    input[type="text"],
    input[type="date"],
    input[type="number"] {
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

    


    .back-button:hover {
      background-color: gray;
      /* 濃い青（ホバー時） */
    }

    .add-row {
      font-size: 24px;
      cursor: pointer;
      margin-top: 10px;
    }
    .add-product:hover{
      background-color: whitesmoke;
    }
    .Add-button{
      background-color: cornflowerblue;
      
    }
    .add-product:hover{
      background-color: gray;
    }
    .Add-button:hover{
      background-color: blue;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px 40px;
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    /* ✅ グリッドを3列に */
.grid.grid-3col {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px 40px;
  margin-bottom: 20px;
}

/* ✅ ラベル整列用 */
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

/* ✅ 編集不可な灰色の入力欄 */
input.readonly-gray {
  background-color: #ccc;  /* 少し濃いグレーで視認性向上 */
  color: #333;
  cursor: not-allowed;
}

.readonly-gray {
    background-color: #ccc;
    color: #333;
  }
  </style>
</head>

<body>

  <h2>注文書作成画面</h2>
 <div class="grid grid-3col">
  <div class="form-group">
    <label>顧客ID</label>
    <input type="text" id="customerId" name="customer_id" oninput="fetchCustomerInfo()">
  </div>
  <div class="form-group">
    <label>顧客名</label>
    <input type="text" id="customerName">
  </div>
  <div class="form-group">
    <label>電話番号</label>
    <input type="text" id="phoneNumber" readonly class="readonly-gray">
  </div>
</div>



  <table>
    <thead>
      <tr>
        <th>品名</th>
        <th>数量</th>
        <th>単価</th>
        <th>摘要</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
      </tr>
      <tr>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
      </tr>
      <tr>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
      </tr>
      <tr>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
      </tr>
      <tr>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
        <td contenteditable="true"></td>
      </tr>
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
// 顧客情報マスタ（PHPでDBから動的生成可）
const customerData = <?php
    // データベースから連想配列を構築
    $host = '10.15.153.12';
    $dbname = 'mbs';
    $username = 'user';
    $password = '1212';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT customer_id, customer_name, phone_number FROM customer");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($rows as $row) {
      $data[$row['customer_id']] = [
        'name' => $row['customer_name'],
        'phone' => $row['phone_number']
      ];
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  ?>;

  function fetchCustomerInfo() {
    const customerId = document.getElementById("customerId").value.trim();

    if (customerId === "") {
      document.getElementById("customerName").value = "";
      document.getElementById("phoneNumber").value = "";
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "getCustomerInfo.php?customer_id=" + encodeURIComponent(customerId), true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.success) {
            document.getElementById("customerName").value = data.name;
            document.getElementById("phoneNumber").value = data.phone;
          } else {
            document.getElementById("customerName").value = "";
            document.getElementById("phoneNumber").value = "";
          }
        } catch (e) {
          console.error("JSON解析エラー:", e);
        }
      }
    };
    xhr.send();
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
      items.push({
        name,
        quantity,
        price,
        remark
      });
    }
  });

  const customerId = document.querySelectorAll('input[type="text"]')[0].value.trim();

  if (!customerId || items.length === 0) {
    alert("顧客IDと商品情報を入力してください。");
    return;
  }

  const payload = {
    customerId,
    items
  };

  fetch("orderInsertSubmit.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  })
  .then(response => response.text())
  .then(result => {
  // データベース挿入成功後に注文一覧へ遷移（URLパラメータ付き）
  window.location.href = "orderHome.php?created=1";
})
  .catch(error => {
    console.error("送信エラー:", error);
    alert("データ送信に失敗しました。");
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