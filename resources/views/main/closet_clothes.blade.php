<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>今日のコーデ詳細</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* この画面特有のスタイル調整 */
        .container {
            padding: 20px;
            max-width: 800px; /* コンテンツ幅を少し広めに */
            margin: 0 auto;
        }
        .coord-image {
            width: 100%;
            max-width: 400px; /* 画像が大きくなりすぎないように */
            height: auto;
            border-radius: 8px;
            border: 1px solid #eee;
            display: block;
            margin: 0 auto 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .coord-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.4rem;
            color: #333;
        }
        
        /* 着用アイテムリスト */
        .wear-section h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #555;
            text-align: center;
        }
        .wear-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        .wear-list li {
            width: 100px;
            text-align: center;
        }
        .wear-list img {
            width: 100%;
            height: 100px;
            object-fit: contain;
            border: 1px solid #eee;
            background: #fff;
            border-radius: 8px;
            padding: 5px;
        }
        .wear-list p {
            font-size: 13px;
            margin-top: 8px;
            color: #333;
            word-break: break-all;
        }

        /* ▼▼ ボタンエリアの修正：横並びにする ▼▼ */
        .action-buttons {
            display: flex;
            flex-direction: row;      /* 横並び */
            flex-wrap: wrap;          /* 画面が狭いときは折り返す */
            gap: 10px;                /* ボタン間の隙間 */
            margin-top: 40px;
            justify-content: center;  /* 中央寄せ */
        }
        
        /* 各ボタンの幅を均等に広げる設定 */
        .action-buttons form {
            flex: 1;              /* 均等にスペースを取る */
            min-width: 140px;     /* 最低でもこれくらいの幅は確保 */
        }
        
        .action-buttons .button {
            width: 100%;          /* 親要素いっぱいに広げる */
            padding: 12px 5px;    /* 上下12px, 左右5px */
            font-size: 14px;
            text-align: center;
            box-sizing: border-box;
            display: inline-block; /* リンクボタン用 */
        }

        /* リンクボタン（キャンセル）もフォームと同じ扱いにするための調整 */
        .cancel-btn-wrapper {
            flex: 1;
            min-width: 140px;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>{{ $date }} のコーデ</h1>
        <a href="/main/calendar">カレンダーへ</a>
    </div>

    <div class="container">
        @if($code)
            @if($code->IMAGE_PATH)
                <img src="{{ asset('storage/' . $code->IMAGE_PATH) }}" alt="今日のコーデ" class="coord-image">
            @else
                <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin-bottom: 20px; color: #999;">
                    画像は登録されていません
                </div>
            @endif

            <h2 class="coord-title">{{ $code->CODE_NAME }}</h2>

            <div class="wear-section">
                <h3>着用アイテム</h3>
                <ul class="wear-list">
                    @forelse($wears as $wear)
                        <li>
                            @if($wear->IMAGE_PATH)
                                <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                            @else
                                <img src="{{ asset('images/no_image.png') }}" alt="No Image">
                            @endif
                            <p>{{ $wear->ITEM_NAME }}</p>
                        </li>
                    @empty
                        <li style="width: 100%; text-align: center; color: #999;">アイテム情報がありません</li>
                    @endforelse
                </ul>
            </div>

            <div class="action-buttons">
                
                <form action="/main/closet_edit" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button primary">変更する</button>
                </form>

                <form action="/main/coord_delete" method="POST" onsubmit="return confirm('本当にこの日のコーデを削除しますか？\n（データは完全に消去されます）');">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button danger">削除する</button>
                </form>

                <div class="cancel-btn-wrapper">
                    <a href="/main/calendar" class="button" style="background-color: #ccc; color: #333; text-decoration: none;">戻る</a>
                </div>
            </div>

        @else
            <p style="text-align: center; margin-top: 60px; color: #777; font-size: 1.1rem;">
                この日のコーデはまだ登録されていません。
            </p>
            <div class="action-buttons">
                <form action="/main/closet_edit" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="button primary">コーデを登録</button>
                </form>
                
                <div class="cancel-btn-wrapper">
                    <a href="/main/calendar" class="button" style="background-color: #ccc; color: #333; text-decoration: none;">カレンダーへ戻る</a>
                </div>
            </div>
        @endif
    </div>
</body>
</html>