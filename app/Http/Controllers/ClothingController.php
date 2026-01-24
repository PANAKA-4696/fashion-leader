<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClothingController extends Controller
{
    // ▼ 登録処理
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'clothing_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();
        $userId = $user->USER_ID;

        // ID生成
        $lastWear = Wear::where('USER_ID', $userId)->orderBy('WEAR_ID', 'desc')->first();
        $nextNumber = 1;
        if ($lastWear) {
            $lastNumber = intval(substr($lastWear->WEAR_ID, -6));
            $nextNumber = $lastNumber + 1;
        }
        $seqStr = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $wearId = 'W' . $userId . $seqStr;

        // 画像保存
        $imagePath = null;
        if ($request->hasFile('clothing_image')) {
            $file = $request->file('clothing_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = 'WIMG' . $userId . $seqStr . '.' . $extension;
            $imagePath = $file->storeAs('wear', $fileName, 'public');
        }

        // ★★★ ここでタグとお気に入りを処理 ★★★
        
        // タグ: "春,夏" という文字列で来るので、カンマで区切って配列にする
        $tagsInput = $request->input('tags'); 
        $tagsArray = $tagsInput ? explode(',', $tagsInput) : [];

        // お気に入り: チェックされていれば true (1), なければ false (0)
        $isFavorite = $request->has('is_favorite');

        // 保存実行
        Wear::create([
            'WEAR_ID'     => $wearId,
            'USER_ID'     => $userId,
            'ITEM_NAME'   => $request->input('name') ?? $request->input('category'),
            'CATEGORY'    => $request->input('category'),
            'IMAGE_PATH'  => $imagePath,
            'TAGS'        => $tagsArray, 
            'IS_FAVORITE' => $isFavorite,
        ]);

        return redirect('/clothing/wear-screen')->with('success', '服を登録しました！');
    }

    // ▼ 更新処理
    public function update(Request $request, $id)
    {
        // 自分の服データを探す
        $wear = Wear::where('WEAR_ID', $id)
                    ->where('USER_ID', Auth::user()->USER_ID)
                    ->firstOrFail();

        // 1. 画像が新しくアップロードされた場合のみ差し替える
        if ($request->hasFile('clothing_image')) {
            // 古い画像を削除
            if ($wear->IMAGE_PATH) {
                Storage::disk('public')->delete($wear->IMAGE_PATH);
            }
            
            // 新しい画像を保存
            $file = $request->file('clothing_image');
            $extension = $file->getClientOriginalExtension();
            // 更新時は "WIMG_UPD_ID.拡張子" という形式にする
            $fileName = 'WIMG_UPD_' . $id . '.' . $extension;
            
            $path = $file->storeAs('wear', $fileName, 'public');
            $wear->IMAGE_PATH = $path;
        }

        // 2. その他の情報を更新
        $wear->CATEGORY = $request->input('category');
        
        // タグの更新
        $tagsInput = $request->input('tags');
        $wear->TAGS = $tagsInput ? explode(',', $tagsInput) : [];

        // お気に入りの更新
        $wear->IS_FAVORITE = $request->has('is_favorite');

        // アイテム名の更新
        if ($request->has('name')) {
            $wear->ITEM_NAME = $request->input('name');
        }

        $wear->save();

        return redirect('/clothing/wear-change')->with('success', '情報を更新しました！');
    }

    // 削除処理
    public function destroy($id)
    {
        $wear = Wear::where('WEAR_ID', $id)
                    ->where('USER_ID', Auth::user()->USER_ID)
                    ->firstOrFail();

        if ($wear->IMAGE_PATH) {
            Storage::disk('public')->delete($wear->IMAGE_PATH);
        }

        $wear->delete();

        return redirect('/clothing/wear-screen')->with('success', '服を削除しました。');
    }
}