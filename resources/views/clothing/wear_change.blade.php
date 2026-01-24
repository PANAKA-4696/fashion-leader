<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>服マスター情報変更</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<style>
    /* 画像表示調整用スタイル (削除画面と同じもの) */
    .img-box {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin-right: 15px;
        overflow: hidden;
    }
    .clothing-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .image-select-item {
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        display: flex;
        align-items: center;
    }
    .image-select-item input[type="radio"] {
        margin-right: 10px;
        transform: scale(1.5);
        cursor: pointer;
    }
    .image-select-item label {
        display: flex;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }
</style>
</head>
<body>
    <div class="header-nav">
        <h1>服マスター情報変更</h1>
        <a href="/clothing/wear-screen" class="back-btn">服管理へ戻る</a>
    </div>

    <div class="container">
        <h2>情報を変更する服を選択してください</h2>
        <p>
            一覧から変更したい服を1つ選択し、「情報変更画面へ」ボタンを押してください。
        </p>
        
        <form id="selectionForm">
            <label for="item_select" style="font-weight: bold; font-size: 18px;">変更する服を選択</label>

            <div class="image-select-list">
                @forelse($clothings as $clothing)
                <div class="image-select-item">
                    <input type="radio" id="item_{{ $clothing->WEAR_ID }}" name="item_id" value="{{ $clothing->WEAR_ID }}" required>
                    
                    <label for="item_{{ $clothing->WEAR_ID }}">
                        <span class="img-box">
                            @if($clothing->IMAGE_PATH)
                                <img class="clothing-img" src="{{ asset('storage/' . $clothing->IMAGE_PATH) }}" alt="{{ $clothing->CATEGORY }}">
                            @else
                                <img class="clothing-img" src="{{ asset('images/no_image.png') }}" alt="画像なし">
                            @endif
                        </span>
                        <span>
                            <strong>{{ $clothing->ITEM_NAME }}</strong><br>
                            <span style="font-size: 0.9em; color: #666;">{{ $clothing->CATEGORY }}</span>

                            {{-- お気に入りはまだDBにないのでコメントアウト
                            @if($clothing->is_favorite)
                                <span class="favorite-display">❤</span>
                            @endif
                            --}}
                        </span>
                    </label>
                </div>
                @empty
                <p>変更する服がありません。</p>
                @endforelse
            </div>
            <br>
            
            @if($clothings->count() > 0)
            <button type="button" onclick="submitForm()" class="button primary">情報変更画面へ</button>
            @endif
            
            <a href="/clothing/wear-screen" class="button">キャンセル</a>

        </form>

        <script>
            function submitForm() {
                // ラジオボタンで選択された値を取得
                const selectedId = document.querySelector('input[name="item_id"]:checked');
                
                if (!selectedId) {
                    alert('変更する服を選択してください。');
                    return;
                }
                
                // 次に作成する「編集画面」へ遷移するURL
                // ルート: /wear-item-change/{ID}
                // 修正後（/clothing を追加）
                window.location.href = '/clothing/wear-item-change/' + selectedId.value;
            }
        </script>
    </div>
</body>
</html>