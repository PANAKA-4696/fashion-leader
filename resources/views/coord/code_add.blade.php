<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コーデ追加画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>コーデ追加</h1>
        <a href="/closet/view">クローゼットへ戻る</a>
    </div>

    <div class="container">
        <p>クローゼットA の服を使って<strong>「クローゼットA」に</strong>新しいコーデを登録します。</p>
        <p style="font-size: 14px; color: #555;">（コーデの「原型」を登録・管理する場合は「コーデマスター管理」から行います）</p>
        
        <form action="/closet/view" method="POST" id="codeForm" enctype="multipart/form-data">
            @csrf
            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/*" onchange="previewImage(event, 'imagePreviewFull')">
            <div class="preview-box">
                <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
            </div>
            <br>
            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ (Enterで追加)">
            <div id="tagContainer" class="tag-container"></div>
            <br>
            <label>コーデに使う服を選択してください:</label>
            <div class="item-list">
                <label class="item">
                    <input type="checkbox" name="clothing_ids[]" value="7">
                    <!-- 
                    CSSと同様に、画像も public フォルダ内にあるものを asset() で呼び出します。
                    <img src="{{ asset('images/アクセサリー2.jpg') }}" alt="アクセサリー2"> 
                    -->
                    <img src="../assets/images/アクセサリー2.jpg" alt="アクセサリー2">
                    <p>アクセサリー2</p>
                </label>
            </div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>このコーデをお気に入り登録</span>
            </label>
            <input type="submit" value="コーデを登録する" class="primary">
            <a href="../closet/closet_view.html" class="button">キャンセル</a>
        </form>
    </div>

    <script>
        // --- タグ入力機能 (変更なし) ---
        const tagInput = document.getElementById('tag_input');
        /* (中略) */
        function updateHiddenInput() { /* (中略) */ }
        
        // --- プレビュー用JS (変更なし) ---
        function previewImage(event, previewId) { /* (中略) */ }
        
        // ▼▼ お気に入りボタン トグル用JS ▼▼
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            if (this.checked) {
                btn.classList.add('favorited');
                btn.querySelector('span:last-child').textContent = 'お気に入りに登録済み';
            } else {
                btn.classList.remove('favorited');
                btn.querySelector('span:last-child').textContent = 'このコーデをお気に入り登録';
            }
        });
        // ▲▲ お気に入りボタン トグル用JS ▲▲
    </script>
</body>
</html>