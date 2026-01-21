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
        
        @if ($errors->any())
            <div style="background-color: #ffebee; border: 1px solid #ef5350; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                <p style="color: #c62828; font-weight: bold;">エラーが発生しました：</p>
                <ul style="color: #d32f2f; margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="/clothing/store" method="post" id="clothingForm" enctype="multipart/form-data">
            @csrf
            
            <label for="clothing_image">服の画像:</label>
            <input type="file" id="clothing_image" name="clothing_image" accept="image/*" required onchange="previewImage(event)">
            <div class="preview-box">
                <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
            </div>
            <br>
            <label for="category">カテゴリ:</label>
            <select id="category" name="category" required>
                <option value="アウター">アウター</option>
                <option value="シャツ">シャツ</option>
                <option value="ボトムス">ボトムス</option>
                <option value="シューズ">シューズ</option>
                <option value="ソックス">ソックス</option>
                <option value="その他">その他</option>
            </select>
            <br>
            <label for="tag_input">タグ:</label>
            <input type="text" id="tag_input" placeholder="タグを入力して Enter を押してください">
            <input type="hidden" id="tags" name="tags" value="">
            <div class="preset-tags">
                <button type="button" class="preset-tag" onclick="addTag('フォーマル')">フォーマル</button>
                <button type="button" class="preset-tag" onclick="addTag('カジュアル')">カジュアル</button>
                <button type="button" class="preset-tag" onclick="addTag('春')">春</button>
                <button type="button" class="preset-tag" onclick="addTag('夏')">夏</button>
            </div>
            <p style="margin-bottom: 5px;">追加されたタグ:</p>
            <div id="tagContainer" class="tag-container"></div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite" value="1">
                <span class="fav-icon">❤</span>
                <span>この服をお気に入り登録</span>
            </label>
            <input type="submit" value="マスターに追加する" class="primary">
            
            <a href="javascript:history.back()" class="button">キャンセル</a>
            </form>
    </div>
    
    <script>
        let tags = [];
        
        // --- タグ入力機能 ---
        const tagInput = document.getElementById('tag_input');
        
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag(this.value);
                this.value = '';
            }
        });
        
        function addTag(tag) {
            const trimmedTag = tag.trim();
            if (trimmedTag && !tags.includes(trimmedTag)) {
                tags.push(trimmedTag);
                updateTagDisplay();
                updateHiddenInput();
            }
        }
        
        function removeTag(tag) {
            tags = tags.filter(t => t !== tag);
            updateTagDisplay();
            updateHiddenInput();
        }
        
        function updateTagDisplay() {
            const container = document.getElementById('tagContainer');
            container.innerHTML = '';
            tags.forEach(tag => {
                const tagEl = document.createElement('span');
                tagEl.className = 'tag';
                tagEl.innerHTML = `${tag} <button type="button" onclick="removeTag('${tag}')">×</button>`;
                container.appendChild(tagEl);
            });
        }
        
        function updateHiddenInput() {
            document.getElementById('tags').value = tags.join(',');
        }

        // --- 画像プレビュー機能 ---
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
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