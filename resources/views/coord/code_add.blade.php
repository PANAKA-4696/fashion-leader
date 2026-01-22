<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コーデ追加画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* レイアウトを崩さない範囲での調整 */
        .search-box { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .tag-item { background: #eee; padding: 2px 8px; border-radius: 12px; margin-right: 5px; font-size: 13px; }
        
        /* 選択しやすいようにアイテムのスタイル調整 */
        .item-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        .item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            text-align: center;
            cursor: pointer;
            position: relative;
        }
        .item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .item p {
            font-size: 12px;
            margin: 5px 0 0 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        /* チェックボックスを目立たせる */
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
        <h1>コーデ追加</h1>
        <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}">クローゼットへ戻る</a>
    </div>

    <div class="container">
        <p>{{ $closet->CLOSET_NAME }} の服を使って<strong>「{{ $closet->CLOSET_NAME }}」に</strong>新しいコーデを登録します。</p>
        
        <form action="{{ route('closet.code.store') }}" method="POST" id="codeForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="closet_id" value="{{ $closet->CLOSET_ID }}">
            <input type="hidden" name="tags_data" id="tags_data">

            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/png, image/jpeg" onchange="previewImage(event, 'imagePreviewFull')">
            <div class="preview-box">
                <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none; max-width: 200px; margin-top:10px;">
            </div>
            <br>

            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ (Enterで追加)">
            <div id="tagContainer" class="tag-container" style="margin-top:5px; min-height:20px;"></div>
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
                    <p>服が登録されていません。「服追加」から登録してください。</p>
                @endforelse
            </div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>このコーデをお気に入り登録</span>
            </label>

            <div style="margin-top:20px;">
                <input type="submit" value="コーデを登録する" class="button primary">
                <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}" class="button">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // --- プレビュー ---
        function previewImage(event, previewId) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById(previewId);
                output.src = reader.result;
                output.style.display = 'block';
            };
            if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
        }

        // --- タグ機能 ---
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

        // --- 服の検索処理 (DBのデータに対応) ---
        function filterClothes() {
            const query = document.getElementById('wear_search').value.toLowerCase();
            const items = document.querySelectorAll('#clothesList .item');
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(query) ? 'block' : 'none';
            });
        }

        // --- 選択制限（シャツ・パンツ・シューズは1つまで） ---
        const checkboxes = document.querySelectorAll('input[name="clothing_ids[]"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const category = this.dataset.category;
                // アクセサリー以外は1つしか選べないようにする
                if (category !== 'accessory' && this.checked) {
                    checkboxes.forEach(other => {
                        if (other !== this && other.dataset.category === category) {
                            other.checked = false;
                        }
                    });
                }
            });
        });

        // --- 送信時バリデーション ---
        document.getElementById('codeForm').addEventListener('submit', function(e) {
            const checked = Array.from(document.querySelectorAll('input[name="clothing_ids[]"]:checked'));
            const categories = checked.map(c => c.dataset.category);
            
            // 必須カテゴリのチェック（もし必須でなければ削除可）
            const required = ['shirt', 'pants', 'shoes'];
            const missing = required.filter(r => !categories.includes(r));

            if (missing.length > 0) {
                // e.preventDefault(); // 必須にする場合はコメントアウトを外す
                // alert("シャツ、パンツ、シューズをそれぞれ1つずつ選択してください。");
                
                // 今回は必須にせず警告だけにしておきますか？
                // 完全に必須にするなら上の preventDefault を有効にしてください。
            }
        });

        // お気に入りトグル
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            const label = btn.querySelector('span:last-child');
            this.checked ? btn.classList.add('favorited') : btn.classList.remove('favorited');
            label.textContent = this.checked ? 'お気に入りに登録済み' : 'このコーデをお気に入り登録';
        });
    </script>
</body>
</html>