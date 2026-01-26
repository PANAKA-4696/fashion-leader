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
        .img-box img { width: 80px; height: 80px; object-fit: contain; border-radius: 5px; border: 1px solid #eee; margin-right: 5px; background: #fafafa; }
        .item-line { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #f9f9f9; padding-bottom: 5px; }
        .label { width: 100px; font-size: 14px; font-weight: bold; }
        .danger { background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        
        /* ポップアップ */
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
        <h1>{{ $closet->CLOSET_NAME }}</h1>
        <div>
            <a href="{{ route('closet.main') }}">一覧へ</a>
            <a href="/main/calendar">メインへ</a>
        </div>
    </div>

    <div class="container">
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <a href="{{ route('closet.edit', ['id' => $closet->CLOSET_ID]) }}" class="button">
                クローゼット名編集
            </a>
        </div>
        
        <hr>
        
        <div style="margin-bottom: 20px;">
            <a href="{{ route('closet.coord.add', ['closet_id' => $closet->CLOSET_ID]) }}" class="button primary">コーデ追加</a>
        </div>
        
        <hr>

        @forelse($codes as $code)
        <div class="coord-item">
            <h3>{{ $code->CODE_NAME }}</h3>
            
            <p><strong>タグ:</strong> 
                @if(!empty($code->tags_array))
                    @foreach($code->tags_array as $tag)
                        <span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:12px; margin-right:4px;">{{ $tag }}</span>
                    @endforeach
                @else
                    <span style="color:#999; font-size:12px;">なし</span>
                @endif
            </p>

            <div class="item-list" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                @foreach($code->wears as $wear)
                    <div style="text-align: center;">
                        @if($wear->IMAGE_PATH)
                            <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}" style="width:80px; height:80px; object-fit:contain; border:1px solid #eee; border-radius:4px;">
                        @else
                            <img src="{{ asset('images/no_image.png') }}" alt="No Image" style="width:80px; height:80px; object-fit:contain; border:1px solid #eee; border-radius:4px;">
                        @endif
                        <p style="font-size:11px; margin:2px 0 0;">{{ $wear->ITEM_NAME }}</p>
                    </div>
                @endforeach
                
                @if($code->wears->isEmpty())
                    <p style="color:#999;">アイテムなし</p>
                @endif
            </div>

            <div style="margin-top: 15px; text-align: right;">
                <form action="{{ route('closet.coord.delete') }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                    @csrf
                    <input type="hidden" name="code_id" value="{{ $code->CODE_ID }}">
                    <button type="submit" class="danger">削除</button>
                </form>
            </div>
        </div>
        @empty
            <p style="padding: 20px; text-align: center; color: #777;">まだコーデが登録されていません。<br>「コーデ追加」ボタンから登録してください。</p>
        @endforelse

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