<?php
// integrated_stats.php
 
// データベース接続関数をインクルード
include 'dbConnectFunction.php';
 
// Ajaxリクエストかどうかを判断するためのフラグ
$is_ajax_request = isset($_GET['is_ajax']) && $_GET['is_ajax'] == '1';
 
// Ajaxリクエストの場合
if ($is_ajax_request) {
    header('Content-Type: application/json'); // JSON形式で返すことを宣言
 
    $keyword = $_GET['keyword'] ?? ''; // JavaScriptから渡されたキーワードを取得
 
    try {
        $stmt = getStatistics($keyword); // 統計情報を取得
 
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
 
        echo json_encode($results); // 結果をJSON形式で出力
 
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'データベースエラー: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => '予期せぬエラーが発生しました: ' . $e->getMessage()]);
    }
    exit; // Ajaxリクエストの場合はここで処理を終了し、HTMLは出力しない
}
 
// 通常のページロードの場合（以下HTML部分）
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>統計情報確認画面</title>
  <style>
    /* 基本的なスタイル */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
 
    /* ヘッダー */
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
 
    /* 検索入力フィールド */
    input[type="text"] {
      width: 300px;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
 
    /* テーブル */
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
 
    /* ボタンコンテナ */
    .button-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin: 30px 0;
    }
 
    /* ボタン */
    .back-button {
      padding: 12px 50px;
      background-color: #6699ff;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      color: black;
      cursor: pointer;
      box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
      transition: background-color 0.3s ease; /* ホバーエフェクト */
    }
 
    .back-button:hover {
      background-color: blue;
      color: white; /* ホバー時の文字色も変更 */
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-title">統計情報確認画面</div>
  </div>
 
  <div style="margin-left: 40px; margin-top: 20px;">
    <input type="text" id="searchInput" name="keyword" placeholder="顧客名または顧客ID" value="">
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
    <tbody id="searchResults">
      <?php
        // 初回ページロード時にデータベースから全データを取得して表示
        // または、検索パラメータがあればそのキーワードで検索して表示
        $initial_keyword = $_GET['keyword'] ?? '';
        $stmt = getStatistics($initial_keyword); // dbConnectFunction.phpの関数を使用
 
        if ($stmt->rowCount() === 0 && empty($initial_keyword)) {
            echo '<tr><td colspan="4">検索条件を入力してください...</td></tr>';
        } elseif ($stmt->rowCount() === 0) {
            echo '<tr><td colspan="4">該当するデータはありません。</td></tr>';
        } else {
           while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$row['customer_id']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['customer_sales']}</td>
            <td>" . ($row['customer_average_leadtime'] ?? 0) . "</td>
          </tr>";
          }

        }
      ?>
    </tbody>
  </table>
 
  <div class="button-container">
    <a href="./home.html"><button class="back-button">戻る</button></a>
    <a href="./customerUpload.php"><button class="back-button">アップロード</button></a>
  </div>
 
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const searchResultsBody = document.getElementById('searchResults');
      let timeout = null; // デバウンス用のタイマー
 
      // 検索を実行し、テーブルを更新する関数
      function performSearch(keyword) {
        // AjaxリクエストのURLをこのファイル自身に設定し、is_ajaxパラメータを追加
        const url = `<?php echo basename(__FILE__); ?>?is_ajax=1&keyword=${encodeURIComponent(keyword)}`;
 
        fetch(url)
          .then(response => {
            if (!response.ok) {
              throw new Error('ネットワーク応答が不正です: ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            searchResultsBody.innerHTML = ''; // 既存の検索結果をクリア
 
            if (data.length === 0) {
              searchResultsBody.innerHTML = '<tr><td colspan="4">該当するデータはありません。</td></tr>';
              return;
            }
 
            data.forEach(row => {
              const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${row.customer_id}</td>
              <td>${row.customer_name}</td>
              <td>${row.customer_sales}</td>
              <td>${row.customer_average_leadtime ?? 0}</td>
            `;

              searchResultsBody.appendChild(tr);
            });
          })
          .catch(error => {
            console.error('検索中にエラーが発生しました:', error);
            searchResultsBody.innerHTML = '<tr><td colspan="4">検索中にエラーが発生しました。エラー: ' + error.message + '</td></tr>';
          });
      }
 
      // 検索入力フィールドのイベントリスナー
      searchInput.addEventListener('input', function() {
        clearTimeout(timeout); // 既存のタイマーをクリア
        const keyword = this.value;
 
        // デバウンス
        timeout = setTimeout(() => {
          performSearch(keyword);
        }, 300); // 300ミリ秒入力がなければ検索を実行
      });
 
      // ページ読み込み時にURLにkeywordパラメータがあれば初期検索を実行
      const urlParams = new URLSearchParams(window.location.search);
      const initialKeyword = urlParams.get('keyword');
      if (initialKeyword) {
          searchInput.value = initialKeyword;
          // ここでは既にPHPで初期表示されているため、
          // ページロード時の初期検索は不要ですが、
          // JS側で強制的に再検索させたい場合は以下を有効にします
          // performSearch(initialKeyword);
      }
    });
  </script>
</body>
</html>