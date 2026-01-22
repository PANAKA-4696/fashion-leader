<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クローゼット編集画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <style>
        /* --- ここから追加：ポップアップ用のスタイル --- */
        .flash-message {
            position: fixed;
            top: 20px;       /* 画面の上から20pxの位置 */
            left: 50%;       /* 左右中央 */
            transform: translateX(-50%);
            background-color: #4CAF50; /* 緑色 */
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;   /* 最前面に表示 */
            opacity: 0;      /* 最初は透明 */
            visibility: hidden;
            transition: opacity 0.5s ease-in-out, visibility 0.5s;
        }
        .flash-message.show {
            visibility: visible;
            opacity: 1;      /* ふわっと表示 */
        }
        /* --- ここまで追加 --- */

        /* --- 以下は既存のタグ用スタイル --- */
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
            min-height: 30px;
        }
        .tag-chip {
            background-color: #e0e0e0;
            color: #333;
            padding: 5px 12px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            font-size: 0.9em;
        }
        .tag-remove {
            margin-left: 8px;
            cursor: pointer;
            color: #666;
            font-weight: bold;
            font-size: 1.1em;
            line-height: 1;
        }
        .tag-remove:hover {
            color: #ff4d4d;
        }
        .tag-input-group {
            display: flex;
            gap: 5px;
        }
        #tag-input {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>クローゼット編集</h1>
        <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}">詳細へ戻る</a>
    </div>

    @if(session('success'))
        <div id="flash-popup" class="flash-message">
            {{ session('success') }}
        </div>
    @endif

    <div class="container">
        <p>現在のクローゼット名：{{ $closet->CLOSET_NAME }}</p>
        
        <form action="{{ route('closet.update', ['id' => $closet->CLOSET_ID]) }}" method="POST">
            @csrf
            
            <label for="new_closet_name">新しいクローゼット名:</label>
            <input type="text" id="new_closet_name" name="new_closet_name" value="{{ $closet->CLOSET_NAME }}" required>
            
            <br><br>

            <label>タグ設定:</label>
            <div class="tag-container">
                <div id="tag-list" class="tag-list"></div>
                
                <div class="tag-input-group">
                    <input type="text" id="tag-input" placeholder="タグを入力してEnterまたは追加ボタン">
                    <button type="button" id="add-tag-btn" class="button">追加</button>
                </div>
            </div>
            
            <input type="hidden" name="new_tag" id="hidden-tags" value="{{ $currentTagsString ?? '' }}">
            
            <br>
            
            <label class="favorite-toggle-btn {{ $isFavorite ? 'favorited' : '' }}" for="favorite-toggle">
                <input type="checkbox" id="favorite-toggle" name="is_favorite" {{ $isFavorite ? 'checked' : '' }}>
                <span class="fav-icon">❤</span>
                <span>{{ $isFavorite ? 'お気に入りに登録済み' : 'クローゼットをお気に入り登録' }}</span>
            </label>

            <div style="margin-top: 20px;">
                <button type="submit" class="button primary">保存</button>
                <a href="{{ route('closet.view', ['id' => $closet->CLOSET_ID]) }}" class="button">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // 画面が読み込まれたら実行
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('flash-popup');
            if (popup) {
                // 1. 表示する
                setTimeout(() => {
                    popup.classList.add('show');
                }, 100);

                // 2. 3秒後に消える
                setTimeout(() => {
                    popup.classList.remove('show');
                }, 3000);
            }
        });


        // --- 2. お気に入りボタンの制御 ---
        document.getElementById('favorite-toggle').addEventListener('change', function() {
            const btn = this.closest('.favorite-toggle-btn');
            if (this.checked) {
                btn.classList.add('favorited');
                btn.querySelector('span:last-child').textContent = 'お気に入りに登録済み';
            } else {
                btn.classList.remove('favorited');
                btn.querySelector('span:last-child').textContent = 'クローゼットをお気に入り登録';
            }
        });


        // --- 3. タグ機能の制御 ---
        const tagInput = document.getElementById('tag-input');
        const addTagBtn = document.getElementById('add-tag-btn');
        const tagList = document.getElementById('tag-list');
        const hiddenTags = document.getElementById('hidden-tags');

        // 初期値（カンマ区切り文字列）を配列に変換。空文字は除去。
        let tags = hiddenTags.value ? hiddenTags.value.split(',').map(t => t.trim()).filter(t => t !== "") : [];

        // 画面と隠しフィールドを更新する関数
        function renderTags() {
            tagList.innerHTML = ''; // 一旦リセット
            
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                // タグ名 + 削除ボタン(×)
                chip.innerHTML = `${tag} <span class="tag-remove" onclick="removeTag(${index})">×</span>`;
                tagList.appendChild(chip);
            });
            
            // 配列をカンマ区切り文字列に戻して隠しフィールドへセット
            hiddenTags.value = tags.join(',');
        }

        // タグを追加する関数
        function addTag() {
            const value = tagInput.value.trim();
            // 空でなく、かつ既に同じタグがない場合のみ追加
            if (value && !tags.includes(value)) {
                tags.push(value);
                renderTags();
                tagInput.value = ''; // 入力欄をクリア
            }
        }

        // タグを削除する関数（HTML側から呼ぶため window に紐付け）
        window.removeTag = function(index) {
            tags.splice(index, 1); // 指定した位置から1つ削除
            renderTags();
        };

        // エンターキーでタグ追加（フォーム送信を防ぐ）
        tagInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Enterキーによるsubmitを防止
                addTag();
            }
        });

        // 「追加」ボタンクリックでタグ追加
        addTagBtn.addEventListener('click', addTag);

        // 初回表示（DBにあるタグを表示）
        renderTags();
    </script>
</body>
</html>