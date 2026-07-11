<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'primary_color',
        'primary_color_hover',
        'secondary_color',
        'secondary_color_hover',
        'is_active',
    ];
}
