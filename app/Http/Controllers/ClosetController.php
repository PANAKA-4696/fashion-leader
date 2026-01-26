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
        $closets = DB::table('CLOSET')->where('USER_ID', $userId)->get();

        return view('closet.closet_main', ['closets' => $closets]);
    }

    // ==========================================
    // 2. クローゼット詳細 (view)
    // ==========================================
    public function show($id)
    {
        $userId = Auth::user()->USER_ID;

        // A. クローゼット情報の取得
        $closet = DB::table('CLOSET')
            ->where('CLOSET_ID', $id)
            ->where('USER_ID', $userId)
            ->first();

        if (!$closet) abort(404);

        // B. クローゼット内のコーデ一覧を取得 (CLOSET_CODE -> CODE)
        $codes = DB::table('CLOSET_CODE')
            ->join('CODE', 'CLOSET_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CLOSET_CODE.CLOSET_ID', $id)
            ->select('CODE.*')
            ->get();

        // C. 各コーデに紐付く服データを取得してセット
        // (N+1対策: コーデIDリストを作って一括取得)
        $codeIds = $codes->pluck('CODE_ID');
        
        $wearsGrouped = DB::table('WEAR_CODE')
            ->join('WEAR', 'WEAR_CODE.WEAR_ID', '=', 'WEAR.WEAR_ID')
            ->whereIn('WEAR_CODE.CODE_ID', $codeIds)
            ->select('WEAR_CODE.CODE_ID', 'WEAR.*')
            ->get()
            ->groupBy('CODE_ID');

        // データ結合
        foreach ($codes as $code) {
            // 服データ
            $code->wears = $wearsGrouped->get($code->CODE_ID, collect([]));
            
            // タグデータ (JSON文字列を配列に戻す)
            // ※タグはCODEテーブルのTAGSカラム(JSON)に入っている想定です
            $tagsArray = [];
            if ($code->TAGS) {
                $decoded = json_decode($code->TAGS, true);
                if (is_array($decoded)) {
                    $tagsArray = $decoded;
                }
            }
            $code->tags_array = $tagsArray;
        }

        // クローゼット自体のタグ（もしあれば）
        // 現状のDB設計だとクローゼット自体にはタグがないかもしれませんが、
        // 念のため view 側でエラーにならないよう空文字などを渡します
        $closet_tags = ''; 

        return view('closet.closet_view', [
            'closet' => $closet,
            'codes'  => $codes,
            'closet_tags' => $closet_tags
        ]);
    }

    // ==========================================
    // 3. クローゼット追加 (add)
    // ==========================================
    public function create()
    {
        return view('closet.closet_add');
    }

    public function store(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        $request->validate(['closet_name' => 'required']);

        $closetId = 'CL' . date('ymdHis') . Str::random(2);

        DB::table('CLOSET')->insert([
            'CLOSET_ID'   => $closetId,
            'USER_ID'     => $userId,
            'CLOSET_NAME' => $request->closet_name,
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

        return view('closet.closet_edit', ['closet' => $closet]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::user()->USER_ID;
        $request->validate(['closet_name' => 'required']);

        DB::table('CLOSET')
            ->where('CLOSET_ID', $id)
            ->where('USER_ID', $userId)
            ->update([
                'CLOSET_NAME' => $request->closet_name,
                'updated_at'  => now(),
            ]);

        return redirect()->route('closet.view', ['id' => $id])->with('success', 'クローゼット名を変更しました！');
    }

    // ==========================================
    // 5. コーデ削除 (紐付け解除)
    // ==========================================
    public function removeCoord(Request $request)
    {
        // どのクローゼットから消すかは、URLパラメータかHidden値で受け取る必要がありますが、
        // ひとまず CODE_ID だけで紐付けテーブルから削除します
        // (厳密には CLOSET_ID も条件に入れたほうが安全です)
        
        $codeId = $request->input('code_id');
        
        // 紐付け解除
        DB::table('CLOSET_CODE')->where('CODE_ID', $codeId)->delete();
        
        // ※必要であれば、紐付けがなくなった CODE 本体を消す処理も入れられますが、
        // クローゼットの場合は「紐付けだけ外す」が一般的かもしれません。
        // 今回は前例(カレンダー)に合わせて、本体も消すなら以下の行を追加します：
        DB::table('WEAR_CODE')->where('CODE_ID', $codeId)->delete();
        DB::table('CODE')->where('CODE_ID', $codeId)->delete();

        return redirect()->back()->with('success', 'コーデを削除しました。');
    }
}