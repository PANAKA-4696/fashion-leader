<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クローゼット画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .closet-list { list-style: none; padding-left: 0; }
        .closet-item { 
            margin-bottom: 15px; 
            border: 1px solid #eee; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .closet-item:hover { transform: translateY(-2px); border-color: #ddd; }
        .closet-name { font-size: 20px; font-weight: bold; text-decoration: none; color: #333; display: flex; align-items: center; }
        .favorite-display { color: #ff4d4d; margin-left: 10px; }
        .tag-display { margin: 8px 0 0 0; color: #666; font-size: 14px; }
        .tag-badge { background: #f0f0f0; padding: 2px 8px; border-radius: 4px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>クローゼット一覧</h1>
        <a href="/main/calendar">カレンダーへ</a>
    </div>

    <div class="container">
        <h2>クローゼットを選択してください</h2>

        <ul class="closet-list">
            
            <li class="closet-item">
                <a href="/closet/view/1" class="closet-name">
                    クローゼットA
                    <span class="favorite-display">❤</span>
                </a>
                <p class="tag-display">
                    タグ: <span class="tag-badge">仕事用</span> <span class="tag-badge">お気に入り</span>
                </p>
            </li>

            <li class="closet-item">
                <a href="/closet/view/2" class="closet-name">クローゼットB</a>
                <p class="tag-display">タグ: <span class="tag-badge">デート</span></p>
            </li>

            <li class="closet-item">
                <a href="/closet/view/3" class="closet-name">
                    クローゼットC
                    <span class="favorite-display">❤</span>
                </a>
                <p class="tag-display">タグ: <span class="tag-badge">ルームウェア</span></p>
            </li>

        </ul>

        <hr>
        <div style="text-align: center; margin-top: 20px;">
            <a href="/closet/add" class="button primary" style="padding: 12px 40px;">クローゼット追加</a>
        </div>
    </div>
</body>
</html>