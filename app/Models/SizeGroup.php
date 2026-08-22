<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeGroup extends Model
{
    use HasFactory;

    protected $table = 'size_groups';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationship to sizes in this group.
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(Size::class, 'size_group_id')->orderBy('sort_order');
    }

    /**
     * Active sizes only.
     */
    public function activeSizes(): HasMany
    {
        return $this->hasMany(Size::class, 'size_group_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
