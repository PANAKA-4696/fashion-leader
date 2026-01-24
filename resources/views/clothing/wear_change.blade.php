<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>服マスター情報変更 (1: 選択)</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター情報変更 (1: 選択)</h1>
        <a href="wear_screen.html">服管理へ戻る</a>
    </div>

    <div class="container">
        <h2>情報を変更する服を選択してください</h2>
        <p>
            一覧から変更したい服を1つ選択し、「情報変更画面へ」ボタンを押してください。
        </p>
        
        <form action="wear_item_change.html" method="get" style="text-align: left;">
            <label for="item_select" style="font-weight: bold; font-size: 18px;">変更する服を選択</label>

            <div class="image-select-list">
                @forelse($clothings as $clothing)
                <div class="image-select-item">
                    <input type="radio" id="item_{{ $clothing->id }}" name="item_id" value="{{ $clothing->id }}" required>
                    <label for="item_{{ $clothing->id }}">
                        <span class="img-box">
                            @if($clothing->image_path)
                                <img class="clothing-img" src="{{ asset('storage/' . $clothing->image_path) }}" alt="{{ $clothing->category }}">
                            @else
                                <img class="clothing-img" src="" alt="画像なし">
                            @endif
                        </span>
                        <span>
                            {{ $clothing->category }}
                            @if($clothing->is_favorite)
                                <span class="favorite-display">❤</span>
                            @endif
                        </span>
                    </label>
                </div>
                @empty
                <p>変更する服がありません。</p>
                @endforelse
            </div>
            <br>
            @if($clothings->count() > 0)
            <a href="javascript:void(0)" onclick="submitForm()" class="button primary" style="background-color: #dc3545; border-color: #dc3545;">情報変更画面へ</a>
            @endif
            
            <a href="wear-screen" class="button">キャンセル</a>

        </form>

        <script>
            function submitForm() {
                const selectedId = document.querySelector('input[name="item_id"]:checked');
                if (!selectedId) {
                    alert('変更する服を選択してください。');
                    return;
                }
                window.location.href = '/clothing/wear-item-change/' + selectedId.value;
            }
        </script>
    </div>
</body>
</html>