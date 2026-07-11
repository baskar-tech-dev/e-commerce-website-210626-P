<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'icon',
        'group',
        'permission_name',
        'role_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the role associated with the menu.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
