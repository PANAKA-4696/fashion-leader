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
        // // ★修正：ログインチェックを追加
        // // セッション切れなどでユーザー情報がない場合、ログイン画面へ強制送還する
        // if (!Auth::check()) {
        //     return redirect()->route('login')->with('error', 'ログインしてください。');
        // }

        // ここまで来れば安全にIDを取得できる
        $userId = Auth::user()->USER_ID;

        $wears = DB::table('WEAR')
            ->where('USER_ID', $userId)
            ->get();

        // URLに日付があればそれを使う、なければ今日
        $date = $request->date ?? date('Y-m-d');

        return view('main.closet_entry_or_change', [
            'wears' => $wears,
            'date'  => $date
        ]);
    }

    // ▼ カレンダー用 API
    public function getMonthlyStatus(Request $request)
    {
        // 1. ログインチェック (これがないとバグで画面が真っ白になります)
        if (!Auth::check()) {
            // ログインしていない場合でも、カレンダー枠を表示するために空のリストを送るか、
            // 空配列を返してJS側で処理させます。今回は安全のため空配列を返します。
            return response()->json([]);
        }

        $year = $request->year;
        $month = $request->month;
        $userId = Auth::user()->USER_ID;

        // 2. その月の日数（28〜31）を取得し、全ての日の「空データ」を作る
        // 例: 2026年1月なら 31日分 の枠を用意する
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $result = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            // 日付文字列を作成 (例: 2026-01-01)
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            
            // デフォルト（未登録）状態でセット
            $result[$dateStr] = [
                'isRegistered' => false,
                'img' => null
            ];
        }

        // 3. データベースから登録済みのデータを取得
        $rows = DB::table('CALENDAR')
            ->leftJoin('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->leftJoin('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $year)
            ->where('CALENDAR.CAL_MONTH', $month)
            ->select('CALENDAR.CAL_DATE', 'CODE.IMAGE_PATH')
            ->get();

        // 4. 登録がある日だけ、画像データで上書きする
        foreach ($rows as $row) {
            if ($row->IMAGE_PATH) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $row->CAL_DATE);
                
                // さっき作った枠の中にデータを入れる
                $result[$dateStr] = [
                    'isRegistered' => true,
                    'img' => $row->IMAGE_PATH
                ];
            }
        }

        // 全ての日付データが入った配列を返す
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

    // =========================================================
    // ▼ ここから下を書き換えてください
    // =========================================================

    // 服マスター管理画面を表示する命令
    public function wear_screen()
    {
        // 1. Wearモデルを使ってデータを取得
        // 'user_id' ではなく 'USER_ID' (大文字) なので注意！
        $clothings = \App\Models\Wear::where('USER_ID', Auth::user()->USER_ID)->get();
        
        // ※タグやお気に入りの絞り込み機能は、DBにカラムがないため一旦削除しました。
        // 必要であれば、TAGテーブルやFAVORITEテーブルと結合する処理を後で追加しましょう。
        
        $categories = $clothings->pluck('CATEGORY')->unique()->sort()->values();
        
        // とりあえずタグ一覧は空にしておきます
        $tags = collect([]); 
        
        // ビューに渡す変数名は、view側を変えなくて済むように $clothings のままにします
        return view('clothing.wear_screen', [
            'clothings' => $clothings,
            'categories' => $categories,
            'tags' => $tags,
            'selectedCategory' => request()->query('category'),
            'selectedTag' => request()->query('tag'),
            'selectedFavorite' => request()->query('favorite'),
        ]);
    }

    // 服の情報変更画面を表示する命令
    public function wear_change()
    {
        $clothings = \App\Models\Wear::where('USER_ID', Auth::user()->USER_ID)->get();
        return view('clothing.wear_change', ['clothings' => $clothings]);
    }

    // 服の情報編集画面を表示する命令
    public function wear_item_change($id)
    {
        // IDで検索（Wearモデルは主キーが WEAR_ID なので find() ではなく where で探すのが無難）
        $clothing = \App\Models\Wear::where('WEAR_ID', $id)
            ->where('USER_ID', Auth::user()->USER_ID)
            ->firstOrFail();

        return view('clothing.wear_item_change', ['clothing' => $clothing]);
    }

    // 服の追加画面を表示する命令
    public function wear_add()
    {
        return view('clothing.clothing_add');
    }

    // 服削除画面を表示する命令
    public function wear_delete()
    {
        $clothings = \App\Models\Wear::where('USER_ID', Auth::user()->USER_ID)->get();
        return view('clothing.wear_delete', ['clothings' => $clothings]);
    }
}