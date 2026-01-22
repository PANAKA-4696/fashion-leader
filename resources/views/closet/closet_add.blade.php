<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クローゼット新規作成</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <style>
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
        <h1>クローゼット新規作成</h1>
        <a href="{{ route('closet.main') }}">一覧へ戻る</a>
    </div>

    <div class="container">
        <form action="{{ route('closet.store') }}" method="POST">
            @csrf
            
            <label for="closet_name">クローゼット名 <span style="color:red;">*</span></label>
            <input type="text" id="closet_name" name="closet_name" placeholder="例：お仕事用" required>
            
            <br><br>

            <label>タグ設定:</label>
            <div class="tag-container">
                <div id="tag-list" class="tag-list"></div>
                
                <div class="tag-input-group">
                    <input type="text" id="tag-input" placeholder="タグを入力してEnterまたは追加ボタン">
                    <button type="button" id="add-tag-btn" class="button">追加</button>
                </div>
            </div>
            
            <input type="hidden" name="new_tag" id="hidden-tags" value="">

            <div style="margin-top: 20px;">
                <button type="submit" class="button primary">作成する</button>
                <a href="{{ route('closet.main') }}" class="button">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // --- タグ機能の制御 ---
        const tagInput = document.getElementById('tag-input');
        const addTagBtn = document.getElementById('add-tag-btn');
        const tagList = document.getElementById('tag-list');
        const hiddenTags = document.getElementById('hidden-tags');

        let tags = [];

        // 画面と隠しフィールドを更新する関数
        function renderTags() {
            tagList.innerHTML = '';
            
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.innerHTML = `${tag} <span class="tag-remove" onclick="removeTag(${index})">×</span>`;
                tagList.appendChild(chip);
            });
            
            // カンマ区切りで隠しフィールドへ
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
    </script>
</body>
</html>