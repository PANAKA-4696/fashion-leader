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
// コーデマスターの保存実行（フォームのaction先に指定します）
Route::post('/coord/master-store', [CoordinationController::class, 'storeMaster'])->name('coord.master.store');

// コーデマスター変更画面
Route::get('/coord/edit', [CoordinationController::class, 'editMaster']);

// コーデ追加画面の表示（クローゼットIDを渡す想定）
Route::get('/closet/{closet_id}/add-code', [CoordinationController::class, 'create'])->name('closet.code.add');
// 保存処理
Route::post('/closet/code-store', [CoordinationController::class, 'store'])->name('closet.code.store');

//フクシマ担当場所(タナカ作)
use App\Http\Controllers\ClosetController; // これが必要です！
// クローゼット一覧画面
Route::get('/closet/main', [ClosetController::class, 'index'])->name('closet.main');

// クローゼット詳細表示 (closet_view)
Route::get('/closet/view/{id}', [ClosetController::class, 'show'])->name('closet.view');

// 今回のエラーを消すために、名前だけ定義します
Route::get('/closet/edit/{id}', [ClosetController::class, 'edit'])->name('closet.edit');

// 現状：名前（name）がついていない
Route::get('/coord/add', [CoordinationController::class, 'create']);

//タナカ担当場所

//オオタ担当場所

//モギ担当場所

//クニヤス担当場所
