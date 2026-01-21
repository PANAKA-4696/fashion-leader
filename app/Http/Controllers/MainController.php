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
        // ソート種別とカテゴリを取得
        $sortBy = request()->query('sort', 'default');
        $selectedCategory = request()->query('category', null);
        
        // ソートロジック
        $clothings = \App\Models\Clothing::all();
        
        // カテゴリでフィルタリング
        if ($selectedCategory) {
            $clothings = $clothings->filter(function($clothing) use ($selectedCategory) {
                return $clothing->category === $selectedCategory;
            });
        }
        
        switch ($sortBy) {
            case 'category':
                // カテゴリでソート
                $clothings = $clothings->sortBy('category');
                break;
            case 'favorite':
                // お気に入り優先（trueが先）
                $clothings = $clothings->sortByDesc('is_favorite');
                break;
            case 'tag':
                // タグが多い順（複雑なので、タグ数でソート）
                $clothings = $clothings->sortByDesc(function($clothing) {
                    $tags = json_decode($clothing->tags, true) ?? [];
                    return count($tags);
                });
                break;
            case 'default':
            default:
                // デフォルトは追加順（ID昇順）
                $clothings = $clothings->sortBy('id');
                break;
        }
        
        // カテゴリ一覧を取得
        $allClothings = \App\Models\Clothing::all();
        $categories = $allClothings->pluck('category')->unique()->sort()->values();
        
        // resources/views/clothing/wear_screen.blade.php を表示せよという意味
        return view('clothing.wear_screen', [
            'clothings' => $clothings,
            'currentSort' => $sortBy,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
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