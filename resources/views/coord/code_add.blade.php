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
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>コーデ追加</h1>
        <a href="/closet/view">クローゼットへ戻る</a>
    </div>

    <div class="container">
        <p>クローゼットA の服を使って<strong>「クローゼットA」に</strong>新しいコーデを登録します。</p>
        <p style="font-size: 14px; color: #555;">（コーデの「原型」を登録・管理する場合は「コーデマスター管理」から行います）</p>
        
        <form action="{{ route('closet.code.store') }}" method="POST" id="codeForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="closet_id" value="CLUS000001000001">
            <input type="hidden" name="tags_data" id="tags_data">

            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/png, image/jpeg" onchange="previewImage(event, 'imagePreviewFull')">
            <div class="preview-box">
                <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none; max-width: 100%;">
            </div>
            <br>
            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ (Enterで追加)">
            <div id="tagContainer" class="tag-container"></div>
            <br>

            <label>コーデに使う服を選択してください:</label>
            <input type="text" id="wear_search" class="search-box" placeholder="服の名前を検索..." onkeyup="filterClothes()">

            <div class="item-list" id="clothesList">
                <label class="item" data-category="accessory" data-name="アクセサリー2">
                    <input type="checkbox" name="clothing_ids[]" value="7" data-category="accessory">
                    <img src="{{ asset('images/sample_acc.jpg') }}" alt="アクセサリー2">
                    <p>アクセサリー2</p>
                </label>
                <label class="item" data-category="shirt" data-name="白Tシャツ">
                    <input type="checkbox" name="clothing_ids[]" value="1" data-category="shirt">
                    <img src="{{ asset('images/sample_shirt.jpg') }}" alt="服">
                    <p>白Tシャツ (シャツ)</p>
                </label>
                <label class="item" data-category="pants" data-name="黒パンツ">
                    <input type="checkbox" name="clothing_ids[]" value="2" data-category="pants">
                    <img src="{{ asset('images/sample_pants.jpg') }}" alt="服">
                    <p>黒パンツ (パンツ)</p>
                </label>
                <label class="item" data-category="shoes" data-name="スニーカー">
                    <input type="checkbox" name="clothing_ids[]" value="3" data-category="shoes">
                    <img src="{{ asset('images/sample_shoes.jpg') }}" alt="服">
                    <p>スニーカー (シューズ)</p>
                </label>
            </div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>このコーデをお気に入り登録</span>
            </label>
            <input type="submit" value="コーデを登録する" class="primary">
            <a href="/closet/view" class="button">キャンセル</a>
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
                    tag.innerHTML = `${val} <span class="tag-delete" onclick="removeTag('${val}', this)" style="cursor:pointer;color:red;">×</span>`;
                    tagContainer.appendChild(tag);
                    tagsDataHidden.value = tagsArray.join(',');
                }
                this.value = '';
            }
        });

        function removeTag(text, btn) {
            tagsArray = tagsArray.filter(t => t !== text);
            btn.parentElement.remove();
            tagsDataHidden.value = tagsArray.join(',');
        }

        // --- ★追加：服の検索処理 ---
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
                if (category !== 'accessory' && this.checked) {
                    checkboxes.forEach(other => {
                        if (other !== this && other.dataset.category === category) {
                            other.checked = false;
                        }
                    });
                }
            });
        });

        // --- 送信時バリデーション（必須項目チェック） ---
        document.getElementById('codeForm').addEventListener('submit', function(e) {
            const checked = Array.from(document.querySelectorAll('input[name="clothing_ids[]"]:checked'));
            const categories = checked.map(c => c.dataset.category);
            const required = ['shirt', 'pants', 'shoes'];
            const missing = required.filter(r => !categories.includes(r));

            if (missing.length > 0) {
                e.preventDefault();
                alert("シャツ、パンツ、シューズをそれぞれ1つずつ選択してください。");
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