<?php

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('create', null, $model->getAuditAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) return;

            $old = array_intersect_key($model->getRawOriginal(), $dirty);
            
            // Exclude common timestamps or sensitive fields from logging if necessary
            unset($dirty['updated_at']);
            unset($old['updated_at']);
            if (isset($dirty['password'])) {
                $dirty['password'] = '[HIDDEN]';
                $old['password'] = '[HIDDEN]';
            }

            if (!empty($dirty)) {
                $model->logAudit('update', $old, $dirty);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->getAuditAttributes(), null);
        });
    }

    /**
     * Get model attributes excluding sensitive columns.
     */
    protected function getAuditAttributes(): array
    {
        $attributes = $this->getAttributes();
        unset($attributes['created_at']);
        unset($attributes['updated_at']);
        if (isset($attributes['password'])) {
            $attributes['password'] = '[HIDDEN]';
        }
        return $attributes;
    }

    /**
     * Record audit log entry in database.
     */
    protected function logAudit(string $action, ?array $old, ?array $new)
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => get_class($this),
                'auditable_id' => $this->getKey(),
                'action' => $action,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request() ? request()->ip() : null,
                'user_agent' => request() ? substr(request()->userAgent(), 0, 255) : null,
            ]);
        } catch (\Exception $e) {
            // Silently fail to not block primary application thread in production
            logger()->error('Failed to write audit log: ' . $e->getMessage());
        }
    }
}
