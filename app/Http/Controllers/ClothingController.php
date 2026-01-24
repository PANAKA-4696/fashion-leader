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
            // ↓ ここを clothing_image に変更
            'clothing_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();
        $userId = $user->USER_ID;

        // 2. 自動連番の生成処理
        $lastWear = Wear::where('USER_ID', $userId)
            ->orderBy('WEAR_ID', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastWear) {
            $lastNumber = intval(substr($lastWear->WEAR_ID, -6));
            $nextNumber = $lastNumber + 1;
        }

        $seqStr = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $wearId = 'W' . $userId . $seqStr;


        // 3. 画像の保存処理
        $imagePath = null;
        
        // ↓ ここも clothing_image に変更！
        if ($request->hasFile('clothing_image')) {
            $file = $request->file('clothing_image'); // ↓ ここも
            $extension = $file->getClientOriginalExtension();
            
            $fileName = 'WIMG' . $userId . $seqStr . '.' . $extension;

            // フォルダがなければここで自動作成されます
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