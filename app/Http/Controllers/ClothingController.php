<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clothing;
use Illuminate\Support\Facades\Storage;

class ClothingController extends Controller
{
    /**
     * 服の追加フォーム表示
     */
    public function create()
    {
        return view('clothing.clothing_add');
    }

    /**
     * 服をデータベースに保存
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'clothing_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string',
            'tags' => 'nullable|string',
            'is_favorite' => 'nullable|in:0,1',
        ]);

        // 画像の保存
        $imagePath = null;
        if ($request->hasFile('clothing_image')) {
            $file = $request->file('clothing_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('clothing', $filename, 'public');
        }

        // タグの配列化
        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
        }

        // 服の保存
        Clothing::create([
            'name' => $validated['category'],
            'category' => $validated['category'],
            'tags' => json_encode($tags),
            'image_path' => $imagePath,
            'is_favorite' => $request->has('is_favorite') ? true : false,
        ]);

        // 服管理画面にリダイレクト
        return redirect('/clothing/wear-screen')->with('success', '服をマスターに追加しました。');
    }

    /**
     * 服を削除
     */
    public function destroy($id)
    {
        $clothing = Clothing::findOrFail($id);
        
        // 画像ファイルの削除
        if ($clothing->image_path && Storage::disk('public')->exists($clothing->image_path)) {
            Storage::disk('public')->delete($clothing->image_path);
        }
        
        // データベースから削除
        $clothing->delete();
        
        // 服管理画面にリダイレクト
        return redirect('/clothing/wear-screen')->with('success', '服をマスターから削除しました。');
    }

    /**
     * 服の編集（画像とメタ情報の更新）
     */
    public function update(Request $request, $id)
    {
        $clothing = Clothing::findOrFail($id);

        // バリデーション
        $validated = $request->validate([
            'clothing_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string',
            'tags' => 'nullable|string',
            'is_favorite' => 'nullable|in:0,1',
        ]);

        // 新しい画像がある場合、古い画像を削除して新しいものを保存
        if ($request->hasFile('clothing_image')) {
            if ($clothing->image_path && Storage::disk('public')->exists($clothing->image_path)) {
                Storage::disk('public')->delete($clothing->image_path);
            }
            
            $file = $request->file('clothing_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('clothing', $filename, 'public');
            $clothing->image_path = $imagePath;
        }

        // タグの配列化
        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
        }

        // 情報を更新
        $clothing->update([
            'name' => $validated['category'],
            'category' => $validated['category'],
            'tags' => json_encode($tags),
            'is_favorite' => $request->has('is_favorite') ? true : false,
        ]);

        // 服管理画面にリダイレクト
        return redirect('/clothing/wear-screen')->with('success', '服の情報を更新しました。');
    }
}
