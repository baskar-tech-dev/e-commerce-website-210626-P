<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramReel extends Model
{
    use HasFactory;

    protected $table = 'instagram_reels';

    protected $fillable = [
        'caption',
        'type',
        'video_url',
        'instagram_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
