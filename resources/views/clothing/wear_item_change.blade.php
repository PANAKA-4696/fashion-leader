<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服マスター情報変更 (2: 編集)</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター情報編集</h1>
        <a href="wear-change">選択画面へ戻る</a>
    </div>
    
    <div class="container">
        <p>
            <strong>「{{ $clothing->category }} (ID: {{ $clothing->id }})」</strong>の情報を編集しています。
        </p>
        
        <form action="/clothing/{{ $clothing->id }}" method="post" id="clothingForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <label for="clothing_image">服の画像:</label>
            <input type="file" id="clothing_image" name="clothing_image" accept="image/*" onchange="previewImage(event)">
            <div class="preview-box">
                @if($clothing->image_path)
                    <img id="imagePreview" src="{{ asset('storage/' . $clothing->image_path) }}" alt="{{ $clothing->category }}" class="preview-img" style="display:block;">
                @else
                    <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
                @endif
            </div>
            <br>
            <label for="category">カテゴリ:</label>
            <select id="category" name="category" required>
                <option value="アウター" @if($clothing->category === 'アウター') selected @endif>アウター</option>
                <option value="シャツ" @if($clothing->category === 'シャツ') selected @endif>シャツ</option>
                <option value="ボトムス" @if($clothing->category === 'ボトムス') selected @endif>ボトムス</option>
                <option value="シューズ" @if($clothing->category === 'シューズ') selected @endif>シューズ</option>
                <option value="ソックス" @if($clothing->category === 'ソックス') selected @endif>ソックス</option>
                <option value="その他" @if($clothing->category === 'その他') selected @endif>その他</option>
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
            
            <label class="favorite-toggle-btn @if($clothing->is_favorite) favorited @endif" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite" value="1" @if($clothing->is_favorite) checked @endif>
                <span class="fav-icon">❤</span>
                <span>@if($clothing->is_favorite)お気に入りに登録済み@else この服をお気に入り登録@endif</span>
            </label>
            <input type="submit" value="変更を保存する" class="primary">
            <a href="/clothing/wear-change" class="button">キャンセル</a>
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

        // 初期化時に既存のタグを読み込む
        document.addEventListener('DOMContentLoaded', function() {
            const tagsJSON = '{{ $clothing->tags ?? "[]" }}';
            try {
                const parsedTags = JSON.parse(tagsJSON.replace(/&quot;/g, '"'));
                if (Array.isArray(parsedTags)) {
                    tags = parsedTags;
                    updateTagDisplay();
                    updateHiddenInput();
                }
            } catch(e) {
                console.error('タグのパースエラー:', e);
            }
        });
    </script>
</body>
</html>