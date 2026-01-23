<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // ★これの重複がエラーの原因でした
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // テーブル名
    protected $table = 'USER';

    // 主キー（USER_ID）
    protected $primaryKey = 'USER_ID';
    
    // 主キーは自動増分（オートインクリメント）ではない
    public $incrementing = false;
    
    // 主キーの型は文字列
    protected $keyType = 'string';

    // 保存可能なカラム
    protected $fillable = [
        'USER_ID',
        'USER_NAME',
        'PASSWORD',
        'MAIL', // 必要であれば追加
    ];

    // パスワードなどの隠したい項目
    protected $hidden = [
        'PASSWORD',
        'remember_token',
    ];

    // キャスト設定
    protected $casts = [
        'email_verified_at' => 'datetime',
        'PASSWORD' => 'hashed', // 自動でハッシュ化
    ];

    /**
     * Laravel標準の「password」ではなく「PASSWORD」を使うように指示する
     */
    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }
}