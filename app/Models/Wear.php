<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wear extends Model
{
    // 1. 使用するテーブル名を指定（大文字のWEAR）
    protected $table = 'WEAR';

    // 2. 主キーのカラム名を指定
    protected $primaryKey = 'WEAR_ID';

    // 3. IDが自動連番（1, 2, 3...）ではないことを伝える
    public $incrementing = false;

    // 4. IDのデータ型が文字列であることを伝える
    protected $keyType = 'string';

    // 5. 複数代入を許可するカラム（保存したい項目）
    protected $fillable = [
        'WEAR_ID',
        'ITEM_NAME',
        'CATEGORY',
        'IMAGE_PATH',
        'USER_ID',
        'TAGS',        // ★追加
        'IS_FAVORITE'  // ★追加
    ];

    // ★追加: キャスト設定（自動変換）
    // DBから取り出したときに、TAGSを自動で配列に、IS_FAVORITEをtrue/falseにしてくれます
    protected $casts = [
        'TAGS' => 'array',
        'IS_FAVORITE' => 'boolean',
    ];

    /**
     * この服が使われているコーデたち（多対多のリレーション）
     */
    public function codes()
    {
        // WEAR_CODE 中間テーブルを介して CODE テーブルと紐付け
        return $this->belongsToMany(Code::class, 'WEAR_CODE', 'WEAR_ID', 'CODE_ID');
    }
}