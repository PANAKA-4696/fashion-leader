<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $table = 'CODE';        // テーブル名を指定
    protected $primaryKey = 'CODE_ID'; // 主キーを指定
    public $incrementing = false;     // IDが自動連番ではないことを伝える
    protected $keyType = 'string';    // IDが文字列であることを伝える

    protected $fillable = ['CODE_ID', 'CODE_NAME', 'IMAGE_PATH', 'USER_ID'];

    // 服との紐付け（中間テーブル WEAR_CODE を使用）
    public function wears()
    {
        return $this->belongsToMany(Wear::class, 'WEAR_CODE', 'CODE_ID', 'WEAR_ID');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'CODE_ID', 'CODE_ID');
    }
}