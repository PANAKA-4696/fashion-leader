<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>コーデ変更画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* ▼▼ スタイル調整 ▼▼ */
        
        /* コーデ全体を囲むリンクブロック */
        .coord-link-block {
            display: block;             /* ブロック要素にする */
            text-decoration: none;      /* 下線を消す */
            color: inherit;             /* 文字色を継承 */
            border: 1px solid #ddd;     /* 枠線 */
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            transition: all 0.2s ease;  /* アニメーション */
        }

        /* マウスを乗せたときの動き */
        .coord-link-block:hover {
            background-color: #f9f9f9;  /* 背景を少し暗く */
            border-color: #bbb;         /* 枠線を濃く */
            transform: translateY(-2px);/* 少し浮かせる */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* 画像リストのコンテナ */
        .row {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            flex-wrap: wrap; 
            justify-content: flex-start;
            margin-top: 10px;
        }

        figure {
            margin: 0;
            width: 120px; /* 少しコンパクトに */
            text-align: center;
            font-size: 13px;
        }
        
        figure img {
            display: block;
            width: 100%;
            height: 120px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid #eee;
            background-color: #fafafa;
        }
        
        figcaption {
            margin-top: 5px;
            color: #555;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .cat-name {
            font-size: 10px;
            color: #999;
            display: block;
        }

        .coord-header {
            display: flex; 
            align-items: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .coord-title {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
            font-weight: bold;
        }

        .favorite-display {
            color: #e91e63;
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .edit-label {
            margin-left: auto; /* 右寄せ */
            font-size: 0.9rem;
            color: #007bff;
            background-color: #e7f1ff;
            padding: 4px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>コーデ変更 (選択)</h1>
        <a href="{{ route('coord.manage') }}">コーデ管理へ戻る</a>
    </div>

    <div class="container">
        <p>
            変更したいコーデを選択してください。
        </p>

        @forelse($coords as $coord)
            <a href="{{ route('coord.change', ['id' => $coord->CODE_ID]) }}" class="coord-link-block">
                
                <div class="coord-header">
                    <h3 class="coord-title">
                        {{ $coord->CODE_NAME }}
                    </h3>
                    @if($coord->IS_FAVORITE)
                        <span class="favorite-display">❤</span>
                    @endif
                    
                    <span class="edit-label">編集する ></span>
                </div>

                <div class="row">
                    @foreach($coord->wears as $wear)
                        <figure>
                            @if($wear->IMAGE_PATH)
                                <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                            @else
                                <img src="{{ asset('images/no_image.png') }}" alt="No Image">
                            @endif
                            
                            <figcaption>
                                {{ $wear->ITEM_NAME }}
                                <span class="cat-name">{{ $wear->CATEGORY }}</span>
                            </figcaption>
                        </figure>
                    @endforeach

                    @if($coord->wears->isEmpty())
                        <p style="color: #999; font-size: 0.9rem; padding: 10px;">アイテム未登録</p>
                    @endif
                </div>
            </a>
            @empty
            <p style="text-align: center; margin-top: 50px;">
                変更できるコーデ（マスターコーデ）が登録されていません。<br>
                <a href="{{ route('coord.save') }}">新規登録</a> から作成してください。
            </p>
        @endforelse

    </div>
</body>
</html>