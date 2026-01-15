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

// 服マスター管理画面
// http://localhost/clothing/wear-screen にアクセスしたら、MainControllerのwear_screenを実行する
Route::get('/clothing/wear-screen', [MainController::class, 'wear_screen']);

//服の情報変更画面
Route::get('/clothing/wear-change', [MainController::class, 'wear_change']);

//服の情報変更画面の編集画面
Route::get('/clothing/wear-item-change', [MainController::class, 'wear_item_change']);

//服の追加画面
Route::get('/clothing/clothing-add', [MainController::class, 'wear_add']);

//服削除画面
Route::get('/clothing/wear-delete', [MainController::class, 'wear_delete']);


//オオタ担当場所

//モギ担当場所

//クニヤス担当場所
