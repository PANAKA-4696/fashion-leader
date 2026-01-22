<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'TAG';
    protected $primaryKey = 'TAG_ID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // 田中さんの設計にtimestampsがない場合

    protected $fillable = ['TAG_ID', 'TAG_NAME', 'USER_ID', 'CODE_ID', 'CLOSET_ID', 'WEAR_ID'];
}