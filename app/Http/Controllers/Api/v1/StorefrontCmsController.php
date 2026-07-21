<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StorefrontCmsController extends Controller
{
    /**
     * Get all storefront CMS settings formatted for frontend consumption.
     */
    public function index(): JsonResponse
    {
        $allSettings = Setting::all();
        
        $cms = [];
        foreach ($allSettings as $setting) {
            $group = $setting->group;
            $key = $setting->key;
            $value = $setting->value; // uses getValueAttribute casting
            
            if (!isset($cms[$group])) {
                $cms[$group] = [];
            }
            $cms[$group][$key] = $value;
        }

        return response()->json([
            'success' => true,
            'data' => $cms,
        ]);
    }
}
