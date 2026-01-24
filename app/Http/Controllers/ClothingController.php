<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // ファイル操作用

class ClothingController extends Controller
{
    // 服を保存する処理
    public function store(Request $request)
    {
        // 1. バリデーション
        $request->validate([
            'category' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 容量制限(5MB)など
        ]);

        $user = Auth::user();
        $userId = $user->USER_ID;

        // 2. 自動連番の生成処理
        // DBから「このユーザーの、最後のWEAR_ID」を取得する
        // ID形式: W + US000001 + 000001 (計15文字)
        // 最後の6文字が連番部分
        
        $lastWear = Wear::where('USER_ID', $userId)
            ->orderBy('WEAR_ID', 'desc') // IDの降順（大きい順）で並べて
            ->first(); // 最初の一つ（つまり最新）を取る

        $nextNumber = 1; // データが何もない場合は 1 からスタート

        if ($lastWear) {
            // 既存データがある場合、IDの後ろ6文字を切り取って数字に変換し、+1する
            // substr(文字列, -6) で末尾6文字を取得
            $lastNumber = intval(substr($lastWear->WEAR_ID, -6));
            $nextNumber = $lastNumber + 1;
        }

        // 6桁になるように0埋めする (例: 1 -> "000001")
        $seqStr = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // IDの完成: W + US000001 + 000001
        $wearId = 'W' . $userId . $seqStr;


        // 3. 画像の保存処理 (ファイル名指定)
        $imagePath = null;
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension(); // 拡張子を取得 (jpg, pngなど)
            
            // ファイル名: WIMG + US000001 + 000001 . jpg
            $fileName = 'WIMG' . $userId . $seqStr . '.' . $extension;

            // storeAs を使うとファイル名を指定して保存できます
            // 保存場所: storage/app/public/wear/ファイル名
            $path = $file->storeAs('wear', $fileName, 'public');
            
            $imagePath = $path;
        }

        // 4. データベース登録
        Wear::create([
            'WEAR_ID'    => $wearId,
            'USER_ID'    => $userId,
            'ITEM_NAME'  => $request->input('name') ?? $request->input('category'),
            'CATEGORY'   => $request->input('category'),
            'IMAGE_PATH' => $imagePath,
        ]);

        return redirect('/clothing/wear-screen')->with('success', '服を登録しました！');
    }

    // 服を削除する処理
    public function destroy($id)
    {
        $wear = Wear::where('WEAR_ID', $id)
                    ->where('USER_ID', Auth::user()->USER_ID)
                    ->firstOrFail();

        // 実際の画像ファイルも削除する（ゴミを残さない）
        if ($wear->IMAGE_PATH) {
            Storage::disk('public')->delete($wear->IMAGE_PATH);
        }

        $wear->delete();

        return redirect('/clothing/wear-screen')->with('success', '服を削除しました。');
    }
}