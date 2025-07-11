<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>統計情報確認画面</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
 
    .header {
      background-color: blue;
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
 
    h1 {
      font-size: 24px;
      margin: 40px;
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
      width: 90%;
      margin: 0 auto;
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
      margin: 30px 0;
    }
 
    .back-button {
      padding: 12px 50px;
      background-color: #6699ff;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      color: black;
      cursor: pointer;
      box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    }
 
    .back-button:hover {
      background-color: blue;
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-title">統計情報確認画面</div>
  </div>
 
  <div style="margin-left: 40px; margin-top: 20px;">
    <form method="get" action="">
      <input type="text" name="keyword" placeholder="顧客名または顧客ID" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
      <button class="search">🔍</button>
    </form>
  </div>
 
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
 
        $keyword = $_GET['keyword'] ?? '';
        $stmt = getStatistics($keyword);
 
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
    <a href="./customerUpload.php"><button class="back-button">アップロード</button></a>
  </div>
</body>
</html>
 