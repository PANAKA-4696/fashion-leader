<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// --- ここから下を追加・修正 ---
use Illuminate\Support\Facades\DB;  // DB用
use Illuminate\Support\Str;         // Str用
use App\Models\Code;                // Codeモデル用
use App\Models\Tag;                 // Tagモデル用
// ----------------------------

class CoordinationController extends Controller
{
    /**
     * コーデ追加画面を表示する
     */
    public function create()
    {
        // resources/views/coord/code_add.blade.php を呼び出す
        return view('coord.code_add');
    }

    // コーデマスター保存画面を表示
    public function createMaster()
    {
        // resources/views/coord/cordination_save.blade.php を表示
        return view('coord.cordination_save');
    }

    /**
     * コーデマスターをDBに保存
     */
    public function storeMaster(Request $request)
    {
        $userId = 'US000001'; // 実際はAuth::id()などで取得

        DB::transaction(function () use ($request, $userId) {
            
            // 1. CODE_IDの生成 (CO + USER_ID + 6桁連番)
            $latest = Code::where('USER_ID', $userId)->orderBy('CODE_ID', 'desc')->first();
            $num = $latest ? (int)substr($latest->CODE_ID, -6) + 1 : 1;
            $codeId = 'CO' . $userId . str_pad($num, 6, '0', STR_PAD_LEFT);

            // 2. 画像の保存
            $imagePath = $request->hasFile('coord_image') ? 
                        $request->file('coord_image')->store('coords/masters', 'public') : null;

            // 3. CODEテーブル（親）への保存
            $code = Code::create([
                'CODE_ID'    => $codeId,
                'CODE_NAME'  => $request->input('code_name', 'マスターコーデ'),
                'IMAGE_PATH' => $imagePath,
                'USER_ID'    => $userId,
            ]);

            // 4. WEAR_CODE（中間テーブル：服との紐付け）への保存
            if ($request->clothing_ids) {
                // $code->wears() リレーションを使用して中間テーブルにインサート
                $code->wears()->attach($request->clothing_ids);
            }

            // 5. TAGテーブルへの保存
            if ($request->tags) {
                // 画面から送られてきたカンマ区切りの文字列を配列にする
                $tagNames = explode(',', $request->tags);
                foreach ($tagNames as $name) {
                    if (trim($name)) {
                        Tag::create([
                            'TAG_ID'   => 'TG' . Str::random(14),
                            'TAG_NAME' => trim($name),
                            'USER_ID'  => $userId,
                            'CODE_ID'  => $codeId
                        ]);
                    }
                }
            }

            // 6. FAVORITEテーブルへの保存 (お気に入りチェック時)
            if ($request->has('is_favorite')) {
                DB::table('FAVORITE')->insert([
                    'USER_ID' => $userId,
                    'CODE_ID' => $codeId
                ]);
            }
        });

        // 保存後は管理画面などにリダイレクト
        return redirect('/coord/manage')->with('success', 'マスターコーデを登録しました！');
    }

    public function editMaster()
    {
        // resources/views/coord/coordination_change.blade.php を表示
        return view('coord.coordination_change');
    }

    public function store(Request $request)
    {
        $userId = 'US000001'; // 実際は Auth::id()
        $closetId = $request->input('closet_id'); // 画面から送られてきたクローゼットID

        DB::transaction(function () use ($request, $userId, $closetId) {
            
            // 1. CODE_IDの生成 (CO + USER_ID + 6桁連番)
            $latest = Code::where('USER_ID', $userId)->orderBy('CODE_ID', 'desc')->first();
            $num = $latest ? (int)substr($latest->CODE_ID, -6) + 1 : 1;
            $codeId = 'CO' . $userId . str_pad($num, 6, '0', STR_PAD_LEFT);

            // 2. 画像の保存
            $imagePath = $request->hasFile('coord_image') ? 
                        $request->file('coord_image')->store('coords', 'public') : null;

            // 3. CODEテーブルに保存
            $code = Code::create([
                'CODE_ID'    => $codeId,
                'CODE_NAME'  => $request->input('code_name', 'マイコーデ'),
                'IMAGE_PATH' => $imagePath,
                'USER_ID'    => $userId,
            ]);

            // 4. クローゼットとの紐付け (CLOSET_CODE) ★ここが今回の重要ポイント
            DB::table('CLOSET_CODE')->insert([
                'CLOSET_ID' => $closetId,
                'CODE_ID'   => $codeId
            ]);

            // 5. 服との紐付け (WEAR_CODE)
            if ($request->clothing_ids) {
                $code->wears()->attach($request->clothing_ids);
            }

            // 6. タグの保存
            if ($request->tags_data) {
                $tags = explode(',', $request->tags_data);
                foreach ($tags as $tagName) {
                    Tag::create([
                        'TAG_ID'   => 'TG' . Str::random(14),
                        'TAG_NAME' => $tagName,
                        'USER_ID'  => $userId,
                        'CODE_ID'  => $codeId
                    ]);
                }
            }

            // 7. お気に入りの保存 (FAVORITE)
            if ($request->has('is_favorite')) {
                DB::table('FAVORITE')->updateOrInsert([
                    'USER_ID' => $userId,
                    'CODE_ID' => $codeId
                ]);
            }
        });

        return redirect('/closet/view')->with('success', 'クローゼットにコーデを追加しました');
    }

    // 今後、保存処理などを作る場合はここに追記していきます
}