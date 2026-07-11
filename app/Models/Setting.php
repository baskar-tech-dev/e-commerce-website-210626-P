<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get setting value casted properly depending on its type.
     */
    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float) $value : $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Set value attribute converting properly to string formats.
     */
    public function setValueAttribute($value)
    {
        if ($this->type === 'json' || is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
        } elseif ($this->type === 'boolean') {
            $this->attributes['value'] = $value ? '1' : '0';
        } else {
            $this->attributes['value'] = (string) $value;
        }
    }

    /**
     * Quick helper to retrieve a setting value.
     */
    public static function get(string $key, string $group = 'general', $default = null)
    {
        $setting = static::where('group', $group)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Quick helper to save/update a setting value.
     */
    public static function set(string $key, $value, string $group = 'general', ?string $type = null)
    {
        $setting = static::firstOrNew(['group' => $group, 'key' => $key]);
        if ($type) {
            $setting->type = $type;
        }
        $setting->value = $value;
        $setting->save();
        return $setting;
    }
}
