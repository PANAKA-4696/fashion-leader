<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $date }} のコーデ詳細</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* 全体画像のスタイル */
        .coord-main-img {
            width: 100%;
            max-width: 400px; /* 巨大になりすぎないように制限 */
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* 画像がない場合のメッセージボックス */
        .no-image-box {
            background-color: #f0f0f0;
            color: #666;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px dashed #ccc;
        }

        /* 使った服リストの簡易スタイル */
        .used-item-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .used-item {
            width: 80px;
            text-align: center;
        }
        .used-item img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>{{ $date }} のコーデ</h1>
        <a href="/main/calendar" class="back-btn">カレンダーへ戻る</a>
    </div>

    <div class="container">
        
        @if(isset($code) && $code->IMAGE_PATH)
            <img src="{{ asset('storage/' . $code->IMAGE_PATH) }}" alt="今日のコーデ" class="coord-main-img">
        @else
            <div class="no-image-box">
                <p>全体画像がありません。</p>
            </div>
        @endif
        @if(count($wears) > 0)
            <h3>着用アイテム</h3>
            <div class="used-item-list">
                @foreach($wears as $wear)
                    <div class="used-item">
                        @if($wear->IMAGE_PATH)
                            <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                        @else
                            <img src="{{ asset('images/no_image.png') }}" alt="No Image">
                        @endif
                        <p style="font-size: 10px; margin:0;">{{ $wear->ITEM_NAME }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <hr>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/main/closet_edit?date={{ $date }}" class="button primary">
                コーデを編集・登録する
            </a>
        </div>
        
    </div>
</body>
</html>