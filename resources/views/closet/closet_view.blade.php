<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $closet_name ?? 'クローゼットA' }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .coord-item { border: 1px solid #ddd; padding: 20px; border-radius: 10px; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .favorite-display { color: #ff4d4d; margin-left: 10px; font-size: 24px; }
        .img-box img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; margin-right: 5px; }
        .item-line { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #f9f9f9; padding-bottom: 5px; }
        .label { width: 100px; font-size: 14px; font-weight: bold; }
        .danger { background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>{{ $closet_name ?? 'クローゼットA' }}</h1>
        <div>
            <a href="{{ route('closet.main') }}">一覧へ</a>
            <a href="/main/calendar">メインへ</a>
        </div>
    </div>

    <div class="container">
        <p><strong>タグ:</strong> {{ $closet_tags ?? 'タグ1, タグ2' }}</p>
        
        <a href="{{ route('closet.edit', ['id' => 1]) }}" class="button">クローゼット編集</a>
        
        <hr>
        
        <div style="margin-bottom: 20px;">
            <a href="{{ route('coord.add') }}" class="button primary">コーデ追加</a>
            <a href="/clothing/add" class="button primary">服追加</a>
        </div>
        
        <hr>

        @foreach($dummy_coords as $coord)
        <div class="coord-item">
            <h3 style="display: flex; align-items: center;">
                {{ $coord['name'] }}
                @if($coord['is_favorite'])
                    <span class="favorite-display">❤</span> 
                @endif
            </h3>

            <p><strong>タグ:</strong> {{ $coord['tags'] }}</p>
            
            <div class="item-line">
                <span class="label" style="color: #c62828;">全体像:</span> 
                <span class="img-box"><img src="{{ asset($coord['img_full']) }}" alt="全体像"></span>
            </div>

            <div class="item-line">
                <span class="label">シャツ:</span> 
                <span class="img-box"><img src="{{ asset($coord['img_shirt']) }}" alt="シャツ"></span>
            </div>

            <div class="item-line">
                <span class="label">パンツ:</span> 
                <span class="img-box"><img src="{{ asset($coord['img_pants']) }}" alt="パンツ"></span>
            </div>

            <div class="item-line">
                <span class="label">シューズ:</span> 
                <span class="img-box"><img src="{{ asset($coord['img_shoes']) }}" alt="シューズ"></span>
            </div>

            @if(isset($coord['img_acc']))
            <div class="item-line">
                <span class="label">アクセ:</span> 
                <span class="img-box"><img src="{{ asset($coord['img_acc']) }}" alt="アクセ"></span>
            </div>
            @endif

            <div style="margin-top: 15px; text-align: right;">
                <button type="button" class="danger">このコーデを削除</button>
            </div>
        </div>
        @endforeach

    </div>
</body>
</html>