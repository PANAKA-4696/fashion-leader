<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クローゼット一覧</title>
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
            background-color: #fff;
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .closet-item:hover { transform: translateY(-2px); border-color: #ddd; background-color: #fcfcfc; }
        .closet-header { font-size: 20px; font-weight: bold; color: #333; display: flex; align-items: center; }
        .favorite-display { color: #ff4d4d; margin-left: 10px; }
        .closet-tags { margin: 8px 0 0 0; color: #666; font-size: 14px; }
        
        .flash-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease-in-out, visibility 0.5s;
        }
        .flash-message.show {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    @if(session('success'))
        <div id="flash-popup" class="flash-message">
            {{ session('success') }}
        </div>
    @endif

    <div class="header-nav">
        <h1>クローゼット一覧</h1>
        <a href="/main/calendar">カレンダーへ</a>
    </div>

    <div class="container">
        <h2>クローゼットを選択してください</h2>

        <div class="closet-list">
            @forelse($closets as $closet)
                <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}" class="closet-item">
                    <div class="closet-header">
                        <span style="margin-right: 8px;">📁</span>
                        {{ $closet->CLOSET_NAME }}
                        @if($closet->is_favorite)
                            <span class="favorite-display">❤</span>
                        @endif
                    </div>
                    <p class="closet-tags">タグ: {{ $closet->tag_string }}</p>
                </a>
            @empty
                <div style="text-align: center; padding: 40px; color: #777;">
                    <p>クローゼットがありません。</p>
                </div>
            @endforelse
        </div>

        <hr>
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('closet.add') }}" class="button primary">新しいクローゼットを作る</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('flash-popup');
            if (popup) {
                setTimeout(() => { popup.classList.add('show'); }, 100);
                setTimeout(() => { popup.classList.remove('show'); }, 3000);
            }
        });
    </script>
</body>
</html>