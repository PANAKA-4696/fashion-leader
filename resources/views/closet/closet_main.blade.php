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
            @foreach($closets as $closet)
            <li class="closet-item">
                <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}" class="closet-name">
                    {{ $closet->CLOSET_NAME }}
                    @if($closet->is_favorite)
                        <span class="favorite-display">❤</span>
                    @endif
                </a>
                <p class="closet-tags">タグ: {{ $closet->tag_string }}</p>
            </li>
            @endforeach
        </ul>

        <hr>
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('closet.add') }}" class="button primary">新しいクローゼットを作る</a>
        </div>
    </div>
</body>
</html>