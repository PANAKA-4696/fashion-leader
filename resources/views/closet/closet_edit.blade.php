<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クローゼット編集画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <style>
        /* --- ポップアップ用のスタイル --- */
        .flash-message {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            background-color: #4CAF50; color: white; padding: 15px 30px;
            border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999; opacity: 0; visibility: hidden;
            transition: opacity 0.5s ease-in-out, visibility 0.5s;
        }
        .flash-message.show { visibility: visible; opacity: 1; }

        /* --- タグ用スタイル --- */
        .tag-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; min-height: 30px; }
        .tag-chip {
            background-color: #e0e0e0; color: #333; padding: 5px 12px;
            border-radius: 16px; display: inline-flex; align-items: center; font-size: 0.9em;
        }
        .tag-remove { margin-left: 8px; cursor: pointer; color: #666; font-weight: bold; font-size: 1.1em; line-height: 1; }
        .tag-remove:hover { color: #ff4d4d; }
        .tag-input-group { display: flex; gap: 5px; }
        #tag-input { flex: 1; }

        /* --- お気に入りボタン用スタイル --- */
        .favorite-toggle-btn {
            display: inline-flex; align-items: center; cursor: pointer; padding: 8px 12px;
            border: 1px solid #ccc; border-radius: 4px; background: #fff; transition: all 0.2s; user-select: none;
        }
        .favorite-toggle-btn input { display: none; }
        .favorite-toggle-btn .fav-icon { margin-right: 5px; color: #ccc; font-size: 18px; }
        .favorite-toggle-btn.favorited { border-color: #ff4d4d; background: #fff5f5; color: #ff4d4d; }
        .favorite-toggle-btn.favorited .fav-icon { color: #ff4d4d; }
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

        <hr style="margin: 40px 0 20px 0; border: 0; border-top: 1px solid #eee;">

        <div style="text-align: right;">
            <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                ※このクローゼットを削除しても、中身の服やコーデのデータ自体は消えません。
            </p>
            
            <form action="{{ route('closet.destroy', ['id' => $closet->CLOSET_ID]) }}" method="POST" onsubmit="return confirm('本当にこのクローゼットを削除しますか？\n\n削除すると一覧画面に戻ります。');">
                @csrf
                <button type="submit" class="button" style="background-color: #ff4d4d; color: white; border: none;">
                    このクローゼットを削除する
                </button>
            </form>
        </div>
        </div>

    <script>
        // --- 1. ポップアップ制御 ---
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('flash-popup');
            if (popup) {
                setTimeout(() => { popup.classList.add('show'); }, 100);
                setTimeout(() => { popup.classList.remove('show'); }, 3000);
            }
        });

        // --- 2. お気に入りボタン制御 ---
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

        // --- 3. タグ機能制御 ---
        const tagInput = document.getElementById('tag-input');
        const addTagBtn = document.getElementById('add-tag-btn');
        const tagList = document.getElementById('tag-list');
        const hiddenTags = document.getElementById('hidden-tags');

        let tags = hiddenTags.value ? hiddenTags.value.split(',').map(t => t.trim()).filter(t => t !== "") : [];

        function renderTags() {
            tagList.innerHTML = '';
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.innerHTML = `${tag} <span class="tag-remove" onclick="removeTag(${index})">×</span>`;
                tagList.appendChild(chip);
            });
            hiddenTags.value = tags.join(',');
        }

        function addTag() {
            const value = tagInput.value.trim();
            if (value && !tags.includes(value)) {
                tags.push(value);
                renderTags();
                tagInput.value = '';
            }
        }

        window.removeTag = function(index) {
            tags.splice(index, 1);
            renderTags();
        };

        tagInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag();
            }
        });

        addTagBtn.addEventListener('click', addTag);
        renderTags();
    </script>
</body>
</html>