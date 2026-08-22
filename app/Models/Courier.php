<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'tracking_page_link',
        'contact_person',
        'contact_number',
        'contact_email',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Orders handled by this courier.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Resolve the live tracking URL for a specific tracking number / AWB.
     */
    public function generateTrackingUrl(?string $trackingNumber): ?string
    {
        if (empty($trackingNumber)) {
            return null;
        }

        $template = trim($this->tracking_page_link ?? '');

        if (empty($template)) {
            return null;
        }

        if (str_contains($template, '{tracking_number}')) {
            return str_replace('{tracking_number}', urlencode($trackingNumber), $template);
        }

        if (str_contains($template, '{tracking_id}')) {
            return str_replace('{tracking_id}', urlencode($trackingNumber), $template);
        }

        if (str_contains($template, '{awb}')) {
            return str_replace('{awb}', urlencode($trackingNumber), $template);
        }

        // If template doesn't contain placeholders, return the link as-is
        return $template;
    }
}
