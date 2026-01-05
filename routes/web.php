<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//リーダタナカ担当場所
// 今日のコーデ変更保存画面
Route::get('/main/closet-edit', [MainController::class, 'editCloset']);

//フクシマ担当場所

//タナカ担当場所

//オオタ担当場所

//モギ担当場所

//クニヤス担当場所
