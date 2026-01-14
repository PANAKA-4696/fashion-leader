<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoordinationController extends Controller
{
    /**
     * コーデ追加画面を表示する
     */
    public function create()
    {
        // resources/views/coord/code_add.blade.php を呼び出す
        return view('coord.code_add');
    }

    // 追加する関数
    public function createMaster()
    {
        // resources/views/coord/cordination_save.blade.php を表示
        return view('coord.cordination_save');
    }

    // 今後、保存処理などを作る場合はここに追記していきます
}