<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>今日のコーデ詳細</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .coord-img-large {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 8px;
            border: 1px solid #ddd;
            display: block;
            margin: 0 auto 20px;
        }
        .wear-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        .wear-item {
            text-align: center;
            width: 90px;
        }
        .wear-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border: 1px solid #eee;
            background: #fafafa;
            border-radius: 4px;
        }
        .wear-item p {
            font-size: 11px;
            margin-top: 5px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>{{ $date }} のコーデ</h1>
        <a href="/main/calendar" class="back-btn">カレンダーへ戻る</a>
    </div>

    <div class="container">
        @if($code)
            @if($code->IMAGE_PATH)
                <img src="{{ asset('storage/' . $code->IMAGE_PATH) }}" alt="今日のコーデ" class="coord-img-large">
            @else
                <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin-bottom: 20px; color: #888;">
                    No Image
                </div>
            @endif

            <h2 style="text-align: center; font-size: 1.2rem; margin-bottom: 20px;">
                {{ $code->CODE_NAME }}
            </h2>

            <h3 style="text-align: center; font-size: 1rem; color: #555; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px;">
                着用アイテム
            </h3>
            
            <div class="wear-list">
                @forelse($wears as $wear)
                    <div class="wear-item">
                        @if($wear->IMAGE_PATH)
                            <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                        @else
                            <img src="{{ asset('images/no_image.png') }}" alt="No Image">
                        @endif
                        <p>{{ $wear->ITEM_NAME }}</p>
                    </div>
                @empty
                    <p style="color: #999;">服データがありません</p>
                @endforelse
            </div>

            <div class="action-buttons">
                <form action="/main/closet_edit" method="POST" style="width: 100%;">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button primary" style="width: 100%;">
                        内容を変更する
                    </button>
                </form>

                <form action="/main/coord_delete" method="POST" style="width: 100%;" onsubmit="return confirm('本当にこの日のコーデを削除しますか？\n（登録したデータは完全に消去されます）');">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button danger" style="width: 100%;">
                        このコーデを削除する
                    </button>
                </form>
            </div>

        @else
            <p style="text-align: center; margin-top: 50px;">
                この日のコーデはまだ登録されていません。
            </p>
            <div style="text-align: center; margin-top: 20px;">
                <form action="/main/closet_edit" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button primary">
                        コーデを登録する
                    </button>
                </form>
            </div>
        @endif
    </div>
</body>
</html>