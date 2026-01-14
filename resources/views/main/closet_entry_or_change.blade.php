<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>今日のコーデ 登録/変更</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>今日のコーデ 登録/変更</h1>
        <a href="/main/closet-clothes">今日のコーデ確認画面へ</a>
    </div>

    <div class="container">
        <h2>今日のコーデを登録しましょう。</h2>
        <p style="font-size: 14px; color: #555;">
            （ここでのコーデ登録は簡易的な物なので、コーデの「原型」を登録・管理する場合は「コーデマスター管理」から行います）
        </p>
        
        <form action="/main/calendar" method="POST" enctype="multipart/form-data" id="closetForm">
            @csrf
            
            <label for="coord_image">全体写真 (任意):</label>
            <input type="file" id="coord_image" name="coord_image" accept="image/png, image/jpeg" onchange="previewImage(event)">
            <div class="preview-box">
                <img id="imagePreview" src="" alt="画像プレビュー" class="preview-img" style="display:none;">
            </div>
            
            <br>

            <label for="tag_input">コーデのタグ:</label>
            <input type="text" id="tag_input" placeholder="例：春のデートコーデ(Enterで追加)">
            <div id="tagContainer" class="tag-container">
                </div>

            <br>

            <label for="wear_search">コーデに使う服を選択してください:</label>
            <input type="text" id="wear_search" placeholder="服を検索..." onkeyup="filterClothes()">
            
            <div class="item-list" id="clothesList">
                <label class="item" data-category="shirt" data-name="白Tシャツ">
                    <input type="checkbox" name="clothing_ids[]" value="1">
                    <img src="{{ asset('images/sample_shirt.jpg') }}" alt="服">
                    <p>白Tシャツ (シャツ)</p>
                </label>
                <label class="item" data-category="pants" data-name="デニム">
                    <input type="checkbox" name="clothing_ids[]" value="2">
                    <img src="{{ asset('images/sample_pants.jpg') }}" alt="服">
                    <p>デニム (パンツ)</p>
                </label>
                </div>

            <hr>

            <label class="favorite-toggle-btn" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite">
                <span class="fav-icon">❤</span>
                <span>今日のコーデをお気に入り登録</span>
            </label>

            <div style="margin-top: 20px;">
                <input type="submit" value="コーデを登録する" class="primary">
                <a href="/main/closet-clothes" class="button">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // No.7: プレビュー処理
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // No.9, 10: タグ入力機能
        const tagInput = document.getElementById('tag_input');
        const tagContainer = document.getElementById('tagContainer');
        tagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tagText = this.value.trim();
                if (tagText) {
                    const tag = document.createElement('span');
                    tag.className = 'tag';
                    tag.innerHTML = `${tagText} <span class="tag-delete" onclick="this.parentElement.remove()">×</span>`;
                    tagContainer.appendChild(tag);
                    this.value = '';
                }
            }
        });

        // No.12: 服の簡易検索
        function filterClothes() {
            const query = document.getElementById('wear_search').value.toLowerCase();
            const items = document.querySelectorAll('#clothesList .item');
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(query) ? 'block' : 'none';
            });
        }

        // No.13: 選択制限（シャツ・パンツ・シューズは1つまで等）のバリデーション
        document.getElementById('closetForm').addEventListener('submit', function(e) {
            // ここで「シャツ、パンツ、シューズが必須」などのチェックをJSで行うことができます
            // 今回は構造の作成を優先するため、枠組みのみ用意しています
        });

        // No.14: お気に入りボタンのトグル
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            this.checked ? btn.classList.add('favorited') : btn.classList.remove('favorited');
        });
    </script>
</body>
</html>