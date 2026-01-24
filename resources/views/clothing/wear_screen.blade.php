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
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin-right: 15px;
        overflow: hidden;
        flex-shrink: 0;
    }
    /* 画像本体 */
    .clothing-img {
        width: 100%;
        height: 100%;
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
                    // 現在のURLパラメータを維持しつつカテゴリだけ変える
                    $params = request()->query();
                    $params['category'] = $category;
                    $url = '/clothing/wear-screen?' . http_build_query($params);
                @endphp
                <a href="{{ $url }}" class="button" style="@if($selectedCategory === $category) background-color: #d32f2f; color: white; @endif">{{ $category }}</a>
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
            // フィルター操作時にURLを書き換えて移動する処理
            function updateFilters() {
                const params = new URLSearchParams(window.location.search);
                
                // タグ
                const tagSelect = document.getElementById('tag-select');
                if (tagSelect.value) {
                    params.set('tag', tagSelect.value);
                } else {
                    params.delete('tag');
                }
                
                // お気に入り
                const favoriteCheckbox = document.getElementById('favorite-checkbox');
                if (favoriteCheckbox.checked) {
                    params.set('favorite', 'true');
                } else {
                    params.delete('favorite');
                }
                
                window.location.href = '/clothing/wear-screen?' + params.toString();
            }
            
            document.getElementById('tag-select').addEventListener('change', updateFilters);
            document.getElementById('favorite-checkbox').addEventListener('change', updateFilters);
        </script>

        <div class="coord-item">
            @forelse($clothings as $clothing)
                <p class="item-line">
                    <span class="img-box">
                        @if($clothing->IMAGE_PATH)
                            <img class="clothing-img" src="{{ asset('storage/' . $clothing->IMAGE_PATH) }}" alt="{{ $clothing->CATEGORY }}">
                        @else
                            <img class="clothing-img" src="{{ asset('images/no_image.png') }}" alt="画像なし">
                        @endif
                    </span>
                    <span style="flex-grow: 1;">
                        <div>
                            <strong>{{ $clothing->ITEM_NAME }}</strong>
                            <span style="font-size: 0.9em; color: #666;">({{ $clothing->CATEGORY }})</span>
                            
                            @if($clothing->IS_FAVORITE)
                                <span style="color: #d32f2f; font-weight: bold; margin-left: 8px;">❤ お気に入り</span>
                            @endif
                        </div>
                        
                        @if($clothing->TAGS && count($clothing->TAGS) > 0)
                            <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                @foreach($clothing->TAGS as $tag)
                                    <span style="display: inline-block; background-color: #e0e0e0; padding: 2px 6px; margin-right: 4px; border-radius: 3px; font-size: 11px;">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </span>
                </p>
            @empty
                <p>条件に一致する服が見つかりませんでした。</p>
            @endforelse
        </div>
    </div>
</body>
</html>