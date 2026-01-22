<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\CoordinationController;


Route::get('/', function () {
    return view('welcome');
});

//リーダー田中担当場所

// コーデ管理
Route::get('/coord/add', [CoordinationController::class, 'create']);
Route::get('/coord/save', [CoordinationController::class, 'createMaster']);
// コーデマスターの保存実行（フォームのaction先に指定します）
Route::post('/coord/master-store', [CoordinationController::class, 'storeMaster'])->name('coord.master.store');

// コーデマスター変更画面
Route::get('/coord/edit', [CoordinationController::class, 'editMaster']);

// ★ここが修正箇所です（closet.code.add → closet.coord.add に変更）
// コーデ追加画面の表示（クローゼットIDを渡す想定）
Route::get('/closet/{closet_id}/add-code', [CoordinationController::class, 'create'])->name('closet.coord.add');

// 保存処理
Route::post('/closet/code-store', [CoordinationController::class, 'store'])->name('closet.code.store');

//フクシマ担当場所(タナカ作)
use App\Http\Controllers\ClosetController; // これが必要です！
// クローゼット一覧画面
Route::get('/closet/main', [ClosetController::class, 'index'])->name('closet.main');

// クローゼット詳細表示 (closet_view)
Route::get('/closet/view/{id}', [ClosetController::class, 'show'])->name('closet.view');

// クローゼット編集画面の表示
Route::get('/closet/edit/{id}', [ClosetController::class, 'edit'])->name('closet.edit');
// クローゼット編集内容の保存（更新）
Route::post('/closet/update/{id}', [ClosetController::class, 'update'])->name('closet.update');


//クニヤス担当場所

// カレンダー API
Route::get('/api/calendar-status', [MainController::class, 'getMonthlyStatus']);

// 今日のコーデ取得 API
Route::get('/api/coord', [MainController::class, 'getCoordData']);
  
// カレンダー画面
Route::get('/main/calendar', [MainController::class, 'calendar']);

// 今日のコーデ確認画面
Route::get('/main/closet_clothes', [MainController::class, 'closet_clothes']);
=======
// 修正後（名前を追加）
Route::get('/coord/add', [CoordinationController::class, 'create'])->name('coord.add');

// クローゼットから特定のコーデを削除する（紐付けを解除する）
Route::post('/closet/coord/delete', [ClosetController::class, 'removeCoord'])->name('closet.coord.delete');

// クローゼット追加画面の表示
Route::get('/closet/add', [ClosetController::class, 'create'])->name('closet.add');

// クローゼットの保存処理
Route::post('/closet/store', [ClosetController::class, 'store'])->name('closet.store');


// 今日のコーデ編集画面（← アンダーバーに統一）
Route::get('/main/closet_edit', [MainController::class, 'closet_edit']);
Route::post('/main/closet_edit', [MainController::class, 'saveCoord']);


//田中担当場所

// 服マスター管理画面
// http://localhost/clothing/wear-screen にアクセスしたら、MainControllerのwear_screenを実行する
Route::get('/clothing/wear-screen', [MainController::class, 'wear_screen']);

//服の情報変更画面
Route::get('/clothing/wear-change', [MainController::class, 'wear_change']);

//服の情報変更画面の編集画面
Route::get('/clothing/wear-item-change', [MainController::class, 'wear_item_change']);

//服の追加画面
Route::get('/clothing/clothing-add', [MainController::class, 'wear_add']);

//服の保存処理
use App\Http\Controllers\ClothingController;
Route::post('/clothing/store', [ClothingController::class, 'store']);

//服の削除処理
Route::delete('/clothing/{id}', [ClothingController::class, 'destroy']);

//服の情報編集画面
Route::get('/clothing/wear-item-change/{id}', [MainController::class, 'wear_item_change']);

//服の情報更新処理
Route::put('/clothing/{id}', [ClothingController::class, 'update']);

//服削除画面
Route::get('/clothing/wear-delete', [MainController::class, 'wear_delete']);


//オオタ担当場所

//モギ担当場所(タナカ修正)
use App\Http\Controllers\AuthController; // ★追加忘れずに

// --- 認証系ルート ---

// 新規登録画面の表示
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
// 新規登録処理（保存）
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// --- 修正後（AuthControllerを使う形に変更） ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// --- メイン画面（遷移先） ---
// まだカレンダー画面のルートがない場合は、仮で以下を追加しておいてください
Route::get('/main/calendar', function () {
    return 'カレンダー画面（仮）'; 
})->name('main.calendar');


// 今日のコーデ削除
Route::post('/main/deleteCoord', [MainController::class, 'deleteCoord']);
