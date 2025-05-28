<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>MBSアプリ</title>
    </head>
    <body>

        <!--  検索  -->
        <div>
            <input type="text">
            <input type="button" value="検索">
        </div>

        <!--  注文書table  -->
        <div>
            <style>
                table, table * {
                    border: 1px solid black;
                }
            </style>
            <table>

                <!--  表の1行目  -->
                <tr>
                    <th>注文ID</th>
                    <th>顧客名</th>
                    <th>作成日</th>
                    <th>状態</th>
                    <th></th>
                    <th></th>
                </tr>
                
                <!--  表の2行目以降  -->
                <?php
                    // DB接続ファイルを読み込む, 実行する
                    require_once 'dbConnect.php';
                    
                    // try catch文でDB関係を全部囲む
                    try {

                        // DBから必要な行を取得するためのSQL文組み立て
                        $sql = "
                            SELECT  *
                            FROM    customer_order
                        ";                          // tabキーでインデントしよう

                        // SQLするときお決まりフレーズ(関数と引数が場面によって変わる, prepare関数を使ったりもする)
                        $statement = $pdo->query($sql);
                        
                        // tableの中身の作成
                        // while文でDBから取得した行のぶんだけ実行
                        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['order_id']) . '</td>';         // ※order_idとかはDBの列名と同じにする！！
                            echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                            echo '<td><input type="button" value="編集"></td>';
                            echo '<td><input type="button" value="削除"></td>';
                            echo '</tr>';
                        }
                    } catch (PDOException $e) {
                        echo "エラー: " . $e->getMessage();
                    }
                ?>
            </table>
        </div>

        <!--  戻る, 新規作成button  -->
        <div>
            <input type="button" value="戻る">
            <input type="button" value="新規注文書作成">
        </div>
    </body>
</html>