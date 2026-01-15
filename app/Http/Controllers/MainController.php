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
        // resources/views/clothing/wear_screen.blade.php を表示せよという意味
        return view('clothing.wear_screen');
        
    }

    //服の情報変更画面を表示する命令
    public function wear_change()
    {
        // resources/views/clothing/wear_change.blade.php を表示せよという意味
        return view('clothing.wear_change');
        
    }

    //服の情報変更画面の編集画面を表示する命令
    public function wear_item_change()
    {
        // resources/views/clothing/wear_item_change.blade.php を表示せよという意味
        return view('clothing.wear_item_change');
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
        // resources/views/clothing/wear_delete.blade.php を表示せよという意味
        return view('clothing.wear_delete');
    }
}