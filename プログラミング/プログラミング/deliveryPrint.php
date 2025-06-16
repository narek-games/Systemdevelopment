<?php
// 仮データ（本来はDBから取得）
$customer_name = "大阪情報専門学校";
$delivery_date = "2022年10月17日";
$items = [
    ["name" => "日経コンピュータ　11月号", "qty" => 1, "price" => 1300],
    ["name" => "日経ネットワーク　11月号", "qty" => 1, "price" => 1300],
];
$total_qty = 2;
$total_price = 2600;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>納品書印刷画面</title>
    <style>
        body {
            font-family: 'Meiryo', 'Hiragino Kaku Gothic ProN', sans-serif;
            background: white;
            color: #222;
        }
        .preview-area {
            width: 1100px;
            margin: 30px auto;
            border: 2px solid #2196f3;
            background: #fff;
            padding: 30px 40px 10px 40px;
            box-sizing: border-box;
        }
        .title {
            color: #2196f3;
            font-size: 32px;
            font-weight: bold;
            display: inline-block;
            margin-right: 40px;
        }
        .date-area {
            float: right;
            font-size: 18px;
            margin-top: 10px;
        }
        .customer {
            text-align: center;
            font-size: 40px;
            font-weight: 500;
            margin: 30px 0 0 0;
            letter-spacing: 2px;
        }
        .sama {
            color: #2196f3;
            font-size: 38px;
            font-weight: bold;
            margin-left: 20px;
        }
        .desc {
            color: #2196f3;
            font-size: 16px;
            margin-bottom: 5px;
            margin-left: 5px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .main-table th, .main-table td {
            border: 1px solid #2196f3;
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
        .main-table .item-qty, .main-table .item-price {
            width: 80px;
        }
        .main-table .item-amount {
            width: 140px;
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
            font-size: 32px;
            font-weight: bold;
            color: #2196f3;
        }
        .main-table .big-total {
            font-size: 32px;
            font-weight: bold;
            color: #2196f3;
            text-align: center;
        }
        .main-table .big-total-amount {
            font-size: 32px;
            font-weight: bold;
            color: #2196f3;
            text-align: right;
        }
        .main-table .total-amount-cell {
            font-size: 38px;
            font-weight: bold;
            color: #222;
            text-align: right;
            border: none;
        }
        .main-table .border-none {
            border: none;
        }
        .main-table .tax-row th, .main-table .tax-row td {
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
            font-size: 38px;
            color: #222;
            font-weight: bold;
            text-align: right;
        }
        /* 戻る・印刷ボタンを元のデザインに */
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
    </style>
</head>
<body>
    <div class="preview-area">
        <div style="overflow: hidden;">
            <span class="title">納品書</span>
            <span class="date-area">2022 年 10 月 17 日　　No.</span>
        </div>
        <div class="customer">
            <?= htmlspecialchars($customer_name) ?> <span class="sama">様</span>
        </div>
        <div class="desc">下記の通り納品いたしました</div>
        <table class="main-table">
            <tr>
                <th class="item-no"></th>
                <th>品　名</th>
                <th>数量</th>
                <th>単価</th>
                <th colspan="2">金額（税抜・税込）</th>
                <th></th>
            </tr>
            <?php for($i=0; $i<5; $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td class="item-name"><?php if(isset($items[$i])) echo htmlspecialchars($items[$i]["name"]); ?></td>
                <td><?php if(isset($items[$i])) echo $items[$i]["qty"]; ?></td>
                <td><?php if(isset($items[$i])) echo number_format($items[$i]["price"]); ?></td>
                <td class="yen">￥<?php if(isset($items[$i])) echo number_format($items[$i]["price"]); ?></td>
                <td class="yen">￥<?php if(isset($items[$i])) echo number_format($items[$i]["price"]); ?></td>
                <td></td>
            </tr>
            <?php endfor; ?>
            <tr>
                <td class="big">合</td>
                <td class="big">計</td>
                <td class="big-total"><?= $total_qty ?></td>
                <td></td>
                <td class="big-total-amount" colspan="2">￥<?= number_format($total_price) ?></td>
                <td></td>
            </tr>
            <tr class="tax-row">
                <th class="tax-label">税率</th>
                <td></td>
                <td></td>
                <td class="tax-label">消費税額等</td>
                <td class="tax-label">税込合計金額</td>
                <td class="total-amount-cell" colspan="2">￥<?= number_format($total_price) ?></td>
            </tr>
        </table>
    </div>
    <div style="text-align:center; margin-top:30px;">
        <a href="deliveryHome.html"><button class="button-back">戻る</button></a>
        <button class="button-print" onclick="window.print()">印刷</button>
    </div>
</body>
</html>
