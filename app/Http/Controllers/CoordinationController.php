<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Auth;
// モデルを使う場合は以下も追加
use App\Models\Code; 

class CoordinationController extends Controller
{
    // ------------------------------------------------
    // 1. 管理メニュー (manage)
    // ------------------------------------------------
    public function index()
    {
        return view('coord.coordination_manage');
    }

    // ==========================================
    // ▼ コーデマスター保存（新規作成）
    // ==========================================
    public function createMaster()
    {
        $userId = Auth::user()->USER_ID;
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->get();

        // 修正: ビュー名を 'coord.coordination_save' に変更
        return view('coord.coordination_save', ['wears' => $wears]);
    }

    public function storeMaster(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        
        // 1. バリデーション
        $request->validate([
            'clothing_ids' => 'required|array|min:1', // 服は最低1つ
        ]);

        // 2. 画像保存
        $imagePath = null;
        if ($request->hasFile('coord_image')) {
            // public/storage/coords フォルダに保存
            $imagePath = $request->file('coord_image')->store('coords', 'public');
        }

        // 3. タグのJSON化
        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;

        // 4. お気に入り
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        // 5. ID生成 (CO + 日時 + ランダム)
        $codeId = 'CO' . date('ymdHis') . Str::random(2);
        
        // コーデ名 (自動生成: 2026-01-26のマスターコーデ)
        $codeName = date('Y-m-d') . 'のマスターコーデ';

        // 6. トランザクションで保存
        DB::transaction(function () use ($userId, $codeId, $codeName, $imagePath, $tagsJson, $isFavorite, $request) {
            
            // CODEテーブルへ保存
            DB::table('CODE')->insert([
                'CODE_ID'     => $codeId,
                'USER_ID'     => $userId,
                'CODE_NAME'   => $codeName,
                'IMAGE_PATH'  => $imagePath,
                'TAGS'        => $tagsJson,
                'IS_FAVORITE' => $isFavorite,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // WEAR_CODE (紐付け) テーブルへ保存
            foreach ($request->clothing_ids as $wearId) {
                DB::table('WEAR_CODE')->insert([
                    'CODE_ID' => $codeId,
                    'WEAR_ID' => $wearId
                ]);
            }
        });

        return redirect()->route('coord.manage')->with('success', 'マスターコーデを登録しました！');
    }

    // ------------------------------------------------
    // 3. 変更選択画面 (coord_choice)
    // ------------------------------------------------
    public function choice()
    {
        $userId = Auth::user()->USER_ID;
        // 登録済みのコーデ一覧を取得
        $coords = DB::table('CODE')->where('USER_ID', $userId)->get();

        return view('coord.coord_choice', ['coords' => $coords]);
    }

    // ------------------------------------------------
    // 4. 詳細編集画面 (coord_change)
    // ------------------------------------------------
    public function edit($id)
    {
        $userId = Auth::user()->USER_ID;
        
        // 指定されたコーデを取得
        $coord = DB::table('CODE')->where('CODE_ID', $id)->where('USER_ID', $userId)->first();
        if (!$coord) abort(404);

        // そのコーデに使われている服も取得
        $usedWearIds = DB::table('WEAR_CODE')->where('CODE_ID', $id)->pluck('WEAR_ID')->toArray();

        // 全ての服リスト（選択用）
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->get();

        return view('coord.coord_change', [
            'coord' => $coord,
            'wears' => $wears,
            'usedWearIds' => $usedWearIds
        ]);
    }

    public function update(Request $request, $id)
    {
        // ★ここに更新処理を書く（後ほど実装）
        return redirect()->route('coord.choice')->with('success', 'コーデを更新しました！');
    }

    // ------------------------------------------------
    // 5. 削除選択画面 (coord_delete)
    // ------------------------------------------------
    public function deleteSelect()
    {
        $userId = Auth::user()->USER_ID;
        // 登録済みのコーデ一覧を取得
        $coords = DB::table('CODE')->where('USER_ID', $userId)->get();

        return view('coord.coord_delete', ['coords' => $coords]);
    }

    public function destroy($id)
    {
        // ★ここに削除処理を書く（後ほど実装）
        DB::table('CODE')->where('CODE_ID', $id)->delete();
        // 関連テーブルの削除も必要なら追加

        return redirect()->route('coord.delete')->with('success', 'コーデを削除しました。');
    }

    // ------------------------------------------------
    // 以下、既存のクローゼット連携用メソッド（変更なし）
    // ------------------------------------------------
    public function create($closet_id = null)
    {
        $userId = Auth::user()->USER_ID;
        if (!$closet_id) {
            return redirect()->route('coord.manage');
        }
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $closet_id)->first();
        if (!$closet) abort(404);
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->orderBy('created_at', 'desc')->get();

        // ビュー名は適宜調整してください（クローゼット内追加用）
        return view('coord.code_add_in_closet', compact('closet', 'wears'));
    }

    public function store(Request $request)
    {
        // (省略: 元のコードのまま)
        // ...
        return redirect()->route('closet.view', ['id' => $request->input('closet_id')]);
    }
}