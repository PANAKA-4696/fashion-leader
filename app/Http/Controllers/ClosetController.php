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

    // ==========================================
    // 4. クローゼット編集 (edit)
    // ==========================================
    public function edit($id)
    {
        $userId = Auth::user()->USER_ID;
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $id)->where('USER_ID', $userId)->first();
        if (!$closet) abort(404);

        // タグの準備 (JSON -> カンマ区切り文字列)
        $currentTagsString = '';
        if ($closet->TAGS) {
            $tagsArray = json_decode($closet->TAGS, true);
            if (is_array($tagsArray)) {
                $currentTagsString = implode(',', $tagsArray);
            }
        }

        // お気に入りの準備 (0か1を true/false に)
        $isFavorite = (bool)$closet->IS_FAVORITE;

        return view('closet.closet_edit', [
            'closet' => $closet,
            'currentTagsString' => $currentTagsString,
            'isFavorite' => $isFavorite
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID;
        // ビュー側の name="new_closet_name" に合わせる
        $request->validate(['new_closet_name' => 'required']);

        // タグの処理
        $tagsInput = $request->input('new_tag'); 
        $tagsJson = null;
        if ($tagsInput) {
            $tagsArray = explode(',', $tagsInput);
            $tagsJson = json_encode($tagsArray, JSON_UNESCAPED_UNICODE);
        }

        // お気に入りの処理 (チェックされていれば1, なければ0)
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        DB::table('CLOSET')
            ->where('CLOSET_ID', $id)
            ->where('USER_ID', $userId)
            ->update([
                'CLOSET_NAME' => $request->new_closet_name,
                'TAGS'        => $tagsJson,
                'IS_FAVORITE' => $isFavorite,
                'updated_at'  => now(),
            ]);

        // 詳細画面 (closet.view) へリダイレクト
        return redirect()->route('closet.view', ['id' => $id])->with('success', 'クローゼット情報を更新しました！');
    }

    public function removeCoord(Request $request) {
        $codeId = $request->input('code_id');
        DB::table('CLOSET_CODE')->where('CODE_ID', $codeId)->delete();
        return redirect()->back()->with('success', 'コーデを削除しました。');
    }

    // ==========================================
    // 6. コーデ追加画面 (新規作成 & マスター選択)
    // ==========================================
    public function addCoord($closet_id)
    {
        $userId = Auth::user()->USER_ID;
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $closet_id)->first();
        if (!$closet) abort(404);

        // 1. 新規作成用の服リスト
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->get();

        // 2. 選択用のマスターコーデリスト
        // (既にこのクローゼットに入っているコーデは除外すると親切ですが、今回は全件出します)
        $masterCoords = DB::table('CODE')
            ->where('USER_ID', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coord.code_add', [
            'closet'       => $closet,
            'wears'        => $wears,
            'masterCoords' => $masterCoords
        ]);
    }

    // ==========================================
    // 7. コーデ保存処理 (新規作成 or 紐付け)
    // ==========================================
    public function storeCode(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        $closetId = $request->input('closet_id');
        $mode = $request->input('mode'); // 'create' か 'select'

        // --- パターンA: 既存のマスターコーデを選択した場合 ---
        if ($mode === 'select') {
            $codeId = $request->input('existing_code_id');
            
            // 紐付け (CLOSET_CODE) を作成
            // ★修正: created_at, updated_at を削除しました
            DB::table('CLOSET_CODE')->insert([
                'CLOSET_ID'  => $closetId,
                'CODE_ID'    => $codeId,
            ]);
            
            return redirect()->route('closet.view', ['id' => $closetId])->with('success', 'マスターコーデを追加しました！');
        }

        // --- パターンB: 新規作成して追加する場合 ---
        if ($mode === 'create') {
            // 1. 画像保存
            $imagePath = null;
            if ($request->hasFile('coord_image')) {
                $imagePath = $request->file('coord_image')->store('coords', 'public');
            }
            
            // 2. タグJSON化
            $tagsJson = null;
            if ($request->tags_data) {
                $tagsJson = json_encode(explode(',', $request->tags_data), JSON_UNESCAPED_UNICODE);
            }

            // 3. CODEテーブルへ保存 (ここにはタイムスタンプがあるはずなので残します)
            $newCodeId = 'CO' . date('ymdHis') . Str::random(2);
            DB::table('CODE')->insert([
                'CODE_ID'    => $newCodeId,
                'USER_ID'    => $userId,
                'CODE_NAME'  => $request->code_name ?? '新規コーデ',
                'IMAGE_PATH' => $imagePath,
                'TAGS'       => $tagsJson,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. 服との紐付け (WEAR_CODE)
            // ★念のためここもタイムスタンプを削除しておきます（エラー防止）
            if ($request->clothing_ids) {
                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID'    => $newCodeId,
                        'WEAR_ID'    => $wearId,
                    ]);
                }
            }

            // 5. クローゼットとの紐付け (CLOSET_CODE)
            // ★修正: created_at, updated_at を削除しました
            DB::table('CLOSET_CODE')->insert([
                'CLOSET_ID'  => $closetId,
                'CODE_ID'    => $newCodeId,
            ]);

            return redirect()->route('closet.view', ['id' => $closetId])->with('success', '新しいコーデを作成して追加しました！');
        }
        
        return redirect()->back();
    }

    // ==========================================
    // 8. クローゼット削除 (destroy)
    // ==========================================
    public function destroy($id)
    {
        $userId = Auth::user()->USER_ID;

        // 1. まず、クローゼットの中身（コーデとの紐付け）を削除
        // ※コーデ自体(CODEテーブル)は消さずに残します
        DB::table('CLOSET_CODE')->where('CLOSET_ID', $id)->delete();

        // 2. クローゼット本体を削除
        DB::table('CLOSET')
            ->where('CLOSET_ID', $id)
            ->where('USER_ID', $userId)
            ->delete();

        // 3. 一覧画面へ戻る
        return redirect()->route('closet.main')->with('success', 'クローゼットを削除しました。');
    }
}