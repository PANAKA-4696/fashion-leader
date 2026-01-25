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
    public function closet_clothes(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        $date = $request->date; // 例: 2026-01-26

        // 日付を分解
        [$y, $m, $d] = explode('-', $date);

        // 1. その日のコーデデータを取得
        // CALENDAR -> TODAY_CODE -> CODE と繋いで画像パスなどを取ります
        $code = DB::table('CALENDAR')
            ->join('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->join('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $y)
            ->where('CALENDAR.CAL_MONTH', $m)
            ->where('CALENDAR.CAL_DATE', $d)
            ->select('CODE.*') // CODEテーブルの全データ（IMAGE_PATHなど）を取得
            ->first();

        // 2. そのコーデに使われている服たちも取得（表示用）
        $wears = [];
        if ($code) {
            $wears = DB::table('WEAR_CODE')
                ->join('WEAR', 'WEAR_CODE.WEAR_ID', '=', 'WEAR.WEAR_ID')
                ->where('WEAR_CODE.CODE_ID', $code->CODE_ID)
                ->select('WEAR.*')
                ->get();
        }

        return view('main.closet_clothes', [
            'date'  => $date,
            'code'  => $code,   // コーデ情報（画像など）
            'wears' => $wears,  // 使った服リスト
        ]);
    }

    // ▼ 今日のコーデ登録・変更画面
    public function closet_edit(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        // URLに日付があればそれを使う、なければ今日
        $date = $request->date ?? date('Y-m-d');
        [$y, $m, $d] = explode('-', $date);

        // 1. ユーザーの全服データを取得（選択リスト用）
        $wears = \App\Models\Wear::where('USER_ID', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. その日のコーデが既に登録されているか確認
        // CALENDAR -> TODAY_CODE -> CODE の順で繋いでデータを取ってきます
        $existingCoord = DB::table('CALENDAR')
            ->join('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->join('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $y)
            ->where('CALENDAR.CAL_MONTH', $m)
            ->where('CALENDAR.CAL_DATE', $d)
            ->select('CODE.*') // コーデの全情報（画像、タグ、お気に入りなど）を取得
            ->first();

        // 3. そのコーデに使われている服のIDリストを取得
        $selectedWearIds = [];
        if ($existingCoord) {
            $selectedWearIds = DB::table('WEAR_CODE')
                ->where('CODE_ID', $existingCoord->CODE_ID)
                ->pluck('WEAR_ID')
                ->toArray();
        }

        return view('main.closet_entry_or_change', [
            'wears'           => $wears,           // 全服リスト
            'date'            => $date,            // 対象日付
            'existingCoord'   => $existingCoord,   // 登録済みのコーデ情報 (なければ null)
            'selectedWearIds' => $selectedWearIds, // 選ばれている服IDの配列
        ]);
    }

    
    // ▼ カレンダー用 API (修正版)
    public function getMonthlyStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $year = $request->year;
        $month = $request->month;
        $userId = Auth::user()->USER_ID;

        // 1. その月の日数分、空枠を用意
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $result = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $result[$dateStr] = [
                'isRegistered' => false,
                'img' => null
            ];
        }

        // 2. データベースから画像パスを取得
        // CALENDAR -> TODAY_CODE -> CODE と繋いで IMAGE_PATH を取得
        $rows = DB::table('CALENDAR')
            ->join('TODAY_CODE', 'CALENDAR.CALENDAR_ID', '=', 'TODAY_CODE.CALENDAR_ID')
            ->join('CODE', 'TODAY_CODE.CODE_ID', '=', 'CODE.CODE_ID')
            ->where('CALENDAR.USER_ID', $userId)
            ->where('CALENDAR.CAL_YEAR', $year)
            ->where('CALENDAR.CAL_MONTH', $month)
            ->select('CALENDAR.CAL_DATE', 'CODE.IMAGE_PATH')
            ->get();

        // 3. データがある日は上書き
        foreach ($rows as $row) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $row->CAL_DATE);
            
            $result[$dateStr] = [
                'isRegistered' => true,
                // 画像パスがあればセット (nullならnullのまま)
                'img' => $row->IMAGE_PATH 
            ];
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

    // ▼ 今日のコーデ登録処理
    public function saveCoord(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        $date = $request->date;
        $wearIds = $request->clothing_ids ?? [];
        [$y, $m, $d] = explode('-', $date);

        // タグとお気に入りデータの準備
        $tagsInput = $request->input('tags');
        $tagsJson = $tagsInput ? json_encode(explode(',', $tagsInput), JSON_UNESCAPED_UNICODE) : null;
        $isFavorite = $request->has('is_favorite') ? 1 : 0;

        // 画像保存処理
        $imagePath = null;
        if ($request->hasFile('coord_image')) {
            $storedPath = $request->file('coord_image')->store('coord', 'public');
            $imagePath = $storedPath;
        }

        // --- データ保存 ---

        $codeId = 'C' . strtoupper(substr(uniqid(), -7));

        // 1. CODE テーブルへの保存（ここには created_at があるのでOK）
        DB::table('CODE')->insert([
            'CODE_ID'     => $codeId,
            'USER_ID'     => $userId,
            'CODE_NAME'   => $date . ' のコーデ',
            'IMAGE_PATH'  => $imagePath,
            'TAGS'        => $tagsJson,
            'IS_FAVORITE' => $isFavorite,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 2. WEAR_CODE テーブルへの保存
        foreach ($wearIds as $wearId) {
            DB::table('WEAR_CODE')->insert([
                'CODE_ID' => $codeId,
                'WEAR_ID' => $wearId
            ]);
        }

        // 3. CALENDAR テーブルへの保存
        $calendar = DB::table('CALENDAR')
            ->where('USER_ID', $userId)
            ->where('CAL_YEAR', $y)
            ->where('CAL_MONTH', $m)
            ->where('CAL_DATE', $d)
            ->first();

        if ($calendar) {
            $calendarId = $calendar->CALENDAR_ID;
        } else {
            $calendarId = uniqid('CAL');
            
            // ▼▼ 修正箇所: created_at, updated_at を削除しました ▼▼
            DB::table('CALENDAR')->insert([
                'CALENDAR_ID' => $calendarId,
                'USER_ID'     => $userId,
                'CAL_YEAR'    => $y,
                'CAL_MONTH'   => $m,
                'CAL_DATE'    => $d,
                // created_at と updated_at は削除
            ]);
            // ▲▲ 修正箇所終わり ▲▲
        }

        // 4. TODAY_CODE テーブルを更新
        DB::table('TODAY_CODE')->updateOrInsert(
            ['CALENDAR_ID' => $calendarId],
            ['CODE_ID' => $codeId]
        );

        return redirect('/main/calendar')->with('success', 'コーデを登録しました！');
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
    public function wear_screen(Request $request)
    {
        $userId = Auth::user()->USER_ID;
        
        // 1. クエリの準備（まだ実行しません）
        $query = \App\Models\Wear::where('USER_ID', $userId);

        // ▼ カテゴリ絞り込み
        if ($request->filled('category')) {
            $query->where('CATEGORY', $request->category);
        }

        // ▼ お気に入り絞り込み
        if ($request->input('favorite') === 'true') {
            $query->where('IS_FAVORITE', true);
        }

        // ▼ タグ絞り込み (JSONカラムの中にそのタグが含まれているか)
        if ($request->filled('tag')) {
            $query->whereJsonContains('TAGS', $request->tag);
        }

        // 絞り込んだ結果を取得
        $clothings = $query->get();


        // --- フィルター用リスト（ドロップダウンの中身）の作成 ---
        // 絞り込みに関係なく、ユーザーの全データからリストを作る必要があります
        $allWears = \App\Models\Wear::where('USER_ID', $userId)->get();

        // カテゴリ一覧
        $categories = $allWears->pluck('CATEGORY')->unique()->sort()->values();

        // タグ一覧 (JSON配列を取り出して、結合して、重複を消す)
        $tags = $allWears->pluck('TAGS') // 全員のタグ配列を取得 [[春,夏], [秋], null...]
            ->flatten()       // 平らにならす [春, 夏, 秋, null...]
            ->filter()        // 空っぽを除去
            ->unique()        // 重複を除去
            ->sort()          // あいうえお順
            ->values();       // 番号を振り直す

        return view('clothing.wear_screen', [
            'clothings' => $clothings,
            'categories' => $categories,
            'tags' => $tags,
            'selectedCategory' => $request->category,
            'selectedTag' => $request->tag,
            'selectedFavorite' => $request->favorite,
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