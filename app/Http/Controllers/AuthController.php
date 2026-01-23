<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // 新規登録画面の表示
    public function showRegister()
    {
        return view('auth.register');
    }

    // 新規登録処理
    public function register(Request $request)
    {
        // 1. バリデーション（入力チェック）
        $request->validate([
            'user_name' => 'required|string|max:50',
            'email'     => 'required|string|email|max:254|unique:USER,MAIL',
            // 定義書より：8文字以上12文字未満(max:11)
            'password'  => 'required|string|min:8|max:11',
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.max' => 'パスワードは12文字未満（11文字まで）で入力してください。',
        ]);

        DB::transaction(function () use ($request) {
            // 2. USER_IDの自動生成（US + 6桁連番）
            // 最後のユーザーを取得して番号を+1する
            $latestUser = User::orderBy('USER_ID', 'desc')->first();
            if ($latestUser) {
                // "US000001" -> 数値部分 "000001" を取り出して +1
                $num = intval(substr($latestUser->USER_ID, 2)) + 1;
            } else {
                $num = 1;
            }
            // 6桁でゼロ埋め (例: 1 -> US000001)
            $newUserId = 'US' . str_pad($num, 6, '0', STR_PAD_LEFT);

            // 3. ユーザー作成
            $user = User::create([
                'USER_ID'   => $newUserId,
                'USER_NAME' => $request->user_name,
                'MAIL'      => $request->email,
                'PASSWORD'  => Hash::make($request->password), // パスワードは必ずハッシュ化
            ]);

            // 4. そのままログインさせる
            Auth::login($user);
        });

        // 5. カレンダー画面（メイン）へリダイレクト
        return redirect()->route('main.calendar')->with('success', '登録が完了しました！');
    }

    // --- 既存のコードの下に追加 ---

    /**
     * ログイン画面の表示
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理 (DB照合)
     */
    public function login(Request $request)
    {
        // 1. 入力チェック
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 2. 認証試行
        // Auth::attempt(['DBのカラム名' => 入力値, 'password' => パスワード])
        // Userモデルの設定により、MAILカラムでユーザーを探し、PASSWORDカラムのハッシュと照合します
        if (Auth::attempt(['MAIL' => $request->email, 'password' => $request->password])) {
            
            // ログイン成功：セッション固定攻撃対策
            $request->session()->regenerate();

            // カレンダー画面へ移動
            return redirect()->route('main.calendar')->with('success', 'ログインしました');
        }

        // 3. 認証失敗：元の画面に戻してエラー表示
        // パスワードの入力は初期化されます（emailだけ保持）
        return back()->withErrors([
            'login_error' => 'メールアドレスまたはパスワードが違います。',
        ])->onlyInput('email');
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout(); // ログアウト実行

        $request->session()->invalidate();       // セッションを無効化
        $request->session()->regenerateToken();  // CSRFトークンを再生成（セキュリティ対策）

        // ログイン画面に戻す
        return redirect()->route('login')->with('success', 'ログアウトしました。');
    }
}