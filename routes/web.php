<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//リーダタナカ担当場所
// 今日のコーデ変更保存画面
use App\Http\Controllers\MainController;
// http://localhost/main/edit にアクセスしたら、MainControllerのeditClosetを実行する
Route::get('/main/closet-edit', [MainController::class, 'editCloset']);

use App\Http\Controllers\CoordinationController;
Route::get('/coord/add', [CoordinationController::class, 'create']);

// コーデマスター保存画面
Route::get('/coord/save', [CoordinationController::class, 'createMaster']);

//フクシマ担当場所

//タナカ担当場所

//オオタ担当場所

//モギ担当場所

//クニヤス担当場所
