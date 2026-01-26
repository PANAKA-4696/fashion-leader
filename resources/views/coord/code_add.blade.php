<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コーデ追加画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* タブのスタイル */
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .tab-btn {
            background: none; border: none; padding: 10px 20px; cursor: pointer;
            font-size: 16px; font-weight: bold; color: #999; border-radius: 5px;
        }
        .tab-btn.active { background-color: #333; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* 既存のスタイル */
        .search-box { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .tag-item { background: #eee; padding: 2px 8px; border-radius: 12px; margin-right: 5px; font-size: 13px; }
        .tag-container { margin-top:5px; min-height:20px; display:flex; flex-wrap:wrap; gap:5px; }

        /* アイテムリスト（服・マスターコーデ共通） */
        .item-list {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;
        }
        .item {
            border: 1px solid #ddd; border-radius: 5px; padding: 5px; text-align: center;
            cursor: pointer; position: relative; background: #fff;
        }
        .item img {
            width: 100%; height: 100px; object-fit: cover; border-radius: 4px;
        }
        .item p {
            font-size: 12px; margin: 5px 0 0 0; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        }
        .item input[type="checkbox"], .item input[type="radio"] {
            position: absolute; top: 5px; left: 5px; transform: scale(1.2);
        }
        
        /* マスターコーデ選択時のスタイル */
        .master-item.selected { border: 2px solid #4CAF50; background-color: #e8f5e9; }

        /* お気に入りボタン */
        .favorite-toggle-btn {
            display: inline-flex; align-items: center; cursor: pointer; padding: 8px 12px;
            border: 1px solid #ccc; border-radius: 4px; background: #fff; transition: all 0.2s;
            user-select: none; margin-top: 20px;
        }
        .favorite-toggle-btn input { display: none; }
        .favorite-toggle-btn .fav-icon { margin-right: 5px; color: #ccc; font-size: 18px; }
        .favorite-toggle-btn.favorited { border-color: #ff4d4d; background: #fff5f5; color: #ff4d4d; }
        .favorite-toggle-btn.favorited .fav-icon { color: #ff4d4d; }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>コーデ追加</h1>
        <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}">クローゼットへ戻る</a>
    </div>

    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('create')">新しく作る</button>
            <button class="tab-btn" onclick="switchTab('select')">マスターから選ぶ</button>
        </div>

        <div id="tab-create" class="tab-content active">
            <p>{{ $closet->CLOSET_NAME }} の服を使って新しいコーデを登録します。</p>
            
            <form action="{{ route('closet.code.store') }}" method="POST" id="codeForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="mode" value="create"> <input type="hidden" name="closet_id" value="{{ $closet->CLOSET_ID }}">
                <input type="hidden" name="tags_data" id="tags_data">

                <label>コーデ名 (任意):</label>
                <input type="text" name="code_name" placeholder="例：お気に入りの春服" class="search-box">

                <label for="coord_image">全体写真 (任意):</label>
                <input type="file" id="coord_image" name="coord_image" accept="image/png, image/jpeg" onchange="previewImage(event, 'imagePreviewFull')">
                <div class="preview-box">
                    <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none; max-width: 200px; margin-top:10px;">
                </div>
                <br>

                <label for="tag_input">コーデのタグ:</label>
                <input type="text" id="tag_input" placeholder="例：春のデートコーデ (Enterで追加)" class="search-box">
                <div id="tagContainer" class="tag-container"></div>
                <br>

                <label>コーデに使う服を選択してください:</label>
                <input type="text" id="wear_search" class="search-box" placeholder="服の名前を検索..." onkeyup="filterClothes()">

                <div class="item-list" id="clothesList">
                    @forelse($wears as $wear)
                        <label class="item" data-category="{{ $wear->CATEGORY }}" data-name="{{ $wear->ITEM_NAME }}">
                            <input type="checkbox" name="clothing_ids[]" value="{{ $wear->WEAR_ID }}" data-category="{{ $wear->CATEGORY }}">
                            @if($wear->IMAGE_PATH)
                                <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->ITEM_NAME }}">
                            @else
                                <div style="height:100px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:10px;">No Image</div>
                            @endif
                            <p>{{ $wear->ITEM_NAME }}</p>
                        </label>
                    @empty
                        <p>服が登録されていません。</p>
                    @endforelse
                </div>

                <label class="favorite-toggle-btn" for="favorite-toggle-create">
                    <input type="checkbox" id="favorite-toggle-create" name="is_favorite">
                    <span class="fav-icon">❤</span>
                    <span>このコーデをお気に入り登録</span>
                </label>

                <div style="margin-top:20px;">
                    <input type="submit" value="新規作成して追加" class="button primary">
                </div>
            </form>
        </div>

        <div id="tab-select" class="tab-content">
            <p>登録済みのマスターコーデから選んで追加します。</p>

            <form action="{{ route('closet.code.store') }}" method="POST">
                @csrf
                <input type="hidden" name="mode" value="select"> <input type="hidden" name="closet_id" value="{{ $closet->CLOSET_ID }}">

                <input type="text" id="master_search" class="search-box" placeholder="コーデ名を検索..." onkeyup="filterMaster()">

                <div class="item-list" id="masterList">
                    @forelse($masterCoords as $coord)
                        <label class="item master-item" data-name="{{ $coord->CODE_NAME }}" onclick="selectMaster(this)">
                            <input type="radio" name="existing_code_id" value="{{ $coord->CODE_ID }}" required>
                            @if($coord->IMAGE_PATH)
                                <img src="{{ asset('storage/' . $coord->IMAGE_PATH) }}" alt="{{ $coord->CODE_NAME }}">
                            @else
                                <div style="height:100px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:10px;">No Image</div>
                            @endif
                            <p>{{ $coord->CODE_NAME }}</p>
                        </label>
                    @empty
                        <p style="grid-column: 1 / -1;">登録済みのコーデがありません。</p>
                    @endforelse
                </div>

                <div style="margin-top:20px;">
                    <input type="submit" value="選択したコーデを追加" class="button primary">
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- タブ切り替え ---
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            document.getElementById('tab-' + tabName).classList.add('active');
            // ボタンのactive切り替えは簡易的に実装（順番依存）
            const btns = document.querySelectorAll('.tab-btn');
            if(tabName === 'create') { btns[0].classList.add('active'); }
            else { btns[1].classList.add('active'); }
        }

        // --- マスターコーデ検索 (JSフィルタ) ---
        function filterMaster() {
            const query = document.getElementById('master_search').value.toLowerCase();
            const items = document.querySelectorAll('#masterList .item');
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(query) ? 'block' : 'none';
            });
        }

        // --- マスター選択時のスタイル変更 ---
        function selectMaster(label) {
            document.querySelectorAll('.master-item').forEach(el => el.classList.remove('selected'));
            label.classList.add('selected');
        }

        // --- (以下、既存のJSロジック) ---
        function previewImage(event, previewId) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById(previewId);
                output.src = reader.result;
                output.style.display = 'block';
            };
            if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
        }

        const tagInput = document.getElementById('tag_input');
        const tagContainer = document.getElementById('tagContainer');
        const tagsDataHidden = document.getElementById('tags_data');
        let tagsArray = [];

        tagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.trim();
                if (val && !tagsArray.includes(val)) {
                    tagsArray.push(val);
                    const tag = document.createElement('span');
                    tag.className = 'tag-item';
                    tag.innerHTML = `${val} <span class="tag-delete" onclick="removeTag('${val}', this)" style="cursor:pointer; margin-left:5px; color:red;">×</span>`;
                    tagContainer.appendChild(tag);
                    tagsDataHidden.value = tagsArray.join(',');
                }
                this.value = '';
            }
        });

        window.removeTag = function(text, btn) {
            tagsArray = tagsArray.filter(t => t !== text);
            btn.parentElement.remove();
            tagsDataHidden.value = tagsArray.join(',');
        }

        function filterClothes() {
            const query = document.getElementById('wear_search').value.toLowerCase();
            const items = document.querySelectorAll('#clothesList .item');
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(query) ? 'block' : 'none';
            });
        }

        const checkboxes = document.querySelectorAll('input[name="clothing_ids[]"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const category = this.dataset.category;
                if (category !== 'accessory' && this.checked) {
                    checkboxes.forEach(other => {
                        if (other !== this && other.dataset.category === category) {
                            other.checked = false;
                        }
                    });
                }
            });
        });

        document.getElementById('favorite-toggle-create').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            const label = btn.querySelector('span:last-child');
            this.checked ? btn.classList.add('favorited') : btn.classList.remove('favorited');
            label.textContent = this.checked ? 'お気に入りに登録済み' : 'このコーデをお気に入り登録';
        });
    </script>
</body>
</html>