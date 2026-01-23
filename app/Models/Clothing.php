<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clothing extends Model
{
    use HasFactory;

    protected $table = 'clothing';

    protected $fillable = [
        'user_id', // ★追加
        'name',
        'category',
        'tags',
        'image_path',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'tags' => 'json',
    ];
}
