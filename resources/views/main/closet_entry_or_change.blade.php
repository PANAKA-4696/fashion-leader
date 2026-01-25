<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>今日のコーデ 登録/変更</title>
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
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>今日のコーデ 登録/変更</h1>
        <a href="/main/closet_clothes?date={{ $date }}">今日のコーデ確認画面へ</a>
    </div>

    <div class="container">
        <h2>{{ $date }} のコーデ</h2>
        <p style="font-size: 14px; color: #555;">
            @if($existingCoord)
                登録済みの内容を編集できます。
            @else
                新しくコーデを登録します。
            @endif
        </p>
        
        <form action="/main/closet_edit" method="POST" enctype="multipart/form-data" id="closetForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/*" onchange="previewImage(event)">
            <div class="preview-box">
                @if(isset($existingCoord) && $existingCoord->IMAGE_PATH)
                    <img id="imagePreview" src="{{ asset('storage/' . $existingCoord->IMAGE_PATH) }}" alt="登録済み画像" class="preview-img" style="display:block;">
                @else
                    <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
                @endif
            </div>
            
            <br>

            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ(Enterで追加)">
            <input type="hidden" id="tags" name="tags" value="">
            
            <div id="tagContainer" class="tag-container"></div>

            <br>

            <label for="wear_search">コーデに使う服を選択してください:</label>
            <input type="text" id="wear_search" placeholder="カテゴリや名前で検索..." onkeyup="filterClothes()">
            
            <div class="item-list" id="clothesList">
                @forelse($wears as $wear)
                <label class="item" data-category="{{ $wear->CATEGORY }}" data-name="{{ $wear->ITEM_NAME }}">
                    <input type="checkbox" name="clothing_ids[]" value="{{ $wear->WEAR_ID }}"
                        @if(in_array($wear->WEAR_ID, $selectedWearIds)) checked @endif
                    >
                    
                    @if($wear->IMAGE_PATH)
                        <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                    @else
                        <img src="{{ asset('images/no_image.png') }}" alt="画像なし">
                    @endif
                    
                    <p>{{ $wear->ITEM_NAME }}</p>
                    <span style="font-size:10px; color:#666;">{{ $wear->CATEGORY }}</span>
                </label>
                @empty
                    <p style="padding: 20px;">登録された服がありません。<br><a href="/clothing/clothing-add">服を追加</a>してください。</p>
                @endforelse
            </div>

            <hr>

            <label class="favorite-toggle-btn @if(isset($existingCoord) && $existingCoord->IS_FAVORITE) favorited @endif" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite" 
                    @if(isset($existingCoord) && $existingCoord->IS_FAVORITE) checked @endif
                >
                <span class="fav-icon">❤</span>
                <span>@if(isset($existingCoord) && $existingCoord->IS_FAVORITE) お気に入りに登録済み @else 今日のコーデをお気に入り登録 @endif</span>
            </label>

            <div style="margin-top: 20px;">
                <input type="submit" value="コーデを登録/更新する" class="primary">
                <a href="/main/calendar" class="button">キャンセル</a>
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
        
        // ★修正: サーバーから既存のタグを受け取って初期化
        const existingTagsJson = @json(isset($existingCoord) && $existingCoord->TAGS ? $existingCoord->TAGS : '[]');
        
        try {
            // DBから取ったJSON文字列をパースして配列にする
            // すでに配列で来ている場合と文字列の場合を考慮
            const parsed = typeof existingTagsJson === 'string' ? JSON.parse(existingTagsJson) : existingTagsJson;
            if (Array.isArray(parsed)) {
                tags = parsed;
            }
        } catch (e) {
            console.log('Tag parse error', e);
        }

        const tagInput = document.getElementById('tag_input');
        
        // 初期表示更新
        updateTagDisplay();
        updateHiddenInput();

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

        // 服の簡易検索
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

        // お気に入りトグル
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            const span = btn.querySelector('span:last-child');
            if (this.checked) {
                btn.classList.add('favorited');
                span.textContent = 'お気に入りに登録済み';
            } else {
                btn.classList.remove('favorited');
                span.textContent = '今日のコーデをお気に入り登録';
            }
        });

        // ▼▼ 追加: 服検索ボックスでのEnterキーによる誤送信（保存）を防止 ▼▼
        document.getElementById('wear_search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // 何もしない（送信を止める）
                return false;
            }
        });
    </script>
</body>
</html>