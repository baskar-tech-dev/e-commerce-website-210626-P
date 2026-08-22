<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Size extends Model
{
    use HasFactory;

    protected $table = 'sizes';

    protected $fillable = [
        'size_group_id',
        'name',
        'code',
        'measurement_hint',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'size_group_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Parent SizeGroup relationship.
     */
    public function sizeGroup(): BelongsTo
    {
        return $this->belongsTo(SizeGroup::class, 'size_group_id');
    }
}
