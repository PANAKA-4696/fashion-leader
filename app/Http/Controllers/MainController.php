<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // ★これを追加

class MainController extends Controller
{
    // ▼ カレンダー画面
    public function calendar()
    {
        return view('main.calendar_menu');
    }

    // ▼ 今日のコーデ確認画面
    public function closet_clothes(Request $request) { return view('main.closet_clothes', [ 'date' => $request->date ]); }

    // ▼ 今日のコーデ登録・変更画面
    public function closet_edit(Request $request)
    {
        // 変更後
        $userId = Auth::user()->USER_ID;

        $wears = DB::table('WEAR')
            ->where('USER_ID', $userId)
            ->get();

        return view('main.closet_entry_or_change', [
            'wears' => $wears,
            'date'  => $request->date
        ]);
    }

    // ▼ カレンダー用 API
    public function getMonthlyStatus(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        // 変更後
        $userId = Auth::user()->USER_ID;

        $rows = DB::table('CALENDAR')
            ->leftJoin('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->leftJoin('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $year)
            ->where('CALENDAR.CAL_MONTH', $month)
            ->select('CALENDAR.CAL_DATE', 'CODE.IMAGE_PATH')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            if ($row->IMAGE_PATH) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $row->CAL_DATE);
                $result[$dateStr] = [
                    'isRegistered' => true,
                    'img' => $row->IMAGE_PATH
                ];
            }
        }

        return response()->json($result);
    }

    // ▼ 今日のコーデ取得 API
    public function getCoordData(Request $request)
    {
        $date = $request->date;
        // 変更後
        $userId = Auth::user()->USER_ID;

        [$y, $m, $d] = explode('-', $date);

        $calendar = DB::table('CALENDAR')
            ->leftJoin('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->leftJoin('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $y)
            ->where('CALENDAR.CAL_MONTH', $m)
            ->where('CALENDAR.CAL_DATE', $d)
            ->select(
                'CODE.CODE_ID',
                'CODE.IMAGE_PATH as overall'
            )
            ->first();

        if (!$calendar) {
            return response()->json(null);
        }

        $wears = DB::table('WEAR_CODE')
            ->join('WEAR', 'WEAR.WEAR_ID', '=', 'WEAR_CODE.WEAR_ID')
            ->where('WEAR_CODE.CODE_ID', $calendar->CODE_ID)
            ->select('WEAR.CATEGORY', 'WEAR.IMAGE_PATH')
            ->get();

        $result = [
            'overall' => $calendar->overall,
            'shirt'   => optional($wears->firstWhere('CATEGORY', 'shirt'))->IMAGE_PATH,
            'pants'   => optional($wears->firstWhere('CATEGORY', 'pants'))->IMAGE_PATH,
            'shoes'   => optional($wears->firstWhere('CATEGORY', 'shoes'))->IMAGE_PATH,
        ];

        return response()->json($result);
    }

    // ▼ 今日のコーデ登録
    public function saveCoord(Request $request)
    {
        $date = $request->date;
        // 変更後
        $userId = Auth::user()->USER_ID;
        $wearIds = $request->clothing_ids ?? [];

        [$y, $m, $d] = explode('-', $date);

        // コーデID作成
        $codeId = 'C' . strtoupper(substr(uniqid(), -7));

        // 全体写真の保存
        $imagePath = 'images/default_coord.jpg';
        if ($request->hasFile('coord_image')) {
            $storedPath = $request->file('coord_image')->store('coord', 'public');
            $imagePath = 'storage/' . $storedPath;
        }

        // CODE 登録
        DB::table('CODE')->insert([
            'CODE_ID'   => $codeId,
            'USER_ID'   => $userId,
            'CODE_NAME' => $date . ' のコーデ',
            'IMAGE_PATH'=> $imagePath,
        ]);

        // WEAR_CODE 登録
        foreach ($wearIds as $wearId) {
            DB::table('WEAR_CODE')->insert([
                'CODE_ID' => $codeId,
                'WEAR_ID' => $wearId
            ]);
        }

        // カレンダー登録
        DB::table('CALENDAR')->updateOrInsert(
            [
                'USER_ID' => $userId,
                'CAL_YEAR' => $y,
                'CAL_MONTH' => $m,
                'CAL_DATE' => $d
            ],
            [
                'CALENDAR_ID' => uniqid('CAL')
            ]
        );

        // カレンダーID取得
        $calendarId = DB::table('CALENDAR')
            ->where('USER_ID', $userId)
            ->where('CAL_YEAR', $y)
            ->where('CAL_MONTH', $m)
            ->where('CAL_DATE', $d)
            ->value('CALENDAR_ID');

        // TODAY_CODE 登録
        DB::table('TODAY_CODE')->updateOrInsert(
            ['CALENDAR_ID' => $calendarId],
            ['CODE_ID' => $codeId]
        );

        return redirect('/main/calendar');
    }

    // ▼ 今日のコーデ削除
    public function deleteCoord(Request $request)
    {
        $date = $request->date;
        // 変更後
        $userId = Auth::user()->USER_ID;

        [$y, $m, $d] = explode('-', $date);

        $calendarId = DB::table('CALENDAR')
            ->where('USER_ID', $userId)
            ->where('CAL_YEAR', $y)
            ->where('CAL_MONTH', $m)
            ->where('CAL_DATE', $d)
            ->value('CALENDAR_ID');

        if ($calendarId) {
            DB::table('TODAY_CODE')->where('CALENDAR_ID', $calendarId)->delete();
        }

        return redirect('/main/calendar');
    }

    // 服マスター管理画面を表示する命令
    public function wear_screen()
    {
        // フィルタパラメータを取得
        $selectedCategory = request()->query('category', null);
        $selectedTag = request()->query('tag', null);
        $selectedFavorite = request()->query('favorite', null);
        
        // フィルタリングロジック
        $clothings = \App\Models\Clothing::all();
        
        // カテゴリでフィルタリング
        if ($selectedCategory) {
            $clothings = $clothings->filter(function($clothing) use ($selectedCategory) {
                return $clothing->category === $selectedCategory;
            });
        }
        
        // タグでフィルタリング
        if ($selectedTag) {
            $clothings = $clothings->filter(function($clothing) use ($selectedTag) {
                $tags = json_decode($clothing->tags, true) ?? [];
                return in_array($selectedTag, $tags);
            });
        }
        
        // お気に入りでフィルタリング
        if ($selectedFavorite === 'true') {
            $clothings = $clothings->filter(function($clothing) {
                return $clothing->is_favorite === true;
            });
        }
        
        // カテゴリ一覧を取得
        $allClothings = \App\Models\Clothing::all();
        $categories = $allClothings->pluck('category')->unique()->sort()->values();
        
        // タグ一覧を取得
        $allTags = collect();
        $allClothings->each(function($clothing) use (&$allTags) {
            $tags = json_decode($clothing->tags, true) ?? [];
            $allTags = $allTags->concat($tags);
        });
        $tags = $allTags->unique()->sort()->values();
        
        // resources/views/clothing/wear_screen.blade.php を表示せよという意味
        return view('clothing.wear_screen', [
            'clothings' => $clothings,
            'categories' => $categories,
            'tags' => $tags,
            'selectedCategory' => $selectedCategory,
            'selectedTag' => $selectedTag,
            'selectedFavorite' => $selectedFavorite,
        ]);
        
    }

    //服の情報変更画面を表示する命令
    public function wear_change()
    {
        // データベースから全ての服を取得
        $clothings = \App\Models\Clothing::all();
        // resources/views/clothing/wear_change.blade.php を表示せよという意味
        return view('clothing.wear_change', ['clothings' => $clothings]);
        
    }

    //服の情報編集画面を表示する命令
    public function wear_item_change($id)
    {
        // IDから服の情報を取得
        $clothing = \App\Models\Clothing::findOrFail($id);
        // resources/views/clothing/wear_item_change.blade.php を表示せよという意味
        return view('clothing.wear_item_change', ['clothing' => $clothing]);
    }

    //服の追加画面を表示する命令
    public function wear_add()
    {
        // resources/views/clothing/clothing_add.blade.php を表示せよという意味
        return view('clothing.clothing_add');
        
    }
    //服削除画面を表示する命令
    public function wear_delete()
    {
        // データベースから全ての服を取得
        $clothings = \App\Models\Clothing::all();
        // resources/views/clothing/wear_delete.blade.php を表示せよという意味
        return view('clothing.wear_delete', ['clothings' => $clothings]);
    }
}
