<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ようこそ画面</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container small-container">
        <h2>ようこそ！</h2>
        <h1>目指せ！ファッションリーダー</h1>
        <p>あなたの毎日のコーデを簡単に管理できます</p>
        <br>
        
        <a href="{{ route('login') }}" class="button primary full">ログイン</a>
        
        <a href="{{ route('register') }}" class="button full">新規登録</a>
    </div>
</body>
</html>