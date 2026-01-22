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

// 修正後（名前を追加）
Route::get('/coord/add', [CoordinationController::class, 'create'])->name('coord.add');

// クローゼットから特定のコーデを削除する（紐付けを解除する）
Route::post('/closet/coord/delete', [ClosetController::class, 'removeCoord'])->name('closet.coord.delete');

// クローゼット追加画面の表示
Route::get('/closet/add', [ClosetController::class, 'create'])->name('closet.add');

// クローゼットの保存処理
Route::post('/closet/store', [ClosetController::class, 'store'])->name('closet.store');

//タナカ担当場所

//オオタ担当場所

//モギ担当場所

//クニヤス担当場所
