<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>服マスター管理</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="header-nav">
        <h1>服マスター管理</h1>
        <a href="../main/calendar_menu.html">メインへ戻る</a>
    </div>

    <div class="container">
        <p>
            ここでは、アプリに登録されている<strong>すべての服（マスターデータ）</strong>を管理します。<br>
            ここでの変更や削除は、<strong>すべてのクローゼットに影響します</strong>のでご注意ください。
        </p>
        <p style="font-size: 14px; color: #555;">（クローゼット内の服を非表示にする・入れ替える機能は、各クローゼットの詳細画面で行います）</p>

        <a href="wear-change" class="button">服の情報を変更</a>
        <a href="clothing-add" class="button primary">服をマスターに追加</a>
        <a href="wear-delete" class="button danger">服をマスターから削除</a>
        <hr>

        <h3>登録済みの服一覧 (全件)</h3>
        
        <div style="margin-bottom: 15px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: bold; align-self: center;">カテゴリ:</span>
            <a href="/clothing/wear-screen" class="button" style="@if(!$selectedCategory) background-color: #4CAF50; color: white; @endif">すべて表示</a>
            @foreach($categories as $category)
                @php
                    $categoryUrl = '/clothing/wear-screen?category=' . urlencode($category);
                    if ($currentSort !== 'default') {
                        $categoryUrl .= '&sort=' . $currentSort;
                    }
                @endphp
                <a href="{{ $categoryUrl }}" class="button" style="@if($selectedCategory === $category) background-color: #4CAF50; color: white; @endif">{{ $category }}</a>
            @endforeach
        </div>
        
        <div style="margin-bottom: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: bold; align-self: center;">ソート:</span>
            @php
                function buildUrl($sort, $category = null) {
                    $url = '/clothing/wear-screen?sort=' . $sort;
                    if ($category) {
                        $url .= '&category=' . urlencode($category);
                    }
                    return $url;
                }
            @endphp
            <a href="{{ buildUrl('default', $selectedCategory) }}" class="button" style="@if($currentSort === 'default') background-color: #2196F3; color: white; @endif">追加順</a>
            <a href="{{ buildUrl('category', $selectedCategory) }}" class="button" style="@if($currentSort === 'category') background-color: #2196F3; color: white; @endif">カテゴリ順</a>
            <a href="{{ buildUrl('favorite', $selectedCategory) }}" class="button" style="@if($currentSort === 'favorite') background-color: #2196F3; color: white; @endif">お気に入り優先</a>
            <a href="{{ buildUrl('tag', $selectedCategory) }}" class="button" style="@if($currentSort === 'tag') background-color: #2196F3; color: white; @endif">タグが多い順</a>
        </div>
        <div class="coord-item">
            @forelse($clothings as $clothing)
                <p class="item-line">
                    <span class="img-box">
                        @if($clothing->image_path)
                            <img class="clothing-img" src="{{ asset('storage/' . $clothing->image_path) }}" alt="{{ $clothing->category }}">
                        @else
                            <img class="clothing-img" src="" alt="画像なし">
                        @endif
                    </span>
                    <span style="flex-grow: 1;">
                        <div>
                            {{ $clothing->category }}
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