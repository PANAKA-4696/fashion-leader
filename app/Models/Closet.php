<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Closet extends Model
{
    public function codes()
    {
        // CLOSET_CODEテーブルを介して、CODEテーブルと多対多で繋がる
        return $this->belongsToMany(Code::class, 'CLOSET_CODE', 'CLOSET_ID', 'CODE_ID');
    }

    protected $table = 'CLOSET'; // テーブル名
    protected $primaryKey = 'CLOSET_ID'; // 主キー
    public $incrementing = false; // 自動連番ではない
    protected $keyType = 'string'; // 文字列ID

    protected $fillable = ['CLOSET_ID', 'CLOSET_NAME', 'TAG_NAME', 'USER_ID'];
}