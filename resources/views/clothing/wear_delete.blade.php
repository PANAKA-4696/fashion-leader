<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>服マスター削除 (選択)</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター削除 (選択)</h1>
        <a href="wear-screen">服管理へ戻る</a>
    </div>

    <div class="container">
        <h2>マスターから削除する服を選択</h2>
        <p style="color: #c62828; font-weight: bold;">
            注意: ここで削除を実行すると、すべてのクローゼットからこの服のデータが完全に削除されます。<br>
            この操作は取り消せません。
        </p>
        
        <form action="wear-screen" method="post" onsubmit="return confirm('本当にこのアイテムをマスターから削除しますか？');">
            
            <label for="item_select" style="font-weight: bold; font-size: 18px;">削除する服を選択</label>

            <div class="image-select-list">
                
                <div class="image-select-item">
                    <input type="radio" id="item_1" name="delete_id" value="1" required>
                    <label for="item_1">
                        <span class="img-box"><img class="clothing-img" src="../assets/images/シャツ.jpg" alt="シャツ"></span>
                        <span>シャツ (トップス) <span class="favorite-display">❤</span></span>
                    </label>
                </div>
                
                <div class="image-select-item">
                    <input type="radio" id="item_5" name="delete_id" value="5">
                    <label for="item_5">
                        <span class="img-box"><img class="clothing-img" src="../assets/images/シャツ2.jpg" alt="シャツ2"></span>
                        <span>シャツ2 (トップス)</span>
                    </label>
                </div>

                <div class="image-select-item">
                    <input type="radio" id="item_2" name="delete_id" value="2">
                    <label for="item_2">
                        <span class="img-box"><img class="clothing-img" src="../assets/images/パンツ.jpg" alt="パンツ"></span>
                        <span>パンツ (ボトムス)</span>
                    </label>
                </div>
                
                <div class="image-select-item">
                    <input type="radio" id="item_4" name="delete_id" value="4">
                    <label for="item_4">
                        <span class="img-box"><img class="clothing-img" src="../assets/images/outerwear.jpg" alt="アウター"></span>
                        <span>アウター (アウター)</span>
                    </label>
                </div>

                <div class="image-select-item">
                    <input type="radio" id="item_3" name="delete_id" value="3">
                    <label for="item_3">
                        <span class="img-box"><img class="clothing-img" src="../assets/images/シューズ.jpg" alt="シューズ"></span>
                        <span>シューズ (シューズ) <span class="favorite-display">❤</span></span>
                    </label>
                </div>
            </div>
            <hr>
            <input type="submit" value="選択した服をマスターから削除" class="danger">
            <a href="wear-screen" class="button">キャンセル</a>
        </form>
    </div>
</body>
</html>