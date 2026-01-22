<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    // 今日のコーデ変更画面を表示する命令
    public function editCloset()
    {
        // resources/views/main/closet_entry_or_change.blade.php を表示せよという意味
        return view('main.closet_entry_or_change');
    }
    // 服マスター管理画面を表示する命令
    public function wear_screen()
    {
        // フィルタパラメータを取得
        $selectedCategory = request()->query('category', null);
        $selectedTag = request()->query('tag', null);
        $selectedFavorite = request()->query('favorite', null);
        
        // フィルタリングロジック
        $clothings = \App\Models\Clothing::all();
        
        // カテゴリでフィルタリング
        if ($selectedCategory) {
            $clothings = $clothings->filter(function($clothing) use ($selectedCategory) {
                return $clothing->category === $selectedCategory;
            });
        }
        
        // タグでフィルタリング
        if ($selectedTag) {
            $clothings = $clothings->filter(function($clothing) use ($selectedTag) {
                $tags = json_decode($clothing->tags, true) ?? [];
                return in_array($selectedTag, $tags);
            });
        }
        
        // お気に入りでフィルタリング
        if ($selectedFavorite === 'true') {
            $clothings = $clothings->filter(function($clothing) {
                return $clothing->is_favorite === true;
            });
        }
        
        // カテゴリ一覧を取得
        $allClothings = \App\Models\Clothing::all();
        $categories = $allClothings->pluck('category')->unique()->sort()->values();
        
        // タグ一覧を取得
        $allTags = collect();
        $allClothings->each(function($clothing) use (&$allTags) {
            $tags = json_decode($clothing->tags, true) ?? [];
            $allTags = $allTags->concat($tags);
        });
        $tags = $allTags->unique()->sort()->values();
        
        // resources/views/clothing/wear_screen.blade.php を表示せよという意味
        return view('clothing.wear_screen', [
            'clothings' => $clothings,
            'categories' => $categories,
            'tags' => $tags,
            'selectedCategory' => $selectedCategory,
            'selectedTag' => $selectedTag,
            'selectedFavorite' => $selectedFavorite,
        ]);
        
    }

    //服の情報変更画面を表示する命令
    public function wear_change()
    {
        // データベースから全ての服を取得
        $clothings = \App\Models\Clothing::all();
        // resources/views/clothing/wear_change.blade.php を表示せよという意味
        return view('clothing.wear_change', ['clothings' => $clothings]);
        
    }

    //服の情報編集画面を表示する命令
    public function wear_item_change($id)
    {
        // IDから服の情報を取得
        $clothing = \App\Models\Clothing::findOrFail($id);
        // resources/views/clothing/wear_item_change.blade.php を表示せよという意味
        return view('clothing.wear_item_change', ['clothing' => $clothing]);
    }

    //服の追加画面を表示する命令
    public function wear_add()
    {
        // resources/views/clothing/clothing_add.blade.php を表示せよという意味
        return view('clothing.clothing_add');
        
    }
    //服削除画面を表示する命令
    public function wear_delete()
    {
        // データベースから全ての服を取得
        $clothings = \App\Models\Clothing::all();
        // resources/views/clothing/wear_delete.blade.php を表示せよという意味
        return view('clothing.wear_delete', ['clothings' => $clothings]);
    }
}