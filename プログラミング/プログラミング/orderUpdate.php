<?php
$host = '10.15.153.12';
$dbname = 'mbs';
$username = 'user';
$password = '1212';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$orderId = $_GET['order_id'] ?? '';

$stmt = $pdo->prepare("SELECT o.*, c.customer_name FROM `order` o JOIN customer c ON o.customer_id = c.customer_id WHERE o.order_id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM order_detail WHERE order_id = ? ORDER BY order_product_number");
$stmt->execute([$orderId]);
$orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>MBSアプリ - 注文書編集画面</title>
  <style>
    body { font-family: "Hiragino Kaku Gothic ProN", sans-serif; background-color: white; margin: 0; padding: 0; }
    header { background-color: forestgreen; color: white; padding: 15px; border-radius: 16px; }
    header h1 { margin: 0; font-size: 20px; }
    .readonly-gray { background-color: #ccc; color: #333; }
    table { border-collapse: collapse; margin: 30px auto; width: fit-content; }
    table th, table td { border: 1px solid #999; padding: 10px; text-align: center; }
    .status-green { background-color: green; color: white; padding: 4px 8px; border-radius: 4px; }
    .status-red { background-color: orange; color: white; padding: 4px 8px; border-radius: 4px; }
    .btn-common { width: 200px; padding: 12px; background-color: cornflowerblue; border-radius: 8px; color: white; cursor: pointer; margin: 10px; border: none; }
    .btn-red { background: none; border: none; color: red; font-weight: bold; font-size: 16px; cursor: pointer; margin: 10px; }
    .btn-plus { background-color: cornflowerblue; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 20px; cursor: pointer; margin: 10px; }
  </style>
</head>
<body>
<header><h1>注文書編集画面</h1></header>

<form method="POST" action="orderUpdateSubmit.php" onsubmit="return prepareSubmit()">
  <p>
    注文ID: <input type="text" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>" readonly class="readonly-gray">
    作成日: <input type="text" value="<?= htmlspecialchars($order['order_date']) ?>" readonly class="readonly-gray">
    状態:
    <input type="radio" name="order_state" value="0" <?= $order['order_state'] == 0 ? 'checked' : '' ?>>未納品
    <input type="radio" name="order_state" value="1" <?= $order['order_state'] == 1 ? 'checked' : '' ?>>納品済
  </p>
  <p>
    顧客ID: <input type="text" value="<?= htmlspecialchars($order['customer_id']) ?>" readonly class="readonly-gray">
    顧客名: <input type="text" value="<?= htmlspecialchars($order['customer_name']) ?>" readonly class="readonly-gray">
    納品日: <input type="text" name="order_delivered_date" id="deliveryDate" value="<?= htmlspecialchars($order['order_delivered_date'] ?? '') ?>">
  </p>

  <table>
    <thead>
      <tr>
        <th></th><th>品名</th><th>注文数量</th><th>未納数量</th><th>単価</th><th>摘要</th><th>状態</th>
      </tr>
    </thead>
    <tbody id="orderDetailBody">
      <?php foreach ($orderDetails as $row): ?>
      <tr>
        <td><input type="checkbox"></td>
        <td><input type="text" value="<?= htmlspecialchars($row['product_name']) ?>"></td>
        <td><input type="number" min="0" value="<?= $row['product_quantity'] ?>" oninput="validateNonNegative(this)"></td>
        <td><input type="number" value="<?= $row['undelivered_quantity'] ?>" readonly class="readonly-gray"></td>
        <td>
          <input type="number"
                value="<?= $row['product_price'] ?>"
                min="0"
                step="any"
                onkeydown="preventSpinner(event)"
                oninput="validateNonNegative(this)"
                style="-moz-appearance: textfield; appearance: textfield;">
        </td>
        <td><input type="text" value="<?= htmlspecialchars($row['product_abstract']) ?>"></td>
        <td class="<?= $row['undelivered_quantity'] == 0 ? 'status-green' : 'status-red' ?>">
          <?= $row['undelivered_quantity'] == 0 ? '納品済' : '未納品' ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div style="text-align:center">
    <button type="button" class="btn-red" onclick="deleteCheckedRows()">削除</button>
    <button type="button" class="btn-plus" onclick="addRow()">＋</button>
  </div>
  <div style="text-align:center">
    <button type="button" class="btn-common" onclick="location.href='orderHome.php'">戻る</button>
    <button type="submit" class="btn-common">保存</button>
  </div>
  <input type="hidden" name="details_json" id="details_json">
</form>

<script>
  let originalStatus = "<?= $order['order_state'] ?>";

  // 手入力で負の値を入れられないようにする
function validateNonNegative(input) {
  if (parseFloat(input.value) < 0) {
    input.value = 0;
  }
}

// 単価でスピン（矢印）を無効化
function preventSpinner(e) {
  // ↑↓矢印キーを無効化
  if (e.key === "ArrowUp" || e.key === "ArrowDown") {
    e.preventDefault();
  }
}

  function addOrderQtyListener(input) {
  let prevQty = parseInt(input.value) || 0;

  input.addEventListener("input", () => {
    const newQty = parseInt(input.value) || 0;
    const diff = newQty - prevQty;

    const row = input.closest("tr");
    const undeliveredInput = row.querySelectorAll("td")[3].querySelector("input");
    let undeliveredQty = parseInt(undeliveredInput.value) || 0;

    if (diff > 0) {
      undeliveredQty += diff;
    } else if (diff < 0) {
      undeliveredQty = Math.max(0, undeliveredQty + diff);
    }

    undeliveredInput.value = undeliveredQty;
    prevQty = newQty;

    // 状態セルも更新
    updateRowStatus(row);
  });
}

function updateRowStatus(row) {
  const undelivered = parseInt(row.querySelectorAll("td")[3].querySelector("input").value) || 0;
  const statusCell = row.querySelectorAll("td")[6];

  if (undelivered === 0) {
    statusCell.textContent = "納品済";
    statusCell.className = "status-green";
  } else {
    statusCell.textContent = "未納品";
    statusCell.className = "status-red";
  }
}

  // 未納数量の変化で状態セルを更新する
  function updateStatus(input) {
    const row = input.closest('tr');
    const remain = parseInt(input.value);
    const statusCell = row.querySelector('td:last-child');

    if (!isNaN(remain)) {
      if (remain === 0) {
        statusCell.textContent = "納品済";
        statusCell.className = "status-green";
      } else {
        statusCell.textContent = "未納品";
        statusCell.className = "status-red";
      }
    } else {
      statusCell.textContent = "";
      statusCell.className = "";
    }
  }

  function deleteCheckedRows() {
    document.querySelectorAll("#orderDetailBody tr").forEach(row => {
      if (row.querySelector("input[type='checkbox']").checked) row.remove();
    });
  }

  window.onload = function () {
  const rows = document.querySelectorAll("#orderDetailBody tr");
  rows.forEach(row => {
    const qtyInput = row.querySelectorAll("td")[2].querySelector("input");
    addOrderQtyListener(qtyInput);
  });
};

  function addRow() {
  const tbody = document.getElementById("orderDetailBody");
  const row = document.createElement("tr");

  row.innerHTML = `
    <td><input type="checkbox"></td>
    <td><input type="text"></td>
    <td><input type="number" min="0" oninput="validateNonNegative(this)"></td>
    <td><input type="number" readonly class="readonly-gray"></td>
    <td><input type="number" min="0" step="any" onkeydown="preventSpinner(event)" oninput="validateNonNegative(this)" style="-moz-appearance: textfield; appearance: textfield;"></td>
    <td><input type="text"></td>
    <td></td>
  `;

  tbody.appendChild(row);

  // 注文数量に対してリスナー追加（数量と未納の差分反映用）
  const qtyInput = row.querySelectorAll("td")[2].querySelector("input");
  addOrderQtyListener(qtyInput);
}


  function prepareSubmit() {
    const selectedStatus = document.querySelector('input[name="order_state"]:checked').value;
    const deliveryInput = document.getElementById("deliveryDate");
    if (originalStatus === "0" && selectedStatus === "1") {
      const now = new Date();
      deliveryInput.value = now.toISOString().slice(0, 19).replace("T", " ");
    } else if (originalStatus === "1" && selectedStatus === "0") {
      deliveryInput.value = "";
    }

    const details = [];
    document.querySelectorAll("#orderDetailBody tr").forEach(row => {
      const cells = row.querySelectorAll("td");
      const name = cells[1].querySelector("input").value;
      const qty = cells[2].querySelector("input").value;
      const remain = cells[3].querySelector("input").value;
      const price = cells[4].querySelector("input").value;
      const note = cells[5].querySelector("input").value;

      if (name || qty || remain || price || note) {
        details.push({ name, qty, remain, price, note });
      }
    });
    document.getElementById("details_json").value = JSON.stringify(details);
    return true;
  }
</script>
</body>
</html>
