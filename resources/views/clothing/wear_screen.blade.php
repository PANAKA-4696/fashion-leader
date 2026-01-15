<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>服マスター管理</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター管理</h1>
        <a href="../main/calendar_menu.html">メインへ戻る</a>
    </div>

    <div class="container">
        <p>
            ここでは、アプリに登録されている<strong>すべての服（マスターデータ）</strong>を管理します。<br>
            ここでの変更や削除は、<strong>すべてのクローゼットに影響します</strong>のでご注意ください。
        </p>
        <p style="font-size: 14px; color: #555;">（クローゼット内の服を非表示にする・入れ替える機能は、各クローゼットの詳細画面で行います）</p>

        <a href="wear-change" class="button">服の情報を変更</a>
        <a href="clothing-add" class="button primary">服をマスターに追加</a>
        <a href="wear-delete" class="button danger">服をマスターから削除</a>
        <hr>

        <h3>登録済みの服一覧 (全件)</h3>
        <div class="coord-item">
            <p class="item-line">
                <span class="img-box"><img class="clothing-img" src="../assets/images/シャツ.jpg" alt="シャツ"></span>
                <span style="flex-grow: 1;">シャツ (トップス) <span class="favorite-display">❤</span></span>
            </p>
            <p class="item-line">
                <span class="img-box"><img class="clothing-img" src="../assets/images/パンツ.jpg" alt="パンツ"></span>
                <span style="flex-grow: 1;">パンツ (ボトムス)</span>
            </p>
            <p class="item-line">
                <span class="img-box"><img class="clothing-img" src="../assets/images/シューズ.jpg" alt="シューズ"></span>
                <span style="flex-grow: 1;">シューズ (シューズ) <span class="favorite-display">❤</span></span>
            </p>
            <p class="item-line">
                <span class="img-box"><img class="clothing-img" src="../assets/images/outerwear.jpg" alt="アウター"></span>
                <span style="flex-grow: 1;">アウター (アウター)</span>
            </p>
            <p class="item-line">
                <span class="img-box"><img class="clothing-img" src="../assets/images/シャツ2.jpg" alt="シャツ2"></span>
                <span style="flex-grow: 1;">シャツ2 (トップス)</span>
            </p>
            </div>
    </div>
</body>
</html>