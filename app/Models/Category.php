<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'size_chart_image',
        'icon',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Parent category relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Products relationship.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Boot model events for automatic deactivation cascading.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($category) {
            if (!$category->is_active) {
                $category->deactivateProductsAndChildren();
            }
        });

        static::deleted(function ($category) {
            $category->deactivateProductsAndChildren();
        });
    }

    /**
     * Get all descendant category IDs recursively including this category.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        $children = static::where('parent_id', $this->id)->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return array_values(array_unique($ids));
    }

    /**
     * Deactivate this category's child categories and all associated products.
     */
    public function deactivateProductsAndChildren(): void
    {
        $allIds = $this->getAllDescendantIds();

        // Deactivate descendant categories
        $descendantCategoryIds = array_diff($allIds, [$this->id]);
        if (!empty($descendantCategoryIds)) {
            static::whereIn('id', $descendantCategoryIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        // Deactivate all products belonging to this category and any descendants
        Product::whereIn('category_id', $allIds)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
