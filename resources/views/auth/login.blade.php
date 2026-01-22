<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン画面</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container small-container">
        <h2>ログイン</h2>

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px; font-size: 0.9em; text-align:center;">
                @error('login_error')
                    {{ $message }}
                @else
                    入力内容を確認してください。
                @enderror
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" placeholder="example@mail.com" value="{{ old('email') }}" required>
            
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" placeholder="••••••" required>
            
            <button type="submit" class="button primary full">ログイン</button>
            
            <div style="text-align: center;">
                <a href="{{ route('register') }}" class="button-link">新規登録はこちら</a>
            </div>
        </form>
    </div>
</body>
</html>