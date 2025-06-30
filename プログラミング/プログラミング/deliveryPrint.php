<?php
// =============================
// このファイルは納品書の印刷画面です。
// deliveryHome.phpから顧客名・更新日・納品IDを受け取り、DBから明細を取得して表示します。
// =============================

require_once 'dbConnect.php'; // DB接続用
require_once 'dbConnectFunction.php'; // DB操作関数

// deliveryHome.phpからGETで値を受け取る
$customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : "";
$delivery_date = isset($_GET['delivery_date']) ? $_GET['delivery_date'] : "";
$delivery_id = isset($_GET['delivery_id']) ? $_GET['delivery_id'] : "";

// --- DBから納品明細を取得 ---
$items = [];
$total_qty = 0;
$total_price = 0;
if ($delivery_id !== "") {
    // 納品IDから明細を取得
    $sql = "
        SELECT
            od.product_name AS name,
            od.product_quantity AS qty,
            od.product_price AS price
        FROM delivery_detail AS dd
        INNER JOIN order_detail AS od ON dd.order_id = od.order_id AND dd.order_product_number = od.order_product_number
        WHERE dd.delivery_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delivery_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // 合計数量・金額を計算
    foreach ($items as $row) {
        $total_qty += (int)$row['qty'];
        $total_price += (int)$row['price'] * (int)$row['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>納品書印刷画面</title>
    <style>
        /* 画面全体のデザイン設定 */
        body {
            font-family: 'Meiryo', 'Hiragino Kaku Gothic ProN', sans-serif;
            background: white;
            color: #222;
        }

        /* 納品書のプレビュー枠 */
        .preview-area {
            width: 1050px;
            margin: 30px auto;
            background: #fff;
            padding: 0;
            box-sizing: border-box;
        }

        /* タイトル・日付・No.の行 */
        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 0;
        }

        .title {
            color: #2196f3;
            font-size: 32px;
            font-weight: bold;
            margin-left: 10px;
        }

        .no-area {
            font-size: 18px;
            margin-right: 30px;
            border-bottom: 2px solid #2196f3;
            letter-spacing: 2px;
        }

        /* 顧客名・日付の行 */
        .customer {
            text-align: left;
            font-size: 48px;
            font-weight: 500;
            margin: 10px 0 0 0;
            letter-spacing: 2px;
            border-bottom: 4px solid #222;
            padding-bottom: 5px;
            flex: 1;
        }

        .sama {
            color: #2196f3;
            font-size: 44px;
            font-weight: bold;
            margin-left: 20px;
        }

        .date-area {
            font-size: 18px;
            margin-right: 30px;
            border-bottom: 2px solid #2196f3;
            letter-spacing: 2px;
            text-align: center;
            min-width: 220px;
            flex-shrink: 0;
        }

        /* 納品内容説明 */
        .desc {
            color: #2196f3;
            font-size: 16px;
            margin-bottom: 5px;
            margin-left: 10px;
        }

        /* 明細テーブル全体 */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .main-table th,
        .main-table td {
            border: 2px solid #2196f3;
            font-size: 26px;
            text-align: center;
            padding: 4px 0;
        }

        .main-table th {
            color: #2196f3;
            font-size: 18px;
            font-weight: normal;
            background: #fff;
        }

        .main-table .item-no {
            width: 40px;
        }

        .main-table .item-name {
            text-align: left;
            padding-left: 18px;
        }

        .main-table .item-qty,
        .main-table .item-price {
            width: 80px;
        }

        .main-table .item-amount {
            width: 70px;
        }

        .main-table .amount-label {
            font-size: 16px;
            font-weight: normal;
            color: #2196f3;
            border: none;
            background: none;
            padding: 0;
        }

        .main-table .yen {
            font-size: 22px;
            font-family: 'Arial';
        }

        .main-table .big {
            font-size: 38px;
            font-weight: bold;
            color: #2196f3;
        }

        .main-table .big-total {
            font-size: 38px;
            font-weight: bold;
            color: #2196f3;
            text-align: center;
        }

        .main-table .big-total-amount {
            font-size: 38px;
            font-weight: bold;
            color: #2196f3;
            text-align: right;
        }

        .main-table .total-amount-cell {
            font-size: 44px;
            font-weight: bold;
            color: #222;
            text-align: right;
            border: none;
        }

        .main-table .border-none {
            border: none;
        }

        /* 税率・合計金額の行 */
        .main-table .tax-row th,
        .main-table .tax-row td {
            font-size: 18px;
            color: #2196f3;
            border-top: 2px solid #2196f3;
        }

        .main-table .tax-row .tax-label {
            font-size: 18px;
            color: #2196f3;
        }

        .main-table .tax-row .tax-amount {
            font-size: 18px;
            color: #2196f3;
        }

        .main-table .tax-row .total-amount-cell {
            font-size: 44px;
            color: #222;
            font-weight: bold;
            text-align: right;
        }

        /* 印刷・戻るボタンのデザイン */
        .button-print,
        .button-back {
            width: 150px;
            padding: 12px;
            margin: 20px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: cornflowerblue;
            cursor: pointer;
        }

        .button-back {
            background-color: whitesmoke;
        }

        .button-back:hover {
            background-color: gray;
        }

        .button-print:hover {
            background: blue;
        }

        .button:active {
            box-shadow: inset 2px 2px 5px #666;
        }

        /* 印刷時はボタンを非表示にする */
        @media print {
            .button-print,
            .button-back {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="preview-area">
        <!-- タイトル・日付・No.を横並びで表示 -->
        <div class="title-row" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0;">
            <span class="title">納品書</span>
            <span class="date-area" style="font-size:18px; margin-right:30px; border-bottom:2px solid #2196f3; letter-spacing:2px; text-align:center; min-width:220px; flex-shrink:0; align-self:flex-end;">
                <?= htmlspecialchars($delivery_date) ?>
            </span>
            <span class="no-area" style="font-size:18px; margin-left:20px; border-bottom:2px solid #2196f3; letter-spacing:2px; align-self:flex-end;">No.</span>
        </div>
        <!-- 顧客名（左寄せ） -->
        <div class="customer" style="text-align:left; font-size:48px; font-weight:500; margin:10px 0 0 0; letter-spacing:2px; border-bottom:4px solid #222; padding-bottom:5px; flex:1;">
            <?= htmlspecialchars($customer_name) ?> <span class="sama">様</span>
        </div>
        <!-- 納品内容の説明文 -->
        <div class="desc">下記の通り納品いたしました</div>
        <!-- 明細テーブル（商品一覧） -->
        <table class="main-table">
            <tr>
                <th class="item-no"></th>
                <th colspan="2">品　名</th>
                <th>数量</th>
                <th>単価</th>
                <th style="width:180px; text-align:right;">金額（税抜・税込）
                </th>
                <th style="width:40px;"></th> <!-- 一番右の細い空白列 -->
            </tr>
            <?php // 商品は最大5行分表示します。空欄も出力されます。
            for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td colspan="2" class="item-name"><?php if (isset($items[$i])) echo htmlspecialchars($items[$i]["name"]); ?></td>
                    <td><?php if (isset($items[$i])) echo $items[$i]["qty"]; ?></td>
                    <td><?php if (isset($items[$i])) echo '￥' . number_format($items[$i]["price"]); ?></td>
                    <td class="yen" style="text-align:right;">
                        <?php // 金額（単価×数量）を表示
                        if (isset($items[$i]) && $items[$i]["name"] !== "") {
                            echo '￥' . number_format((int)$items[$i]["price"] * (int)$items[$i]["qty"]);
                        } ?>
                    </td>
                    <td></td> <!-- 一番右の細い空白セル -->
                </tr>
            <?php endfor; ?>
            <!-- 合計行 -->
            <tr>
                <td class="big"></td>
                <td class="big">合　計</td>
                <td></td>
                <td class="big-total"><?= $total_qty ?></td>
                <td></td>
                <td class="big-total-amount" colspan="1" style="text-align:right;">￥<?= number_format($total_price) ?></td>
                <td></td> <!-- 合計行も右端空白 -->
            </tr>
            <!-- 税率・税込合計金額の行 -->
            <tr class="tax-row">
                <th class="tax-label">税率</th>
                <td style="border-left:2px solid #2196f3;"></td>
                <td></td>
                <td class="tax-label" style="border-left:none;">消費税額等</td>
                <td class="tax-label"></td>
                <td class="tax-label">税込合計金額</td>
                <td class="total-amount-cell" colspan="1" style="border-right:2px solid #2196f3; border-bottom:2px solid #2196f3; text-align:right;">￥<?= number_format($total_price) ?></td>
            </tr>
        </table>
    </div>
    <!-- 戻る・印刷ボタン -->
    <div style="text-align:center; margin-top:30px;">
        <a href="deliveryHome.php"><button class="button-back">戻る</button></a>
        <button class="button-print" onclick="window.print()">印刷</button>
    </div>
</body>

</html>