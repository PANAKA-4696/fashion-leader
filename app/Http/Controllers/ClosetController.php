<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Closet;
use App\Models\Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // ID生成用
use Illuminate\Support\Facades\Auth; // ★追加

class ClosetController extends Controller
{
    /**
     * クローゼット一覧 (closet_main)
     */
    public function index()
    {
        $userId = Auth::user()->USER_ID; // ★変更

        // ユーザーのクローゼット一覧を取得
        $closets = Closet::where('USER_ID', $userId)->get();

        // 各クローゼットごとに「タグ」と「お気に入り」の情報をくっつける
        foreach ($closets as $closet) {
            
            // ★追加：タグを取得して文字列にする（例: "春服, デート"）
            $tags = DB::table('TAG')
                ->where('CLOSET_ID', $closet->CLOSET_ID)
                ->pluck('TAG_NAME')
                ->toArray();
            
            // 配列をカンマ区切りの文字列にして、closetオブジェクトに一時的に保存
            // タグがなければ「タグなし」や空文字にする
            $closet->tag_string = !empty($tags) ? implode(', ', $tags) : 'タグなし';


            // 既存：お気に入り判定
            $closet->is_favorite = DB::table('FAVORITE')
                ->where('USER_ID', $userId)
                ->where('CLOSET_ID', $closet->CLOSET_ID)
                ->exists();
        }

        return view('closet.closet_main', compact('closets'));
    }

    /**
     * クローゼット詳細 (closet_view)
     */
    public function show($id)
    {
        $userId = Auth::user()->USER_ID; // ★変更

        // 1. クローゼット本体を取得
        $closet = Closet::findOrFail($id);

        // 2. ★追加：このクローゼットに紐づくタグを取得し、カンマ区切りの文字列にする
        // (例: ['夏服', '仕事用'] → "夏服, 仕事用")
        $tags = DB::table('TAG')
            ->where('CLOSET_ID', $id)
            ->pluck('TAG_NAME')
            ->toArray();
        $closet_tags = implode(', ', $tags);

        // 3. このクローゼットに紐づく「コーデ」と、その中の「服」と「タグ」をまとめて取得
        $codes = $closet->codes()->with(['wears', 'tags'])->get();

        // 4. クローゼット自体がお気に入りか判定
        $isFavorite = DB::table('FAVORITE')
            ->where('USER_ID', $userId)
            ->where('CLOSET_ID', $id)
            ->exists();

        // ★ compact に 'closet_tags' を追加して画面に渡す
        return view('closet.closet_view', compact('closet', 'codes', 'isFavorite', 'closet_tags'));
    }

    /**
     * 編集画面の表示
     */
    public function edit($id)
    {
        $closet = Closet::findOrFail($id);
        
        // 現在のタグを取得して、カンマ区切りの文字列にする
        $tags = DB::table('TAG')->where('CLOSET_ID', $id)->pluck('TAG_NAME')->toArray();
        $currentTagsString = implode(', ', $tags);

        $isFavorite = DB::table('FAVORITE')
                        ->where('USER_ID', Auth::user()->USER_ID) // ★変更
                        ->where('CLOSET_ID', $id)
                        ->exists();

        return view('closet.closet_edit', compact('closet', 'isFavorite', 'currentTagsString'));
    }

    /**
     * 編集内容の保存 (No.9)
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID; // ★変更

        DB::transaction(function () use ($request, $id, $userId) {
            // 1. クローゼット本体の更新
            $closet = Closet::findOrFail($id);
            $closet->update([
                'CLOSET_NAME' => $request->new_closet_name,
                // 設計図通り、CLOSETにはtimestampsがあるのでここは自動で動きます
            ]);

            // 2. タグの更新（TAGテーブルには timestamps がないので含めない）
            DB::table('TAG')->where('CLOSET_ID', $id)->delete();

            if ($request->filled('new_tag')) {
                $tags = explode(',', $request->new_tag);
                foreach ($tags as $tagName) {
                    DB::table('TAG')->insert([
                        'TAG_ID'    => 'TG' . date('ymdHis') . Str::random(2),
                        'TAG_NAME'  => trim($tagName),
                        'USER_ID'   => $userId,
                        'CLOSET_ID' => $id,
                        // created_at は入れない
                    ]);
                }
            }

            // 3. お気に入り状態の更新（FAVORITEテーブルにも timestamps がないので修正）
            if ($request->has('is_favorite')) {
                // 第2引数の更新内容を空 [] にするか、USER_IDなどを指定します
                DB::table('FAVORITE')->updateOrInsert(
                    ['USER_ID' => $userId, 'CLOSET_ID' => $id],
                    ['USER_ID' => $userId] // 更新する値がないので自分自身を指定（エラー回避）
                );
            } else {
                DB::table('FAVORITE')
                    ->where('USER_ID', $userId)
                    ->where('CLOSET_ID', $id)
                    ->delete();
            }
        });

        return redirect()->route('closet.view', ['id' => $id])->with('success', 'クローゼットを更新しました');
    }

    /**
     * クローゼットからコーデを削除（紐付け解除）
     */
    public function removeCoord(Request $request)
    {
        // 送られてきたコーデIDを取得
        $codeId = $request->input('code_id');
        
        // 中間テーブル「CLOSET_CODE」から、そのコーデの紐付けを削除
        // 注意：コーデ（CODEマスタ）自体は消さず、紐付けだけを消します
        DB::table('CLOSET_CODE')
            ->where('CODE_ID', $codeId)
            ->delete();

        // 元の画面（詳細画面）に戻る
        return back()->with('success', 'コーディネートをクローゼットから外しました');
    }

    /**
     * 追加画面の表示
     */
    public function create()
    {
        return view('closet.closet_add');
    }

    /**
     * 保存処理 (closet_add からの送信)
     */
    public function store(Request $request)
    {
        // 1. 入力チェック
        $request->validate([
            'closet_name' => 'required|max:255',
        ]);

        $userId = Auth::user()->USER_ID; // ★変更

        // トランザクションでまとめて保存（失敗したらロールバック）
        DB::transaction(function () use ($request, $userId) {
            
            // 2. IDの生成
            $newId = 'CL' . date('ymdHis') . Str::random(2);

            // 3. CLOSETテーブルへ保存
            Closet::create([
                'CLOSET_ID'   => $newId,
                'CLOSET_NAME' => $request->closet_name,
                'CLOSET_INFO' => null, // 説明欄はなくなったのでnull
                'USER_ID'     => $userId,
            ]);

            // 4. TAGテーブルへ保存（ここを追加）
            if ($request->filled('new_tag')) {
                $tags = explode(',', $request->new_tag);
                
                foreach ($tags as $index => $tagName) {
                    // ループ処理が高速だとIDが重複する恐れがあるため、ランダム文字数を少し増やすか工夫
                    // 'TG'(2) + 日時(12) + ランダム(2) = 16文字
                    $tagId = 'TG' . date('ymdHis') . Str::random(2);
                    
                    DB::table('TAG')->insert([
                        'TAG_ID'    => $tagId,
                        'TAG_NAME'  => trim($tagName),
                        'USER_ID'   => $userId,
                        'CLOSET_ID' => $newId, // 作成したばかりのクローゼットIDと紐付け
                    ]);
                }
            }
        });

        // 4. 一覧画面へ戻る
        return redirect()->route('closet.main')->with('success', 'クローゼットを作成しました！');
    }
}