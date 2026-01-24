<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>服マスター削除 (選択)</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター削除 (選択)</h1>
        <a href="wear-screen">服管理へ戻る</a>
    </div>

    <div class="container">
        <h2>マスターから削除する服を選択</h2>
        <p style="color: #c62828; font-weight: bold;">
            注意: ここで削除を実行すると、すべてのクローゼットからこの服のデータが完全に削除されます。<br>
            この操作は取り消せません。
        </p>
        
        <form id="selectionForm">
            
            <label for="item_select" style="font-weight: bold; font-size: 18px;">削除する服を選択</label>

            <div class="image-select-list">
                @forelse($clothings as $clothing)
                <div class="image-select-item">
                    <input type="radio" id="item_{{ $clothing->id }}" name="delete_id" value="{{ $clothing->id }}" required>
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
                <p>削除する服がありません。</p>
                @endforelse
            </div>
            <hr>
            @if($clothings->count() > 0)
            <button type="button" onclick="submitDelete()" class="danger">選択した服をマスターから削除</button>
            @endif
            <a href="wear-screen" class="button">キャンセル</a>
        </form>

        <form id="deleteForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        
        <script>
            function submitDelete() {
                const selectedId = document.querySelector('input[name="delete_id"]:checked');
                if (!selectedId) {
                    alert('削除する服を選択してください。');
                    return;
                }
                
                if(confirm('本当にこのアイテムをマスターから削除しますか？')) {
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = '/clothing/' + selectedId.value;
                    deleteForm.submit();
                }
            }
        </script>
    </div>
</body>
</html>