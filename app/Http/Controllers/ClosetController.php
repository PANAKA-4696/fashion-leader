<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClosetController extends Controller
{
    // ==========================================
    // 1. クローゼット一覧 (index)
    // ==========================================
    public function index()
    {
        $userId = Auth::user()->USER_ID;
        $closets = DB::table('CLOSET')
            ->where('USER_ID', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($closets as $closet) {
            $closet->is_favorite = isset($closet->IS_FAVORITE) ? $closet->IS_FAVORITE : false;
            
            // ▼▼ タグを配列として準備 ▼▼
            $closet->tags_array = [];
            if (isset($closet->TAGS) && $closet->TAGS) {
                $decoded = json_decode($closet->TAGS, true);
                if (is_array($decoded)) {
                    $closet->tags_array = $decoded;
                }
            }
        }

        // 一覧画面 (ファイル名は環境に合わせて closet_view または closet_main)
        return view('closet.closet_view', ['closets' => $closets]);
    }

    // ==========================================
    // 2. クローゼット詳細 (show)
    // ==========================================
    public function show($id)
    {
        $userId = Auth::user()->USER_ID;

        $closet = DB::table('CLOSET')
            ->where('CLOSET_ID', $id)
            ->where('USER_ID', $userId)
            ->first();

        if (!$closet) abort(404);

        // ▼▼ クローゼット自体のタグを配列化 ▼▼
        $closet->tags_array = [];
        if (isset($closet->TAGS) && $closet->TAGS) {
            $decoded = json_decode($closet->TAGS, true);
            if (is_array($decoded)) {
                $closet->tags_array = $decoded;
            }
        }

        // コーデ一覧
        $codes = DB::table('CLOSET_CODE')
            ->join('CODE', 'CLOSET_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CLOSET_CODE.CLOSET_ID', $id)
            ->select('CODE.*')
            ->get();

        // 服データ取得
        $codeIds = $codes->pluck('CODE_ID');
        $wearsGrouped = DB::table('WEAR_CODE')
            ->join('WEAR', 'WEAR_CODE.WEAR_ID', '=', 'WEAR.WEAR_ID')
            ->whereIn('WEAR_CODE.CODE_ID', $codeIds)
            ->select('WEAR_CODE.CODE_ID', 'WEAR.*')
            ->get()
            ->groupBy('CODE_ID');

        foreach ($codes as $code) {
            $code->wears = $wearsGrouped->get($code->CODE_ID, collect([]));
            $code->tags_array = [];
            if ($code->TAGS) {
                $decoded = json_decode($code->TAGS, true);
                if (is_array($decoded)) $code->tags_array = $decoded;
            }
        }

        // 詳細画面
        return view('closet.closet_main', [
            'closet' => $closet,
            'codes'  => $codes
        ]);
    }

    // --- 他のメソッド(create, store, edit, update, removeCoord)は変更なし ---
    // (先ほど修正した store メソッドなどはそのまま残しておいてください)
    public function create() { return view('closet.closet_add'); }
    
    public function store(Request $request) {
        $userId = Auth::user()->USER_ID;
        $request->validate(['closet_name' => 'required']);
        $closetId = 'CL' . date('ymdHis') . Str::random(2);
        
        $tagsInput = $request->input('new_tag'); 
        $tagsJson = null;
        if ($tagsInput) {
            $tagsArray = explode(',', $tagsInput);
            $tagsJson = json_encode($tagsArray, JSON_UNESCAPED_UNICODE);
        }

        DB::table('CLOSET')->insert([
            'CLOSET_ID'   => $closetId,
            'USER_ID'     => $userId,
            'CLOSET_NAME' => $request->closet_name,
            'TAGS'        => $tagsJson, // ★ここの // を消して有効化！
            'IS_FAVORITE' => 0,         // ★ここの // も消して有効化！
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('closet.main')->with('success', 'クローゼットを作成しました！');
    }

    public function edit($id) {
        $userId = Auth::user()->USER_ID;
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $id)->where('USER_ID', $userId)->first();
        if (!$closet) abort(404);
        return view('closet.closet_edit', ['closet' => $closet]);
    }

    public function update(Request $request, $id) {
        $userId = Auth::user()->USER_ID;
        $request->validate(['closet_name' => 'required']);
        DB::table('CLOSET')->where('CLOSET_ID', $id)->where('USER_ID', $userId)->update([
            'CLOSET_NAME' => $request->closet_name, 'updated_at' => now(),
        ]);
        return redirect()->route('closet.view', ['id' => $id])->with('success', 'クローゼット名を変更しました！');
    }

    public function removeCoord(Request $request) {
        $codeId = $request->input('code_id');
        DB::table('CLOSET_CODE')->where('CODE_ID', $codeId)->delete();
        return redirect()->back()->with('success', 'コーデを削除しました。');
    }
}