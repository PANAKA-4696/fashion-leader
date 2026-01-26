<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>コーデマスター管理</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>コーデマスター管理</h1>
        <a href="/main/calendar" class="back-btn">カレンダーへ戻る</a>
    </div>

    <div class="container">
        <p>
            ここでは、アプリに登録されている<strong>すべてのコーデ（マスターデータ）</strong>を管理します。<br>
            クローゼットに追加する「コーデの原型」を作成・編集・削除できます。
        </p>
        
        <p style="font-size: 14px; color: #555;">
            <strong>注意:</strong> ここでの変更や削除は、<strong>すべてのクローゼットに影響する</strong>可能性があります。
        </p>
        <hr>

        <a href="{{ route('coord.save') }}" class="button primary" style="margin-bottom: 15px;">
            コーデをマスターに保存
        </a>
        
        <a href="{{ route('coord.choice') }}" class="button" style="margin-bottom: 15px;">
            コーデをマスターから変更
        </a>
        
        <a href="{{ route('coord.delete') }}" class="button danger">
            コーデをマスターから削除
        </a>
    </div>
</body>
</html>