<?php
require_once 'dbConnect.php';

$orderId = $_GET['order_id'] ?? 'OR000001'; // デフォルトでOR000001を表示（開発用）

try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.customer_name 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.customer_id 
        WHERE o.order_id = :order_id
    ");
    $stmt->execute(['order_id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT * FROM order_items WHERE order_id = :order_id
    ");
    $stmt->execute(['order_id' => $orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "データ取得失敗: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>MBSアプリ</title>
<style>
  body{
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    margin: 20px;
  }
  table, th, td {
    border: 1px solid #333;
    border-collapse: collapse;
  }
  th, td {
    padding: 5px 10px;
    text-align: center;
  }
  .btn {
    width: 170px;
    height: 40px;
    font-size: 18px;
    margin: 10px;
    background-color: #dbe7f9;
    color: black;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  .btn:hover{
    background-color: #4d7bc1;
  }
  .btn-delete{
    position: absolute;
    bottom: -40px;
    left: 0;
    width: 70px;
    height: 30px;
    font-size: 14px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  .btn-addRow{
    position: absolute;
    right: 0;
    bottom: -40px;
    width: 30px;
    height: 30px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 5px;
    border: none;
    cursor: pointer;
  }
  .status-red {
    color: red;
  }
  .status-green {
    color: green;
  }
</style>
</head>

<body>
<h2>注文書編集画面</h2>
<!--DBの連携 (最後にやる)-->
<p>
  注文ID: <input type="text" value="<?= htmlspecialchars($order['order_id']) ?>" readonly>
  作成日: <input type="text" value="<?= date('Y年m月d日H時i分s秒', strtotime($order['created_at'])) ?>" readonly>
  状態:
  <input type="radio" name="status" <?= $order['order_state'] === '未納品' ? 'checked' : '' ?>>未納品
  <input type="radio" name="status" <?= $order['order_state'] === '納品済' ? 'checked' : '' ?>>納品済
</p>
<p>
  顧客ID: <input type="text" value="<?= htmlspecialchars($order['customer_id']) ?>" readonly>
  顧客名: <input type="text" value="<?= htmlspecialchars($order['customer_name']) ?>" readonly>
  納品日: <input type="text" value="<?= date('Y年m月d日H時i分s秒', strtotime($order['delivery_date'])) ?>" readonly>
</p>
<!--ここまで-->

<div style="width: fit-content; margin: 30px auto; position: relative; text-align: center;">
  <table>
    <thead>
      <tr>
        <th></th>
        <th>品名</th>
        <th>注文数量</th>
        <th>未納数量</th>
        <th>単価</th>
        <th>摘要</th>
        <th>状態</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): 
      $status = ((int)$item['undelivered_quantity'] === 0) ? '納品済' : '未納品';
      $statusClass = $status === '納品済' ? 'status-green' : 'status-red';
      ?>
      <tr>
        <td><input type="checkbox"></td>
        <td><input type="text" value="<?= htmlspecialchars($item['item_name']) ?>"></td>
        <td><input type="number" min="0" value="<?= $item['order_quantity'] ?>"></td>
        <td><input type="number" min="0" value="<?= $item['undelivered_quantity'] ?>"></td>
        <td><input type="number" min="0" value="<?= $item['unit_price'] ?>"></td>
        <td><input type="text" value="<?= htmlspecialchars($item['description']) ?>"></td>
        <td class="<?= $statusClass ?>"><?= $status ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <button class="btn-delete" onclick="deleteCheckedRows()">削除</button>
  <button class="btn-addRow" onclick="addRow()">+</button>
</div>
<p>
  <button class="btn" onclick="location.href='orderHome.html'">戻る</button>
  <button class="btn" onclick="saveTableState()">保存</button>
</p>
<script>
  //入力が空かどうかをチェック　商品欄等が未入力の場合納品状態の欄が入力されないようにする
  function updateStatusOnInput(event) {
    const row = event.target.closest("tr");
    const name = row.querySelectorAll("td")[1].querySelector("input").value.trim();
    const orderQty = row.querySelectorAll("td")[2].querySelector("input").value.trim();
    const remainQtyInput = row.querySelectorAll("td")[3].querySelector("input");
    const remainQty = parseInt(remainQtyInput.value);
    const statusCell = row.querySelectorAll("td")[6];
    //情報がすべて空 ➡ 納品状態も空欄
    if (!name && !orderQty && !remainQtyInput.value) {
      statusCell.textContent = "";
      statusCell.className = "";
      return;
    }
    //0の場合 ➡ 納品済　
    //それ以外 ➡ 未納品　
    if (!isNaN(remainQty)) {
      if (remainQty === 0) {
        statusCell.textContent = "納品済";
        statusCell.className = "status-green";
      } else {
        statusCell.textContent = "未納品";
        statusCell.className = "status-red";
      }
    }
  }

  function addRow(data = {}) {
    const table = document.querySelector("table tbody");
    const newRow = document.createElement("tr");

    const isEmpty =
      !data.name && !data.orderQty && !data.remainQty;

    const statusText = isEmpty ? "" :
      (parseInt(data.remainQty) === 0 ? "納品済" : "未納品");

    const statusClass = statusText === "納品済" ? "status-green" :
      statusText === "未納品" ? "status-red" : "";

    newRow.innerHTML = `
      <td><input type="checkbox" ${data.checked ? "checked" : ""}></td>
      <td><input type="text" value="${data.name || ""}"></td>
      <td><input type="number" min="0" value="${data.orderQty || ""}"></td>
      <td><input type="number" min="0" value="${data.remainQty || ""}"></td>
      <td><input type="number" min="0" value="${data.price || ""}"></td>
      <td><input type="text" value="${data.note || ""}"></td>
      <td class="${statusClass}">${statusText}</td>
    `;

    const remainQtyInput = newRow.querySelectorAll("td")[3].querySelector("input");
    remainQtyInput.addEventListener("input", updateStatusOnInput);

    table.appendChild(newRow);
  }

  function deleteCheckedRows() {
    if (!confirm("選択した行を削除してもよろしいですか？")) return;

    const table = document.querySelector("table tbody");
    const rows = table.querySelectorAll("tr");
    rows.forEach(row => {
      const checkbox = row.querySelector("input[type='checkbox']");
      if (checkbox && checkbox.checked) {
        table.removeChild(row);
      }
    });
  }

  function saveTableState() {
    const table = document.querySelector("table tbody");
    const rows = table.querySelectorAll("tr");
    const tableData = [];

    rows.forEach(row => {
      const cells = row.querySelectorAll("td");
      const name = cells[1].querySelector("input").value.trim();
      const orderQty = cells[2].querySelector("input").value.trim();
      const remainQty = cells[3].querySelector("input").value.trim();

      // 空の行は保存しない
      if (!name && !orderQty && !remainQty) return;

      const status = parseInt(remainQty) === 0 ? "納品済" : "未納品";

      const rowData = {
        checked: cells[0].querySelector("input[type='checkbox']").checked,
        name: name,
        orderQty: orderQty,
        remainQty: remainQty,
        price: cells[4].querySelector("input").value,
        note: cells[5].querySelector("input").value,
        status: status
      };
      tableData.push(rowData);
    });

    localStorage.setItem("orderTableData", JSON.stringify(tableData));
    alert("保存しました！");
  }

  window.onload = function () {
    const rows = document.querySelectorAll("tbody tr");
    rows.forEach(row => {
      const remainQtyInput = row.querySelectorAll("td")[3].querySelector("input");
      remainQtyInput.addEventListener("input", updateStatusOnInput);
    });
  };
</script>

</body>
</html>
