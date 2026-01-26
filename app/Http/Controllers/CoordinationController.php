<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // ▼▼ 修正箇所: Facades ではなく Support を直接使います ▼▼

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
        // Support\Str を読み込んでいるので、Str::random() で実体が動きます
        $codeId = 'CO' . date('ymdHis') . Str::random(2);
        
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

    // ==========================================
    // 3. 変更選択画面 (coord_choice)
    // ==========================================
    public function choice()
    {
        $userId = Auth::user()->USER_ID;
        $coords = DB::table('CODE')->where('USER_ID', $userId)->get();

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

    // ==========================================
    // ▼ コーデ更新処理（編集画面からの保存）
    // ==========================================
    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID;

        // 1. バリデーション（服は最低1つ選択）
        $request->validate([
            'clothing_ids' => 'required|array|min:1',
        ]);

        // 2. 更新対象のコーデが存在するか、自分のものか確認
        $coord = DB::table('CODE')->where('CODE_ID', $id)->where('USER_ID', $userId)->first();
        if (!$coord) {
            abort(404); // 存在しない、または他人のデータならエラー
        }

        // 3. 画像処理
        // 新しい画像がアップロードされたらそれを保存、なければ「元のパス」を維持
        $imagePath = $coord->IMAGE_PATH; // 初期値は今の画像
        if ($request->hasFile('coord_image')) {
            // 新しい画像を保存
            $imagePath = $request->file('coord_image')->store('coords', 'public');
            
            // ※必要であれば、ここで古い画像を削除する処理を入れても良いです（Storage::delete...）
        }

        // 4. タグのJSON化
        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;

        // 5. お気に入り状態
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        // 6. トランザクションで一括更新
        DB::transaction(function () use ($userId, $id, $imagePath, $tagsJson, $isFavorite, $request) {
            
            // (A) CODEテーブル（親情報）を UPDATE
            DB::table('CODE')
                ->where('CODE_ID', $id)
                ->update([
                    'IMAGE_PATH'  => $imagePath,
                    'TAGS'        => $tagsJson,
                    'IS_FAVORITE' => $isFavorite,
                    'updated_at'  => now(), // 更新日時を現在にする
                ]);

            // (B) WEAR_CODEテーブル（服の紐付け）を更新
            // やり方: 「一度今の紐付けを全削除」→「選ばれた服を新規登録」が一番確実です。
            
            // B-1. このコーデに紐付いている服を一旦削除
            DB::table('WEAR_CODE')->where('CODE_ID', $id)->delete();

            // B-2. 送られてきた新しい服リストを登録
            foreach ($request->clothing_ids as $wearId) {
                DB::table('WEAR_CODE')->insert([
                    'CODE_ID' => $id,
                    'WEAR_ID' => $wearId
                ]);
            }
        });

        // 完了したら一覧画面（または選択画面）に戻る
        return redirect()->route('coord.choice')->with('success', 'コーデの内容を更新しました！');
    }

    // ==========================================
    // 5. 削除選択画面 (coord_delete)
    // ==========================================
    public function deleteSelect()
    {
        $userId = Auth::user()->USER_ID;
        $coords = DB::table('CODE')->where('USER_ID', $userId)->get();

        return view('coord.coord_delete', ['coords' => $coords]);
    }

    public function destroy($id)
    {
        DB::table('CODE')->where('CODE_ID', $id)->delete();
        return redirect()->route('coord.delete')->with('success', 'コーデを削除しました。');
    }

    // ==========================================
    // 6. クローゼット内でのコーデ追加 (既存機能)
    // ==========================================
    public function create($closet_id = null)
    {
        $userId = Auth::user()->USER_ID;
        if (!$closet_id) {
            return redirect()->route('coord.manage');
        }
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $closet_id)->first();
        if (!$closet) abort(404);
        $wears = DB::table('WEAR')->where('USER_ID', $userId)->orderBy('created_at', 'desc')->get();

        return view('coord.code_add_in_closet', compact('closet', 'wears'));
    }

    // ==========================================
    // 6. クローゼット登録・変更 (closet_entry_or_change.blade.php からの処理)
    // ==========================================
    public function store(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        // 画面から送られてくるクローゼットID (特定の枠のID)
        $closetId = $request->input('closet_id'); 

        // バリデーション
        $request->validate([
            'closet_id' => 'required', // これがないとどこに保存するか分からないため必須
            'clothing_ids' => 'required|array|min:1', // 服は1つ以上
        ]);

        DB::transaction(function () use ($request, $userId, $closetId) {
            
            // ▼▼ ここが修正の肝です ▼▼
            // ① まず、「このクローゼットID」に既にコーデが紐付いているかを確認します
            $existingLink = DB::table('CLOSET_CODE')
                ->where('CLOSET_ID', $closetId)
                ->first();

            if ($existingLink) {
                // --------------------------------------------------
                // 【パターンA: 既にデータがある場合 → 中身を更新 (UPDATE)】
                // --------------------------------------------------
                // 紐付いているコーデIDを取得
                $codeId = $existingLink->CODE_ID;

                // 更新するデータを準備
                $updateData = ['updated_at' => now()];
                
                // 画像が新しくアップロードされた場合のみ更新
                if ($request->hasFile('coord_image')) {
                    $updateData['IMAGE_PATH'] = $request->file('coord_image')->store('coords', 'public');
                }
                // 名前が入力されていれば更新
                if ($request->filled('code_name')) {
                    $updateData['CODE_NAME'] = $request->input('code_name');
                }

                // CODEテーブルを更新 (IDは変わらないのでDBを圧迫しません)
                DB::table('CODE')->where('CODE_ID', $codeId)->update($updateData);

                // 服の組み合わせを更新 (一度削除して登録し直す「洗い替え」方式)
                DB::table('WEAR_CODE')->where('CODE_ID', $codeId)->delete();
                
                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID' => $codeId,
                        'WEAR_ID' => $wearId
                    ]);
                }

            } else {
                // --------------------------------------------------
                // 【パターンB: まだデータがない場合 → 新規作成 (INSERT)】
                // --------------------------------------------------
                // 新しいコーデIDを発行
                $codeId = 'CO' . date('ymdHis') . \Illuminate\Support\Str::random(2);
                
                $imagePath = null;
                if ($request->hasFile('coord_image')) {
                    $imagePath = $request->file('coord_image')->store('coords', 'public');
                }

                $codeName = $request->input('code_name', 'Code ' . date('Y-m-d'));

                // CODEテーブルに新規登録
                DB::table('CODE')->insert([
                    'CODE_ID'    => $codeId,
                    'CODE_NAME'  => $codeName,
                    'IMAGE_PATH' => $imagePath,
                    'USER_ID'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // CLOSET_CODE に紐付け登録 (これでこのクローゼットIDは「使用中」になります)
                DB::table('CLOSET_CODE')->insert([
                    'CLOSET_ID' => $closetId,
                    'CODE_ID'   => $codeId
                ]);

                // WEAR_CODE に服を登録
                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID' => $codeId,
                        'WEAR_ID' => $wearId
                    ]);
                }
            }
        });

        // 処理が終わったら、クローゼット詳細画面へ戻る
        return redirect()
            ->route('closet.view', ['id' => $closetId])
            ->with('success', 'クローゼットの内容を保存しました！');
    }
}