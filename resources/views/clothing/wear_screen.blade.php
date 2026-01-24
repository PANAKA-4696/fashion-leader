<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>服マスター管理</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<style>
    /* 画像の枠（80x80の正方形） */
    .img-box {
        width: 80px;
        height: 80px;
        /* 余白ができても中央に画像が来るようにする */
        display: flex;
        align-items: center;
        justify-content: center;
        
        background-color: #f5f5f5; /* 背景色（画像が縦長のときに余白がわかるように） */
        border-radius: 4px;
        margin-right: 15px;
        overflow: hidden; /* はみ出した部分は隠す（念のため） */
        flex-shrink: 0;   /* 枠が潰れないようにする */
    }

    /* 画像本体 */
    .clothing-img {
        width: 100%;
        height: 100%;
        /* ★重要: アスペクト比を維持したまま、枠内に全体を収める */
        object-fit: contain; 
    }

    /* アイテムの行 */
    .item-line {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
        background: #fff;
        border-radius: 6px;
    }
</style>

</head>
<body>
    <div class="header-nav">
        <h1>服マスター管理</h1>
        <a href="{{ route('main.calendar') }}" class="back-btn">メインへ戻る</a>
    </div>

    <div class="container">
        <p>
            ここでは、アプリに登録されている<strong>すべての服（マスターデータ）</strong>を管理します。<br>
            ここでの変更や削除は、<strong>すべてのクローゼットに影響します</strong>のでご注意ください。
        </p>
        <p style="font-size: 14px; color: #555;">（クローゼット内の服を非表示にする・入れ替える機能は、各クローゼットの詳細画面で行います）</p>

        <a href="wear-change" class="button">服の情報を変更</a>
        <a href="clothing-add" class="button primary">服を追加</a>
        <a href="wear-delete" class="button danger">服を削除</a>
        <hr>

        <h3>登録済みの服一覧 (全件)</h3>
        
        <div style="margin-bottom: 15px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: bold; align-self: center;">カテゴリ:</span>
            <a href="/clothing/wear-screen" class="button" style="@if(!$selectedCategory) background-color: #d32f2f; color: white; @endif">すべて表示</a>
            @foreach($categories as $category)
                @php
                    $categoryUrl = '/clothing/wear-screen?category=' . urlencode($category);
                    if ($selectedTag) {
                        $categoryUrl .= '&tag=' . urlencode($selectedTag);
                    }
                    if ($selectedFavorite) {
                        $categoryUrl .= '&favorite=' . $selectedFavorite;
                    }
                @endphp
                <a href="{{ $categoryUrl }}" class="button" style="@if($selectedCategory === $category) background-color: #d32f2f; color: white; @endif">{{ $category }}</a>
            @endforeach
        </div>
        
        <div style="margin-bottom: 15px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="display: flex; gap: 8px; align-items: center;">
                <label for="tag-select" style="font-weight: bold; white-space: nowrap; flex-shrink: 0;">タグ:</label>
                <select id="tag-select" style="padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">すべてのタグ</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag }}" @if($selectedTag === $tag) selected @endif>{{ $tag }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; gap: 8px; align-items: center;">
                <label for="favorite-checkbox" style="font-weight: bold;">お気に入りのみ:</label>
                <input type="checkbox" id="favorite-checkbox" @if($selectedFavorite === 'true') checked @endif style="width: 18px; height: 18px; cursor: pointer;">
            </div>
        </div>
        
        <script>
            function updateFilters() {
                const params = new URLSearchParams();
                
                @if($selectedCategory)
                    params.append('category', '{{ urlencode($selectedCategory) }}');
                @endif
                
                const tagSelect = document.getElementById('tag-select');
                if (tagSelect.value) {
                    params.append('tag', tagSelect.value);
                }
                
                const favoriteCheckbox = document.getElementById('favorite-checkbox');
                if (favoriteCheckbox.checked) {
                    params.append('favorite', 'true');
                }
                
                const queryString = params.toString();
                window.location.href = '/clothing/wear-screen' + (queryString ? '?' + queryString : '');
            }
            
            document.getElementById('tag-select').addEventListener('change', updateFilters);
            document.getElementById('favorite-checkbox').addEventListener('change', updateFilters);
        </script>

        <div class="coord-item">
            @forelse($clothings as $clothing)
                <p class="item-line">
                    <span class="img-box">
                        @if($clothing->IMAGE_PATH)
                            <img src="{{ asset('storage/' . $clothing->IMAGE_PATH) }}" class="clothing-img" alt="{{ $clothing->CATEGORY }}">
                        @else
                            <img class="clothing-img" src="" alt="画像なし">
                        @endif
                    </span>
                    <span style="flex-grow: 1;">
                        <div>
                            {{ $clothing->CATEGORY }}
                            @if($clothing->is_favorite)
                                <span style="color: #d32f2f; font-weight: bold; margin-left: 8px;">❤ お気に入り</span>
                            @endif
                        </div>
                        @if($clothing->tags)
                            <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                @php
                                    $tags = json_decode($clothing->tags, true) ?? [];
                                @endphp
                                @forelse($tags as $tag)
                                    <span style="display: inline-block; background-color: #e0e0e0; padding: 2px 6px; margin-right: 4px; border-radius: 3px; font-size: 11px;">{{ $tag }}</span>
                                @empty
                                @endforelse
                            </div>
                        @endif
                    </span>
                </p>
            @empty
                <p>登録された服がありません。<a href="clothing-add">服を追加</a>してください。</p>
            @endforelse
            </div>
    </div>
</body>
</html>
