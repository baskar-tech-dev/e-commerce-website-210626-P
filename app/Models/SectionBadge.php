<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionBadge extends Model
{
    use HasFactory;

    protected $table = 'section_badges';

    protected $fillable = [
        'title',
        'filter_type',
        'badge_key',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
