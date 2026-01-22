<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ★追加：テーブル名を指定
    protected $table = 'USER';

    // ★追加：主キーを指定（デフォルトのidではないため）
    protected $primaryKey = 'USER_ID';
    
    // ★追加：主キーは文字列（char）なのでincrementしない
    public $incrementing = false;
    protected $keyType = 'string';

    // 保存可能なカラム
    protected $fillable = [
        'USER_ID',
        'USER_NAME',
        'MAIL',
        'PASSWORD',
        // 'ROOT', // 必要であれば追加
    ];

    // パスワードのカラム名が標準の 'password' ではなく 'PASSWORD' の場合
    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }

    protected $hidden = [
        'PASSWORD',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'PASSWORD' => 'hashed', // 自動でハッシュ化
    ];
}