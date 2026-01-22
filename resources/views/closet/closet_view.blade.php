<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $closet->CLOSET_NAME }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* --- 既存のスタイル --- */
        .coord-item { border: 1px solid #ddd; padding: 20px; border-radius: 10px; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .favorite-display { color: #ff4d4d; margin-left: 10px; font-size: 24px; }
        .img-box img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; margin-right: 5px; }
        .item-line { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #f9f9f9; padding-bottom: 5px; }
        .label { width: 100px; font-size: 14px; font-weight: bold; }
        .danger { background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }

        /* --- ★追加：ポップアップ用のスタイル --- */
        .flash-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50; /* 緑色 */
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
        <h1>{{ $closet->CLOSET_NAME }}</h1>
        <div>
            <a href="{{ route('closet.main') }}">一覧へ</a>
            <a href="/main/calendar">メインへ</a>
        </div>
    </div>

    <div class="container">
        <p><strong>タグ:</strong> {{ $closet_tags ?? 'タグ1, タグ2' }}</p>
        
        <a href="{{ route('closet.edit', ['id' => $closet->CLOSET_ID]) }}" class="button">
            クローゼット編集
        </a>
        
        <hr>
        
        <div style="margin-bottom: 20px;">
            <a href="{{ route('closet.coord.add', ['closet_id' => $closet->CLOSET_ID]) }}" class="button primary">コーデ追加</a>
            <a href="/clothing/add" class="button primary">服追加</a>
        </div>
        
        <hr>

        @foreach($codes as $code)
        <div class="coord-item">
            <h3>{{ $code->CODE_NAME }}</h3>
            <p><strong>タグ:</strong> 
                @foreach($code->tags as $tag) {{ $tag->TAG_NAME }}@if(!$loop->last), @endif @endforeach
            </p>

            @foreach(['shirt' => 'シャツ', 'pants' => 'パンツ', 'shoes' => 'シューズ'] as $cat => $label)
                @php $item = $code->wears->where('CATEGORY', $cat)->first(); @endphp
                <div class="item-line">
                    <span class="label">{{ $label }}:</span>
                    <span class="img-box">
                        @if($item)
                            <img src="{{ asset('storage/' . $item->IMAGE_PATH) }}" alt="{{ $label }}">
                        @else
                            <span style="font-size:10px; color:#ccc;">未登録</span>
                        @endif
                    </span>
                </div>
            @endforeach

            <form action="{{ route('closet.coord.delete') }}" method="POST">
                @csrf
                <input type="hidden" name="code_id" value="{{ $code->CODE_ID }}">
                <button type="submit" class="danger">このコーデを削除</button>
            </form>
        </div>
        @endforeach

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('flash-popup');
            if (popup) {
                // 1. ふわっと表示
                setTimeout(() => {
                    popup.classList.add('show');
                }, 100);

                // 2. 3秒後に消える
                setTimeout(() => {
                    popup.classList.remove('show');
                }, 3000);
            }
        });
    </script>
</body>
</html>