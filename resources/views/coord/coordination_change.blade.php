<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コーデ変更画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .search-box { margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .error-message { color: red; font-size: 12px; display: none; margin-bottom: 10px; }
        .tag-container { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px; }
        .tag-item { background: #eee; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>コーデ変更（編集）</h1>
        <a href="/coord/choice">コーデ選択へ</a>
    </div>

    <div class="container">
        <p>新しい服（マスターデータ）をアプリに登録します。</p>
        <p style="font-size: 14px; color: #555;">（コーデの「原型」を登録・管理する場合は「コーデマスター管理」から行います）</p>
        
        <form action="/coord/update" method="POST" id="codeForm" enctype="multipart/form-data">
            @csrf
            
            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/jpeg,image/png" onchange="previewImage(event, 'imagePreviewFull')">
            <div class="preview-box">
                <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none; border: 1px solid #ccc;">
            </div>
            <br>

            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ(Enterで追加)">
            <div id="tagContainer" class="tag-container"></div>
            <input type="hidden" name="tags" id="hidden_tags">
            <br>

            <label>コーデに使う服を選択してください:</label>
            <input type="text" id="itemSearch" class="search-box" placeholder="服を検索...">
            
            <div id="itemValidationError" class="error-message">シャツ、パンツ、シューズは必須項目です。</div>

            <div class="item-list" id="itemList">
                <label class="item" data-category="shirt">
                    <input type="checkbox" name="clothing_ids[]" value="101" data-category="shirt">
                    <img src="{{ asset('images/sample_shirt.jpg') }}" alt="シャツ">
                    <p>ストライプシャツ</p>
                </label>
                <label class="item" data-category="pants">
                    <input type="checkbox" name="clothing_ids[]" value="102" data-category="pants">
                    <img src="{{ asset('images/sample_pants.jpg') }}" alt="パンツ">
                    <p>チノパンツ</p>
                </label>
                <label class="item" data-category="shoes">
                    <input type="checkbox" name="clothing_ids[]" value="103" data-category="shoes">
                    <img src="{{ asset('images/sample_shoes.jpg') }}" alt="シューズ">
                    <p>ローファー</p>
                </label>
                <label class="item" data-category="accessory">
                    <input type="checkbox" name="clothing_ids[]" value="104" data-category="accessory">
                    <img src="{{ asset('images/sample_acc.jpg') }}" alt="アクセサリー">
                    <p>腕時計</p>
                </label>
            </div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>このコーデをお気に入り登録</span>
            </label>

            <input type="submit" value="コーデを登録する" class="primary">
            
            <a href="javascript:history.back();" class="button">キャンセル</a>
        </form>
    </div>

    <script>
        // --- No.6 プレビュー機能 ---
        function previewImage(event, previewId) {
            const output = document.getElementById(previewId);
            const reader = new FileReader();
            reader.onload = function() {
                output.src = reader.result;
                output.style.display = 'block';
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        // --- No.8~9 タグ追加機能 ---
        const tagInput = document.getElementById('tag_input');
        const tagContainer = document.getElementById('tagContainer');
        const hiddenTags = document.getElementById('hidden_tags');
        let tags = [];

        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tagValue = this.value.trim();
                if (tagValue && !tags.includes(tagValue)) {
                    tags.push(tagValue);
                    const tagElem = document.createElement('span');
                    tagElem.className = 'tag-item';
                    tagElem.textContent = tagValue;
                    tagContainer.appendChild(tagElem);
                    hiddenTags.value = tags.join(',');
                }
                this.value = '';
            }
        });

        // --- No.11 検索機能 ---
        document.getElementById('itemSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.item').forEach(item => {
                const name = item.querySelector('p').textContent.toLowerCase();
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });

        // --- No.12 カテゴリー制限（シャツ・パンツ・シューズは1つまで） ---
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

        // --- No.14 バリデーションチェック ---
        document.getElementById('codeForm').addEventListener('submit', function(e) {
            const selectedCategories = Array.from(document.querySelectorAll('input[name="clothing_ids[]"]:checked'))
                                            .map(cb => cb.dataset.category);
            
            const required = ['shirt', 'pants', 'shoes'];
            const hasAllRequired = required.every(req => selectedCategories.includes(req));

            if (!hasAllRequired) {
                e.preventDefault();
                document.getElementById('itemValidationError').style.display = 'block';
                alert('シャツ、パンツ、シューズは必須項目です。選択を確認してください。');
            }
        });

        // お気に入りボタンの表示トグル
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            btn.querySelector('span:last-child').textContent = this.checked ? 'お気に入りに登録済み' : 'このコーデをお気に入り登録';
            this.checked ? btn.classList.add('favorited') : btn.classList.remove('favorited');
        });
    </script>
</body>
</html>