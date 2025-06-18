<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>統計情報確認画面</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }

    h1 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    input[type="text"] {
      width: 300px;
      padding: 6px;
    }

    button.search {
      padding: 6px 10px;
      margin-left: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border: 1px solid #666;
    }

    th, td {
      border: 1px solid #666;
      padding: 10px;
      text-align: center;
      height: 40px;
    }

    th {
      background-color: #f0f0f0;
    }

    td a {
      color: blue;
      text-decoration: none;
    }

    .button-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
    }

    .back-button {
      padding: 12px 50px;
      background-color: #cce0ff;
      border: 1px solid #999;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <h1>統計情報確認画面</h1>

  <input type="text" placeholder="顧客名または顧客ID">
  <button class="search">🔍</button>

  <table>
    <thead>
      <tr>
        <th>顧客ID</th>
        <th>顧　客　名</th>
        <th>累計売上<br>（円）</th>
        <th>平均リードタイム<br>（日）</th>
      </tr>
    </thead>
    <tbody>
        <?php
            include 'dbConnectFunction.php';
            $stmt = getStatistics();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                    <td><a href='#'>{$row['customer_id']}</a></td>
                    <td><a href='#'>{$row['customer_name']}</a></td>
                    <td>{$row['customer_sales']}</td>
                    <td>{$row['customer_average_leadtime']}</td>
                </tr>";
            }
        ?>
    </tbody>
  </table>

  <div class="button-container">
    <a href="./home.html"><button class="back-button">戻る</button></a>
    <a href="./customerUpload.html"><button class="back-button">アップロード</button></a>
  </div>

</body>
</html>
