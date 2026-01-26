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

    // ▼ 名前・タグ・日付の順で名前を決める保存処理
    public function storeMaster(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        
        $request->validate([
            'clothing_ids' => 'required|array|min:1', 
        ]);

        // 画像保存
        $imagePath = null;
        if ($request->hasFile('coord_image')) {
            $imagePath = $request->file('coord_image')->store('coords', 'public');
        }

        // タグ整形
        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        // ID発行
        $codeId = 'CO' . date('ymdHis') . Str::random(2);
        
        // ▼▼ 名前決定ロジック ▼▼
        $inputName = $request->input('code_name');
        
        if ($inputName) {
            // 1. 入力があればそれ
            $codeName = $inputName;
        } elseif ($tagsInput) {
            // 2. なければ、最初のタグ + (コーデ)
            $firstTag = explode(',', $tagsInput)[0];
            $codeName = $firstTag . ' (コーデ)'; 
        } else {
            // 3. それもなければ日付
            $codeName = date('Y-m-d') . 'の保存コーデ';
        }
        // ▲▲ ここまで ▲▲

        DB::transaction(function () use ($userId, $codeId, $codeName, $imagePath, $tagsJson, $isFavorite, $request) {
            
            // CODEテーブルへ保存 (日付紐付けなし)
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

            // 服の紐付け
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

        // 1. カレンダー(TODAY_CODE)に含まれるIDを除外リストとして取得
        $calendarCodeIds = DB::table('TODAY_CODE')->pluck('CODE_ID')->toArray();

        // 2. マスターコーデ一覧を取得
        $coords = DB::table('CODE')
            ->where('USER_ID', $userId)
            ->whereNotIn('CODE_ID', $calendarCodeIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. 各コーデに紐付いている「服データ」を一括取得
        // (N+1問題を避けるため、ループ内でクエリを投げずに一括で取ります)
        $codeIds = $coords->pluck('CODE_ID'); // 表示するコーデのIDリスト

        $wearsGrouped = DB::table('WEAR_CODE')
            ->join('WEAR', 'WEAR_CODE.WEAR_ID', '=', 'WEAR.WEAR_ID')
            ->whereIn('WEAR_CODE.CODE_ID', $codeIds)
            ->select('WEAR_CODE.CODE_ID', 'WEAR.WEAR_ID', 'WEAR.ITEM_NAME', 'WEAR.CATEGORY', 'WEAR.IMAGE_PATH')
            ->get()
            ->groupBy('CODE_ID'); // CODE_IDごとにまとめる

        // 4. コーデデータに服データをくっつける
        foreach ($coords as $coord) {
            // そのコーデIDに対応する服リストがあればセット、なければ空のコレクション
            $coord->wears = $wearsGrouped->get($coord->CODE_ID, collect([]));
        }

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

    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID;
        $request->validate(['clothing_ids' => 'required|array|min:1']);

        $coord = DB::table('CODE')->where('CODE_ID', $id)->where('USER_ID', $userId)->first();
        if (!$coord) abort(404);

        $imagePath = $coord->IMAGE_PATH;
        if ($request->hasFile('coord_image')) {
            $imagePath = $request->file('coord_image')->store('coords', 'public');
        }

        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;
        $isFavorite = $request->has('is_favorite') ? 1 : 0;
        
        // 名前も更新できるようにする場合はここで処理しますが、
        // 現状の要件では触れていないので一旦保留（必要なら追加してください）

        DB::transaction(function () use ($userId, $id, $imagePath, $tagsJson, $isFavorite, $request) {
            DB::table('CODE')
                ->where('CODE_ID', $id)
                ->update([
                    'IMAGE_PATH'  => $imagePath,
                    'TAGS'        => $tagsJson,
                    'IS_FAVORITE' => $isFavorite,
                    'updated_at'  => now(),
                ]);

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
        
        // カレンダー除外
        $calendarCodeIds = DB::table('TODAY_CODE')->pluck('CODE_ID')->toArray();

        $coords = DB::table('CODE')
            ->where('USER_ID', $userId)
            ->whereNotIn('CODE_ID', $calendarCodeIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coord.coord_delete', ['coords' => $coords]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            DB::table('WEAR_CODE')->where('CODE_ID', $id)->delete();
            DB::table('CLOSET_CODE')->where('CODE_ID', $id)->delete();
            DB::table('CODE')->where('CODE_ID', $id)->delete();
        });

        return redirect()->route('coord.delete')->with('success', 'コーデを削除しました。');
    }

    // ==========================================
    // 6. クローゼット用 (store)
    // ==========================================
    public function store(Request $request)
    {
        // ... (以前渡した、クローゼット登録用のコード。今回は省略しますが必要なら追加します) ...
        // ※Masterとは関係ないですが、ファイル内には存在させておいてください
        
        return redirect()->back(); // 仮置き
    }
    // (※必要であれば前回のクローゼット用storeメソッドをここに貼り付けてください)
}