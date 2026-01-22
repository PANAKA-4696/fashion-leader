<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録画面</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container small-container">
        <h2>新規登録</h2>
        
        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px; font-size: 0.9em;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            
            <label for="username">ユーザー名</label>
            <input type="text" id="username" name="user_name" placeholder="ユーザー名を入力" value="{{ old('user_name') }}" required>

            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" placeholder="example@mail.com" value="{{ old('email') }}" required>

            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" placeholder="8文字以上12文字未満" required>
            <p style="font-size:12px; color:#666; margin-top:-5px; margin-bottom:15px;">※8文字以上12文字未満で入力してください</p>

            <button type="submit" class="button primary full">登録する</button>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="button-link">ログイン画面へ戻る</a>
            </div>
        </form>
    </div>
</body>
</html>