<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>納品書管理画面</title>
  <style>
    body {
      font-family: sans-serif;
    }

    h1 {
      font-size: 24px;
      margin-bottom: 10px;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
    }

    th {
      background-color: #f0f0f0;
    }

    .btn-blue a {
      color: blue;
      text-decoration: none;
    }

    .btn-red a {
      color: red;
      text-decoration: none;
    }

    .btn-orange a {
      color: orange;
      text-decoration: none;
    }

    .footer-buttons {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 20px;
    }

    .footer-buttons a {
      font-size: 20px;
      padding: 10px 30px;
      border-radius: 10px;
      border: 2px solid #ccc;
      background: #cfe0f8;
      cursor: pointer;
      text-decoration: none;
      color: black;
      display: inline-block;
      text-align: center;
    }

    .scrollbar {
      float: right;
      height: 200px;
      width: 20px;
      background: #eee;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
    }

    .scrollbar div {
      font-size: 20px;
    }
  </style>
</head>

<body>

  <h1>納品書管理画面</h1>

  <div style="display: flex;">
    <table>
      <thead>
        <tr>
          <th>納品ID</th>
          <th>顧客ID</th>
          <th>更新日</th>
          <th>顧客名</th>
          <th colspan="3">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>DE000001</td>
          <td>CU000001</td>
          <td>2022年10月17日</td>
          <td>大阪情報専門学校</td>
          <td class="btn-blue"><a href="deliveryUpdate.php">編集</a></td>
          <td class="btn-red"><a href="deliveryDelete.php">削除</a></td>
          <td class="btn-orange"><a href="deliveryPrint.php">印刷</a></td>
        </tr>
        <!-- 空行 -->
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="7" style="height: 20px;"></td>
        </tr>
      </tbody>
    </table>

    <div class="scrollbar">
      <div>▲</div>
      <div>▼</div>
    </div>
  </div>

  <div class="footer-buttons">
    <a href="home.html">戻る</a>
    <a href="orderOption.php">新規納品書作成</a>
  </div>

</body>

</html>