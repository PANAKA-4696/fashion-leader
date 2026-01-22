<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // DB操作用
use Illuminate\Support\Str;         // 文字列生成用
// モデルは今回DBファサードを使うのでuseしなくても動作しますが、残しておいてもOKです

class CoordinationController extends Controller
{
    /**
     * コーデ追加画面を表示する
     * URL: /closet/{closet_id}/add-code
     */
    public function create($closet_id = null)
    {
        if (!$closet_id) {
            // 修正前：IDがない場合のビュー指定（もしここも間違っていたら直します）
            // return view('coord.code_add'); 
            
            // 修正後：実際のファイル名に合わせる
            return view('coord.code_add'); 
        }

        $userId = 'US000001';

        // （中略：クローゼット取得や服取得のロジックはそのまま）
        $closet = DB::table('CLOSET')->where('CLOSET_ID', $closet_id)->first();
        if (!$closet) abort(404);

        $wears = DB::table('WEAR')
            ->where('USER_ID', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // ★ここがエラーの原因です
        // 修正前：closetフォルダのcoord_addを探していた
        // return view('closet.coord_add', compact('closet', 'wears'));

        // 修正後：coordフォルダのcode_addを指定する
        return view('coord.code_add', compact('closet', 'wears'));
    }

    /**
     * コーデをDBに保存（クローゼットへの追加）
     * URL: /closet/code-store
     */
    public function store(Request $request)
    {
        $userId = 'US000001'; // 実際は Auth::id()
        $closetId = $request->input('closet_id'); // フォームから送られてきたID

        // 入力チェック
        $request->validate([
            'closet_id' => 'required',
            'clothing_ids' => 'required|array', // 服が1つ以上選ばれていること
        ]);

        DB::transaction(function () use ($request, $userId, $closetId) {
            
            // 1. CODE_IDの生成 (CO + 日時 + ランダム2桁 = 16桁)
            $codeId = 'CO' . date('ymdHis') . Str::random(2);

            // 2. 画像の保存
            $imagePath = null;
            if ($request->hasFile('coord_image')) {
                // storage/app/public/coords に保存
                $imagePath = $request->file('coord_image')->store('coords', 'public');
            }

            // 3. CODEテーブルに保存
            // コーデ名は仮で「Code 日付」を入れるか、フォームに入力欄があればそれを使います
            $codeName = $request->input('code_name', 'Code ' . date('Y-m-d'));

            DB::table('CODE')->insert([
                'CODE_ID'    => $codeId,
                'CODE_NAME'  => $codeName,
                'IMAGE_PATH' => $imagePath,
                'USER_ID'    => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. クローゼットとの紐付け (CLOSET_CODE)
            DB::table('CLOSET_CODE')->insert([
                'CLOSET_ID' => $closetId,
                'CODE_ID'   => $codeId
            ]);

            // 5. 服との紐付け (WEAR_CODE)
            if ($request->clothing_ids) {
                foreach ($request->clothing_ids as $wearId) {
                    DB::table('WEAR_CODE')->insert([
                        'CODE_ID' => $codeId,
                        'WEAR_ID' => $wearId
                    ]);
                }
            }

            // 6. タグの保存
            if ($request->tags_data) {
                $tags = explode(',', $request->tags_data);
                foreach ($tags as $tagName) {
                    // ID生成: TG + 日時 + ランダム2桁 = 16桁
                    $tagId = 'TG' . date('ymdHis') . Str::random(2);
                    
                    DB::table('TAG')->insert([
                        'TAG_ID'   => $tagId,
                        'TAG_NAME' => trim($tagName),
                        'USER_ID'  => $userId,
                        'CODE_ID'  => $codeId
                    ]);
                }
            }

            // 7. お気に入りの保存 (FAVORITE)
            if ($request->has('is_favorite')) {
                DB::table('FAVORITE')->insert([
                    'USER_ID' => $userId,
                    'CODE_ID' => $codeId
                ]);
            }
        });

        // ★重要：保存後はクローゼット詳細画面（ID付き）に戻る
        return redirect()
            ->route('closet.view', ['id' => $closetId])
            ->with('success', 'クローゼットにコーデを追加しました！');
    }

    // --- 以下、既存のマスタ登録系メソッド（変更なし） ---

    public function createMaster()
    {
        return view('coord.cordination_save');
    }

    public function storeMaster(Request $request)
    {
        // （既存のstoreMasterのコードをそのまま残してください）
        // ...
        return redirect('/coord/manage')->with('success', 'マスターコーデを登録しました！');
    }

    public function editMaster()
    {
        return view('coord.coordination_change');
    }
}