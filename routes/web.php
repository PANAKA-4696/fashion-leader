<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // 追加
use App\Http\Controllers\MainController;
use App\Http\Controllers\CoordinationController;
use App\Http\Controllers\ClosetController;
use App\Http\Controllers\ClothingController;
use App\Http\Controllers\AuthController;


// --- モギ(リーダタナカ)担当場所（authの中やトップページ） ---

// ----------------------------------------------------------------
// ▼ 1. 誰でもアクセスできるエリア（ログイン・登録・トップ）
// ----------------------------------------------------------------

// トップページ（ログイン済みならカレンダーへ、未ログインならWelcome画面へ）
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('main.calendar');
    }
    return view('welcome');
});

// 新規登録
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// ログイン
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');


// ----------------------------------------------------------------
// ▼ 2. ログインしている人だけが入れるエリア（主要機能）
// ----------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // --- クニヤス担当場所（カレンダー・メイン） ---
    
    // カレンダー画面
    Route::get('/main/calendar', [MainController::class, 'calendar'])->name('main.calendar');
    
    // カレンダー API
    Route::get('/api/calendar-status', [MainController::class, 'getMonthlyStatus']);
    
    // 今日のコーデ取得 API
    Route::get('/api/coord', [MainController::class, 'getCoordData']);
    
    // 今日のコーデ確認画面
    Route::get('/main/closet_clothes', [MainController::class, 'closet_clothes']);
    
    // 今日のコーデ編集・登録画面
    Route::get('/main/closet_edit', [MainController::class, 'closet_edit'])->name('closet.edit');
    Route::post('/main/closet_edit', [MainController::class, 'saveCoord']); // 保存処理
    
    // 今日のコーデ削除
    Route::post('/main/deleteCoord', [MainController::class, 'deleteCoord']);


    // --- 田中担当場所（服マスター管理） ---

    // 服一覧画面
    Route::get('/clothing/wear-screen', [MainController::class, 'wear_screen']);
    
    // 服の情報変更画面一覧
    Route::get('/clothing/wear-change', [MainController::class, 'wear_change']);
    
    // 服の追加画面
    Route::get('/clothing/clothing-add', [MainController::class, 'wear_add']);
    
    // 服の保存処理
    Route::post('/clothing/store', [ClothingController::class, 'store']);
    
    // 服の削除処理
    Route::delete('/clothing/{id}', [ClothingController::class, 'destroy']);
    Route::get('/clothing/wear-delete', [MainController::class, 'wear_delete']); // 削除選択画面
    
    // 服の編集画面・更新処理
    Route::get('/clothing/wear-item-change/{id}', [MainController::class, 'wear_item_change']);
    Route::put('/clothing/{id}', [ClothingController::class, 'update']);
    // (重複していたルート定義は削除しました)


    // --- フクシマ(リーダータナカ)担当場所（クローゼット管理） ---

    // クローゼット一覧
    Route::get('/closet/main', [ClosetController::class, 'index'])->name('closet.main');
    
    // クローゼット詳細
    Route::get('/closet/view/{id}', [ClosetController::class, 'show'])->name('closet.view');
    
    // クローゼット追加
    Route::get('/closet/add', [ClosetController::class, 'create'])->name('closet.add');
    Route::post('/closet/store', [ClosetController::class, 'store'])->name('closet.store');
    
    // クローゼット編集・更新
    Route::get('/closet/edit/{id}', [ClosetController::class, 'edit'])->name('closet.edit');
    Route::post('/closet/update/{id}', [ClosetController::class, 'update'])->name('closet.update');
    
    // コーデ削除（紐付け解除）
    Route::post('/closet/coord/delete', [ClosetController::class, 'removeCoord'])->name('closet.coord.delete');


    // --- リーダー田中担当場所（コーデ管理） ---

    // 2. コーデマスター保存（新規登録画面）
    Route::get('/coord/save', [CoordinationController::class, 'createMaster'])->name('coord.save');
    Route::post('/coord/store', [CoordinationController::class, 'storeMaster'])->name('coord.store'); // 保存処理

    // 4. コーデ変更（詳細編集画面 & 更新処理）
    Route::get('/coord/change/{id}', [CoordinationController::class, 'edit'])->name('coord.change');
    Route::put('/coord/update/{id}', [CoordinationController::class, 'update'])->name('coord.update');

    // クローゼット内でのコーデ追加
    Route::get('/closet/{closet_id}/add-code', [CoordinationController::class, 'create'])->name('closet.coord.add');
    Route::post('/closet/code-store', [CoordinationController::class, 'store'])->name('closet.code.store');

    // --- オオタ(リーダータナカ)担当場所（コーデ管理） ---

    // 1. コーデマスター管理（メニュー）
    Route::get('/coord/manage', [CoordinationController::class, 'index'])->name('coord.manage');

    // 3. コーデ変更（一覧から選択する画面）
    Route::get('/coord/choice', [CoordinationController::class, 'choice'])->name('coord.choice');

    // 5. コーデ削除（一覧から選択する画面 & 削除処理）
    Route::get('/coord/delete', [CoordinationController::class, 'deleteSelect'])->name('coord.delete');
    Route::delete('/coord/destroy/{id}', [CoordinationController::class, 'destroy'])->name('coord.destroy');

    
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

}); // ▲ ここで middleware group を閉じる