<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClosetController extends Controller
{
    public function index()
    {
        // 先ほど作成した resources/views/closet/closet_main.blade.php を表示
        return view('closet.closet_main');
    }

    public function show($id)
    {
        // DBの代わりに仮のデータを作成
        $closet_name = "クローゼットA";
        $closet_tags = "仕事用, お気に入り, 冬物";

        // コーデ一覧のダミーデータ
        $dummy_coords = [
            [
                'name' => 'No.1 コーディネート',
                'is_favorite' => true,
                'tags' => '春のカジュアル, デート',
                'img_full' => 'images/sample_full.jpg',
                'img_shirt' => 'images/sample_shirt.jpg',
                'img_pants' => 'images/sample_pants.jpg',
                'img_shoes' => 'images/sample_shoes.jpg',
            ],
            [
                'name' => 'No.2 コーディネート',
                'is_favorite' => false,
                'tags' => '春のフォーマル, 仕事',
                'img_full' => 'images/sample_shirt2.jpg',
                'img_shirt' => 'images/sample_shirt2.jpg',
                'img_pants' => 'images/sample_pants2.jpg',
                'img_shoes' => 'images/sample_shoes2.jpg',
                'img_acc' => 'images/sample_acc.jpg',
            ],
        ];

        return view('closet.closet_view', compact('closet_name', 'closet_tags', 'dummy_coords'));
    }

    // ClosetController.php の中身

    public function edit($id)
    {
        // 今はレイアウト確認用なので、中身は空っぽで大丈夫です
        return "ここは編集画面（レイアウト未作成）です。ID: " . $id;
    }
}