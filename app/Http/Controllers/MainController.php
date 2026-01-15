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
    public function Calendar()
    {
        // resources/views/main/Calendar_menu.blade.php を表示せよという意味
        return view('main.Calendar_menu');
    }
    public function Closet_clothes()
    {
        // resources/views/main/closet_clothes.blade.php を表示せよという意味
        return view('main.closet_clothes');
    }

}