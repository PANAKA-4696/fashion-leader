<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CoordinationController extends Controller
{
    // ==========================================
    // 1. 管理メニュー (manage)
    // ==========================================
    public function index()
    {
        return view('coord.coordination_manage');
    }

    // ==========================================
    // 2. コーデマスター保存（新規作成）
    // ==========================================
    public function createMaster()
    {
        $userId = Auth::user()->USER_ID;
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->get();

        return view('coord.coordination_save', ['wears' => $wears]);
    }

    public function storeMaster(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        
        // 1. バリデーション
        $request->validate([
            'clothing_ids' => 'required|array|min:1', 
        ]);

        // 2. 画像保存
        $imagePath = null;
        if ($request->hasFile('coord_image')) {
            $imagePath = $request->file('coord_image')->store('coords', 'public');
        }

        // 3. タグのJSON化
        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;

        // 4. お気に入り
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        // 5. ID生成
        $codeId = 'CO' . date('ymdHis') . Str::random(2);
        
        // ※日付に依存しない汎用的な名前にする場合、入力があればそれを、なければ "Master Code" 等にする
        $defaultName = $request->input('code_name', 'マスターコーデ ' . date('Y-m-d')); // 一応日付は識別用に残すが紐付けではない

        // 6. トランザクションで保存
        DB::transaction(function () use ($userId, $codeId, $defaultName, $imagePath, $tagsJson, $isFavorite, $request) {
            
            // CODEテーブルへ保存 (ここだけ保存。日付やクローゼットとは紐付けない)
            DB::table('CODE')->insert([
                'CODE_ID'     => $codeId,
                'USER_ID'     => $userId,
                'CODE_NAME'   => $defaultName,
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

    // ==========================================
    // 3. 変更選択画面 (coord_choice)
    // ==========================================
    public function choice()
    {
        $userId = Auth::user()->USER_ID;

        // ▼▼ 修正箇所: カレンダー(TODAY_CODE)に含まれるIDを除外して取得 ▼▼
        
        // 1. カレンダーで使われているコーデIDのリストを取得
        $calendarCodeIds = DB::table('TODAY_CODE')->pluck('CODE_ID')->toArray();

        // 2. それら「以外」のコーデを取得 (これでマスターとクローゼットのコーデだけになる)
        $coords = DB::table('CODE')
            ->where('USER_ID', $userId)
            ->whereNotIn('CODE_ID', $calendarCodeIds) // ここで除外
            ->orderBy('created_at', 'desc') // 新しい順の方が見やすい
            ->get();

        return view('coord.coord_choice', ['coords' => $coords]);
    }

    // ==========================================
    // 4. 詳細編集画面 (coord_change)
    // ==========================================
    public function edit($id)
    {
        $userId = Auth::user()->USER_ID;
        
        $coord = DB::table('CODE')->where('CODE_ID', $id)->where('USER_ID', $userId)->first();
        if (!$coord) abort(404);

        $usedWearIds = DB::table('WEAR_CODE')->where('CODE_ID', $id)->pluck('WEAR_ID')->toArray();
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->get();

        return view('coord.coord_change', [
            'coord' => $coord,
            'wears' => $wears,
            'usedWearIds' => $usedWearIds
        ]);
    }

    // ▼ コーデ更新処理（編集画面からの保存）
    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID;

        $request->validate([
            'clothing_ids' => 'required|array|min:1',
        ]);

        $coord = DB::table('CODE')->where('CODE_ID', $id)->where('USER_ID', $userId)->first();
        if (!$coord) abort(404);

        // 画像処理
        $imagePath = $coord->IMAGE_PATH;
        if ($request->hasFile('coord_image')) {
            $imagePath = $request->file('coord_image')->store('coords', 'public');
        }

        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        DB::transaction(function () use ($userId, $id, $imagePath, $tagsJson, $isFavorite, $request) {
            
            // CODEテーブル更新 (Update)
            DB::table('CODE')
                ->where('CODE_ID', $id)
                ->update([
                    'IMAGE_PATH'  => $imagePath,
                    'TAGS'        => $tagsJson,
                    'IS_FAVORITE' => $isFavorite,
                    'updated_at'  => now(),
                ]);

            // 服の紐付け洗い替え
            DB::table('WEAR_CODE')->where('CODE_ID', $id)->delete();
            foreach ($request->clothing_ids as $wearId) {
                DB::table('WEAR_CODE')->insert([
                    'CODE_ID' => $id,
                    'WEAR_ID' => $wearId
                ]);
            }
        });

        return redirect()->route('coord.choice')->with('success', 'コーデを更新しました！');
    }

    // ==========================================
    // 5. 削除選択画面 (coord_delete)
    // ==========================================
    public function deleteSelect()
    {
        $userId = Auth::user()->USER_ID;
        
        // ▼▼ 修正箇所: ここもカレンダー(TODAY_CODE)を除外 ▼▼
        $calendarCodeIds = DB::table('TODAY_CODE')->pluck('CODE_ID')->toArray();

        $coords = DB::table('CODE')
            ->where('USER_ID', $userId)
            ->whereNotIn('CODE_ID', $calendarCodeIds) // 除外
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coord.coord_delete', ['coords' => $coords]);
    }

    public function destroy($id)
    {
        // 削除時は、念のため紐付けテーブルも一緒に消す
        DB::transaction(function () use ($id) {
            DB::table('WEAR_CODE')->where('CODE_ID', $id)->delete();
            DB::table('CLOSET_CODE')->where('CODE_ID', $id)->delete(); // クローゼット紐付けがあれば消す
            // TODAY_CODEはこのコントローラーでは対象外だが、念のため消しても良い
            // DB::table('TODAY_CODE')->where('CODE_ID', $id)->delete();
            
            DB::table('CODE')->where('CODE_ID', $id)->delete();
        });

        return redirect()->route('coord.delete')->with('success', 'コーデを削除しました。');
    }

    // ==========================================
    // 6. クローゼット登録・変更 (closet_entry_or_change用)
    // ==========================================
    // ※この store メソッドはクローゼット用です
    public function store(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        $closetId = $request->input('closet_id'); 

        $request->validate([
            'closet_id' => 'required',
            'clothing_ids' => 'required|array|min:1', 
        ]);

        DB::transaction(function () use ($request, $userId, $closetId) {
            
            $existingLink = DB::table('CLOSET_CODE')
                ->where('CLOSET_ID', $closetId)
                ->first();

            if ($existingLink) {
                // 更新 (Update)
                $codeId = $existingLink->CODE_ID;
                $updateData = ['updated_at' => now()];
                if ($request->hasFile('coord_image')) {
                    $updateData['IMAGE_PATH'] = $request->file('coord_image')->store('coords', 'public');
                }
                if ($request->filled('code_name')) {
                    $updateData['CODE_NAME'] = $request->input('code_name');
                }

                DB::table('CODE')->where('CODE_ID', $codeId)->update($updateData);

                DB::table('WEAR_CODE')->where('CODE_ID', $codeId)->delete();
                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID' => $codeId,
                        'WEAR_ID' => $wearId
                    ]);
                }

            } else {
                // 新規 (Insert)
                $codeId = 'CO' . date('ymdHis') . Str::random(2);
                
                $imagePath = null;
                if ($request->hasFile('coord_image')) {
                    $imagePath = $request->file('coord_image')->store('coords', 'public');
                }

                $codeName = $request->input('code_name', 'Code ' . date('Y-m-d'));

                DB::table('CODE')->insert([
                    'CODE_ID'    => $codeId,
                    'CODE_NAME'  => $codeName,
                    'IMAGE_PATH' => $imagePath,
                    'USER_ID'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('CLOSET_CODE')->insert([
                    'CLOSET_ID' => $closetId,
                    'CODE_ID'   => $codeId
                ]);

                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID' => $codeId,
                        'WEAR_ID' => $wearId
                    ]);
                }
            }
        });

        return redirect()
            ->route('closet.view', ['id' => $closetId])
            ->with('success', 'クローゼットの内容を保存しました！');
    }
}