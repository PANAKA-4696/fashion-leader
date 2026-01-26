<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>マスターコーデ登録</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .item-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
        .item {
            width: 100px;
            text-align: center;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 4px;
            padding: 5px;
            transition: 0.2s;
            position: relative;
        }
        .item:has(input:checked) {
            border-color: #d32f2f;
            background-color: #ffebee;
        }
        .item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f5f5f5;
        }
        .item p {
            font-size: 12px;
            margin: 5px 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item input[type="checkbox"] {
            position: absolute;
            top: 5px;
            left: 5px;
            transform: scale(1.2);
        }
        /* 入力フォーム周りのスタイル調整 */
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            margin-top: 15px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>マスターコーデ登録</h1>
        <a href="{{ route('coord.manage') }}">管理メニューへ戻る</a>
    </div>

    <div class="container">
        <p>よく使うコーデを「マスター」として登録します。<br>（日付には紐付きません）</p>

        <form action="{{ route('coord.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/*" onchange="previewImage(event)">
            
            <div class="preview-box" style="margin-top: 10px;">
                <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display: none; max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <label for="code_name">コーデ名 (任意):</label>
            <input type="text" id="code_name" name="code_name" placeholder="例：お気に入りデート服、面接用スーツなど">
            <p style="font-size: 0.8rem; color: #666; margin-top: 2px;">※空欄の場合、タグ名または登録日が名前になります。</p>

            <label for="tag_input">タグ:</label>
            <input type="text" id="tag_input" placeholder="例：春, デート (Enterで追加)">
            <input type="hidden" id="tags" name="tags" value="">
            
            <div id="tagContainer" class="tag-container"></div>

            <label for="wear_search">登録する服を選択:</label>
            <input type="text" id="wear_search" placeholder="カテゴリや名前で検索..." onkeyup="filterClothes()">
            
            <div class="item-list" id="clothesList">
                @forelse($wears as $wear)
                <label class="item" data-category="{{ $wear->CATEGORY }}" data-name="{{ $wear->ITEM_NAME }}">
                    <input type="checkbox" name="clothing_ids[]" value="{{ $wear->WEAR_ID }}">
                    
                    @if($wear->IMAGE_PATH)
                        <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                    @else
                        <img src="{{ asset('images/no_image.png') }}" alt="画像なし">
                    @endif
                    
                    <p>{{ $wear->ITEM_NAME }}</p>
                    <span style="font-size:10px; color:#666;">{{ $wear->CATEGORY }}</span>
                </label>
                @empty
                    <p style="padding: 20px;">登録された服がありません。</p>
                @endforelse
            </div>

            <hr>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>お気に入りに登録する</span>
            </label>

            <div style="margin-top: 20px; text-align: center;">
                <input type="submit" value="マスターコーデを保存" class="primary" style="width: 100%; max-width: 300px;">
                <br>
                <a href="{{ route('coord.manage') }}" class="button" style="display: inline-block; margin-top: 10px; background-color: #ccc; color: #333; text-decoration: none; width: 100%; max-width: 300px; box-sizing: border-box; text-align: center;">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // 画像プレビュー
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        // --- タグ機能 ---
        let tags = [];
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
                tagEl.innerHTML = `${tag} <span class="tag-delete" onclick="removeTag('${tag}')">×</span>`;
                container.appendChild(tagEl);
            });
        }
        
        function updateHiddenInput() {
            document.getElementById('tags').value = tags.join(',');
        }

        // 服検索フィルタ
        function filterClothes() {
            const query = document.getElementById('wear_search').value.toLowerCase();
            const items = document.querySelectorAll('#clothesList .item');
            
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const category = item.getAttribute('data-category').toLowerCase();
                if (name.includes(query) || category.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // お気に入りトグル表示切替
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            if (this.checked) {
                btn.classList.add('favorited');
            } else {
                btn.classList.remove('favorited');
            }
        });

        // 検索ボックスでのEnter誤送信防止
        document.getElementById('wear_search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        // ▼▼ 追加: コーデ名入力でのEnter誤送信防止 ▼▼
        const codeNameInput = document.getElementById('code_name');
        if (codeNameInput) {
            codeNameInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // 送信をキャンセル
                    return false;
                }
            });
        }
    </script>
</body>
</html>