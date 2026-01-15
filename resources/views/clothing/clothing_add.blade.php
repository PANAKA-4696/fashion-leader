<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服マスター登録</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター登録</h1>
        <a href="javascript:history.back()" style="color: white; text-decoration: none;">戻る</a>
    </div>
    
    <div class="container">
        <p>新しい服（マスターデータ）をアプリに登録します。</p>
        
        <form action="wear-screen" method="post" id="clothingForm" enctype="multipart/form-data">
            
            <label for="clothing_image">服の画像:</label>
            <input type="file" id="clothing_image" name="clothing_image" accept="image/*" required onchange="previewImage(event)">
            <div class="preview-box">
                <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
            </div>
            <br>
            <label for="category">カテゴリ:</label>
            <select id="category" name="category" required>
                <option value="other">その他</option>
            </select>
            <br>
            <label for="tag_input">タグ:</label>
            <input type="text" id="tag_input" placeholder="タグを入力して Enter を押してください">
            <input type="hidden" id="tags" name="tags" value="">
            <div class="preset-tags">
                <button type="button" class="preset-tag" onclick="addTag('フォーマル')">フォーマル</button>
            </div>
            <p style="margin-bottom: 5px;">追加されたタグ:</p>
            <div id="tagContainer" class="tag-container"></div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>この服をお気に入り登録</span>
            </label>
            <input type="submit" value="マスターに追加する" class="primary">
            
            <a href="javascript:history.back()" class="button">キャンセル</a>
            </form>
    </div>
    
    <script>
        // --- タグ入力機能 (変更なし) ---
        const tagInput = document.getElementById('tag_input');
        /* (中略) */
        function updateHiddenInput() { /* (中略) */ }

        // --- 画像プレビュー機能 (変更なし) ---
        function previewImage(event) { /* (中略) */ }
        
        // ▼▼ お気に入りボタン トグル用JS ▼▼
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            if (this.checked) {
                btn.classList.add('favorited');
                btn.querySelector('span:last-child').textContent = 'お気に入りに登録済み';
            } else {
                btn.classList.remove('favorited');
                btn.querySelector('span:last-child').textContent = 'この服をお気に入り登録';
            }
        });
        // ▲▲ お気に入りボタン トグル用JS ▲▲
    </script>
</body>
</html>