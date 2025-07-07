<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;


$host = "10.15.153.12";
$username = "SysDevB";
$password = "1212";
$dbname = "mbs";
$table_name = "customer";


$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excel"])) {
    // Corrected variable from $servername to $host
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        $message = "データベース接続失敗: " . $conn->connect_error;
        $message_type = "error";
    } else {
        $conn->set_charset("utf8mb4"); 
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $uploaded_file_name = basename($_FILES["excel"]["name"]);
        $target_file_path = $target_dir . $uploaded_file_name;
        $file_extension = strtolower(pathinfo($target_file_path, PATHINFO_EXTENSION));

        if ($file_extension != "xlsx") {
            $message = "エラー: XLSXファイルのみアップロードできます。";
            $message_type = "error";
        } else {
            if (move_uploaded_file($_FILES["excel"]["tmp_name"], $target_file_path)) {
                try {
                    $spreadsheet = IOFactory::load($target_file_path);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();
                    $inserted_rows_count = 0;

                    $sql = "INSERT INTO " . $table_name . " (customer_id, customer_name, customer_person, address, phone_number, delivery_notes, customer_notes, registration_date, customer_sales, customer_leadtime, customer_delivery_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            customer_name = VALUES(customer_name),
                            customer_person = VALUES(customer_person),
                            address = VALUES(address),
                            phone_number = VALUES(phone_number),
                            delivery_notes = VALUES(delivery_notes),
                            customer_notes = VALUES(customer_notes),
                            registration_date = VALUES(registration_date),
                            customer_sales = VALUES(customer_sales),
                            customer_leadtime = VALUES(customer_leadtime),
                            customer_delivery_count = VALUES(customer_delivery_count)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("SQL準備エラー: " . $conn->error);
                    }

                    for ($row = 2; $row <= $highestRow; ++$row) {
                        $customer_id_val = $worksheet->getCell('A' . $row)->getValue();
                        $customer_name_val = $worksheet->getCell('C' . $row)->getValue();
                        $customer_person_val = $worksheet->getCell('D' . $row)->getValue();
                        $address_val = $worksheet->getCell('E' . $row)->getValue();
                        $phone_number_val = $worksheet->getCell('F' . $row)->getValue();
                        $delivery_index_val = $worksheet->getCell('G' . $row)->getValue();
                        $customer_notes_val = $worksheet->getCell('H' . $row)->getValue();
                        $registration_date_excel = $worksheet->getCell('I' . $row)->getValue();



                        // registration_dateの処理
// registration_dateの処理
$registration_date_cell = $worksheet->getCell('I' . $row);
$registration_date_excel = $registration_date_cell->getValue();
$registration_date_formatted = null;

// Excel日付値が数値であり、かつ日付として有効な範囲内かを確認
if (is_numeric($registration_date_excel) && $registration_date_excel > 0) { // Excel日付は1900年1月1日を1とする数値、負の値は無効
    try {
        $registration_date_obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($registration_date_excel);
        $registration_date_formatted = $registration_date_obj->format('Y-m-d');
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        // Excel日付の変換に失敗した場合（例: 無効な日付値）
        error_log("Invalid Excel Date for registration_date at row " . $row . ": " . $registration_date_excel . " - " . $e->getMessage());
        $registration_date_formatted = null; // または適切なデフォルト値
    }
}


// customer_deadlineの処理
$customer_deadline_cell = $worksheet->getCell('J' . $row);
$customer_deadline_excel = $customer_deadline_cell->getValue();
$customer_deadline_formatted = null;

// Excel日付値が数値であり、かつ日付として有効な範囲内かを確認
if (is_numeric($customer_deadline_excel) && $customer_deadline_excel > 0) {
    try {
        $customer_deadline_obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($customer_deadline_excel);
        $customer_deadline_formatted = $customer_deadline_obj->format('Y-m-d');
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        // Excel日付の変換に失敗した場合
        error_log("Invalid Excel Date for customer_deadline at row " . $row . ": " . $customer_deadline_excel . " - " . $e->getMessage());
        $customer_deadline_formatted = null; // または適切なデフォルト値
    }
}

                        $param_types = "isssssssisi";
                        $stmt->bind_param(
                            $param_types,
                            $customer_id_val,
                            $customer_name_val,
                            $customer_person_val,
                            $address_val,
                            $phone_number_val,
                            $delivery_index_val,
                            $customer_notes_val,
                            $registration_date_formatted,
                            $customer_sales_val,
                            $customer_deadline_formatted,
                            $customer_delivery_count_val
                        );

                        if ($stmt->execute()) {
                            $inserted_rows_count++;
                        } else {
                            error_log("データベース挿入エラー (行 $row): " . $stmt->error);
                        }
                    }

                    $stmt->close();
                    unlink($target_file_path);
                    $message = "アップロード完了: " . $inserted_rows_count . " 件のレコードが追加されました。";
                    $message_type = "success";

                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    $message = "Excel読み込みエラー: " . $e->getMessage();
                    $message_type = "error";
                    if (file_exists($target_file_path)) unlink($target_file_path);
                } catch (Exception $e) {
                    $message = "サーバーエラー: " . $e->getMessage();
                    $message_type = "error";
                    if (file_exists($target_file_path)) unlink($target_file_path);
                }
            } else {
                $message = "ファイルのアップロードに失敗しました。";
                $message_type = "error";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>顧客情報アップロード画面</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    header {
      width: 100%;
      background-color: #0000FF; /* Blue color from the image */
      color: white;
      padding: 15px 20px;
      text-align: left;
      font-size: 24px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .container {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 600px; /* Adjust as needed */
      margin-top: 50px;
      text-align: center;
    }

    .message {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-weight: bold;
    }

    .message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .upload-area {
      border: 2px dashed #ccc;
      border-radius: 8px;
      padding: 50px 20px;
      text-align: center;
      cursor: pointer;
      margin-bottom: 20px;
      transition: background-color 0.3s ease;
      background-color: #f9f9f9;
    }

    .upload-area:hover {
      background-color: #eef;
    }

    .upload-icon {
      font-size: 60px;
      color: #0000FF; /* Blue color */
      margin-bottom: 10px;
    }

    .upload-text {
      color: #555;
      font-size: 1.1em;
    }

    .file-select {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 30px;
      gap: 10px; /* Space between input and button */
    }

    .file-name {
      flex-grow: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1em;
      text-align: left;
    }

    .button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1em;
      transition: background-color 0.3s ease;
      color: white; /* Default text color for buttons */
    }

    .button-back {
      background-color: #6c757d; /* Grey color for "Back" */
      color: white;
      margin-right: 10px;
    }

    .button-back:hover {
      background-color: #5a6268;
    }

    .button-upload {
      background-color: #007bff; /* Blue color for "Upload" */
      color: white;
    }

    .button-upload:hover {
      background-color: #0056b3;
    }

    /* Reference button style */
    .file-select .button {
        background-color: #007bff; /* Blue color from the image */
    }

    .file-select .button:hover {
        background-color: #0056b3;
    }

    /* Menu icon and navigation - placeholder, adjust as needed */
    .menu-icon {
      display: none; /* Hidden for this specific layout, but good to keep */
    }
    nav#menu {
      display: none; /* Hidden for this specific layout */
    }
  </style>
</head>
<body>
  <header>
    <h1>顧客情報アップロード画面</h1>
    <div class="menu-icon" onclick="toggleMenu()"><div></div><div></div><div></div></div>
    <nav id="menu"></nav>
  </header>

  <div class="container">
    <?php if (!empty($message)): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form id="upload-form" method="POST" enctype="multipart/form-data">
      <div class="upload-area" id="drop-area">
        <div class="upload-icon">⬆</div>
        <div class="upload-text">ここにExcelファイルをドラッグ＆ドロップ</div>
      </div>

      <div class="file-select">
        <input type="text" class="file-name" placeholder="選択したファイル名選択" readonly id="file-name">
        <input type="file" id="file-input" name="excel" style="display: none;" accept=".xlsx">
        <button type="button" class="button" onclick="document.getElementById('file-input').click()">参照</button>
      </div>

      <div>
        <a href="./statisticsHome.html"><button type="button" class="button-back">戻る</button></a>
        <button type="button" class="button-upload" onclick="uploadFile()">アップロード</button>
      </div>
    </form>
  </div>

  <script>
    const fileInput = document.getElementById('file-input');
    const fileNameField = document.getElementById('file-name');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        fileNameField.value = fileInput.files[0].name;
      }
    });

    dropArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropArea.style.backgroundColor = '#eef';
    });

    dropArea.addEventListener('dragleave', () => {
      dropArea.style.backgroundColor = '';
    });

    dropArea.addEventListener('drop', (e) => {
      e.preventDefault();
      dropArea.style.backgroundColor = '';
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        fileInput.files = files;
        fileNameField.value = files[0].name;
      }
    });

    function toggleMenu() {
      const menu = document.getElementById('menu');
      menu.classList.toggle('show-menu');
    }

    function uploadFile() {
      const file = fileInput.files[0];
      if (!file) {
        alert("ファイルを選択してください。");
        return;
      }

      const formData = new FormData();
      formData.append("excel", file);

      fetch(window.location.pathname, {
        method: "POST",
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          // Attempt to read error message from server response
          return response.text().then(text => { throw new Error(text) });
        }
        return response.text();
      })
      .then(result => {
        // Replace the entire body content with the new HTML, which includes the message
        document.body.innerHTML = result;
      })
      .catch(error => {
        console.error("エラー:", error);
        alert("アップロード処理中にエラーが発生しました。\n詳細: " + error.message);
      });
    }
  </script>
</body>
</html>