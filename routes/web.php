<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\CoordinationController;

Route::get('/', function () {
    return view('welcome');
});

// コーデ管理
Route::get('/coord/add', [CoordinationController::class, 'create']);
Route::get('/coord/save', [CoordinationController::class, 'createMaster']);

// カレンダー画面
Route::get('/main/calendar', [MainController::class, 'calendar']);

// 今日のコーデ確認画面
Route::get('/main/closet_clothes', [MainController::class, 'closet_clothes']);

// 今日のコーデ編集画面（← アンダーバーに統一）
Route::get('/main/closet_edit', [MainController::class, 'closet_edit']);
Route::post('/main/closet_edit', [MainController::class, 'saveCoord']);

// カレンダー API
Route::get('/api/calendar-status', [MainController::class, 'getMonthlyStatus']);

// 今日のコーデ取得 API
Route::get('/api/coord', [MainController::class, 'getCoordData']);

// 今日のコーデ削除
Route::post('/main/deleteCoord', [MainController::class, 'deleteCoord']);
