<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Theme;

class ThemeController extends Controller
{
    public function getActiveTheme()
    {
        $theme = Theme::where('is_active', true)->first();
        if (!$theme) {
            return response()->json(['message' => 'No active theme found'], 404);
        }
        return response()->json($theme);
    }
}
