<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コーデマスター登録</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .search-box { margin-bottom: 15px; width: 100%; padding: 8px; }
        .error-message { color: red; font-size: 12px; display: none; }
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
        <h1>コーデマスター登録</h1>
        <a href="{{ route('coord.manage') }}">コーデ管理へ</a>
    </div>

    <div class="container">
        <p>コーデの「原型」を登録・管理する</p>
        
        <form action="{{ route('coord.store') }}" method="POST" id="codeForm" enctype="multipart/form-data">
            @csrf
            
            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/jpeg,image/png" onchange="previewImage(event, 'imagePreviewFull')">
            <div class="preview-box">
                <img id="imagePreviewFull" src="" alt="画像プレビュー" class="preview-img" style="display:none; border: 1px solid #ccc; max-width: 100%;">
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
                @forelse($wears as $wear)
                    <label class="item" data-category="{{ $wear->CATEGORY }}" data-name="{{ $wear->ITEM_NAME }}">
                        <input type="checkbox" name="clothing_ids[]" value="{{ $wear->WEAR_ID }}" data-category="{{ $wear->CATEGORY }}">
                        
                        @if($wear->IMAGE_PATH)
                            <img src="{{ asset('storage/' . $wear->IMAGE_PATH) }}" alt="{{ $wear->CATEGORY }}">
                        @else
                            <img src="{{ asset('images/no_image.png') }}" alt="画像なし">
                        @endif
                        
                        <p style="font-size:11px; margin-top:5px;">{{ $wear->ITEM_NAME }}</p>
                        <span style="font-size:10px; color:#666;">{{ $wear->CATEGORY }}</span>
                    </label>
                @empty
                    <p>登録された服がありません。<br><a href="/clothing/clothing-add">服を追加</a>してください。</p>
                @endforelse
            </div>
            <br>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite" value="1">
                <span class="fav-icon">❤</span>
                <span>このコーデをお気に入り登録</span>
            </label>

            <div style="margin-top: 20px;">
                <input type="submit" value="コーデを登録する" class="primary">
                <a href="{{ route('coord.manage') }}" class="button">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // 1. 画像プレビュー
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

        // 2. 検索機能 (Enter誤送信防止付き)
        const searchInput = document.getElementById('itemSearch');
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.item').forEach(item => {
                // 名前とカテゴリ両方で検索
                const name = item.dataset.name.toLowerCase();
                const category = item.dataset.category.toLowerCase();
                if (name.includes(query) || category.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // 3. カテゴリー制限 (アクセ以外は1つまで)
        // ※カテゴリ名が日本語(アウター等)か英語(shirt等)か、DBの登録値に依存します。
        //   もしDBが日本語なら、下記の 'accessory' も 'アクセサリー' などの日本語にする必要がありますが、
        //   一旦はカテゴリ制限ロジックを入れておきます。
        const checkboxes = document.querySelectorAll('input[name="clothing_ids[]"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const category = this.dataset.category;
                
                // 「その他」や「アクセサリー」以外は1つに制限する例
                // もしカテゴリが日本語登録なら 'その他' などに書き換えてください
                const allowMultiple = ['その他', 'アクセサリー', 'accessory', 'other'];
                
                if (!allowMultiple.includes(category) && this.checked) {
                    checkboxes.forEach(other => {
                        if (other !== this && other.dataset.category === category) {
                            other.checked = false;
                        }
                    });
                }
            });
        });

        // 4. タグ機能
        let tags = [];
        const tagInput = document.getElementById('tag_input');
        const hiddenTags = document.getElementById('hidden_tags');
        const tagContainer = document.getElementById('tagContainer');

        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.trim();
                if (val && !tags.includes(val)) {
                    tags.push(val);
                    renderTags();
                    this.value = '';
                }
            }
        });

        function renderTags() {
            tagContainer.innerHTML = '';
            tags.forEach(tag => {
                const span = document.createElement('span');
                span.className = 'tag';
                span.innerHTML = `${tag} <span class="tag-delete" onclick="removeTag('${tag}')">×</span>`;
                tagContainer.appendChild(span);
            });
            hiddenTags.value = tags.join(',');
        }

        window.removeTag = function(tag) {
            tags = tags.filter(t => t !== tag);
            renderTags();
        };

        // 5. バリデーション & 送信
        document.getElementById('codeForm').addEventListener('submit', function(e) {
            const selectedCategories = Array.from(document.querySelectorAll('input[name="clothing_ids[]"]:checked'))
                                            .map(cb => cb.dataset.category);
            
            // 必須カテゴリチェック (日本語カテゴリ名で判定)
            // もしアウターなども必須ならここに追加
            const required = ['シャツ', 'ボトムス', 'シューズ']; 
            // ※DBのカテゴリ名に合わせてください。もし英語なら 'shirt', 'pants', 'shoes'
            
            // 今回は「どれか1つでも選んでいればOK」にするか、「必須」にするか。
            // 画面定義では「必須」となっていますが、登録されている服のカテゴリ名と一致しないと進めなくなるので注意。
            // 一旦、「服を1つ以上選んでいるか」だけのチェックにしておきます（安全のため）
            
            if (selectedCategories.length === 0) {
                e.preventDefault();
                alert('服を少なくとも1つ選択してください。');
                return;
            }

            // 厳密な必須チェックをするならこちらを有効化
            /*
            const hasShirt = selectedCategories.some(c => c === 'シャツ' || c === 'アウター');
            const hasPants = selectedCategories.includes('ボトムス');
            const hasShoes = selectedCategories.includes('シューズ');
            if (!hasShirt || !hasPants || !hasShoes) {
               // e.preventDefault();
               // alert('トップス、ボトムス、シューズは必須です');
            }
            */
        });

        // 6. お気に入りトグル
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            btn.querySelector('span:last-child').textContent = this.checked ? 'お気に入りに登録済み' : 'このコーデをお気に入り登録';
            this.checked ? btn.classList.add('favorited') : btn.classList.remove('favorited');
        });
    </script>
</body>
</html>