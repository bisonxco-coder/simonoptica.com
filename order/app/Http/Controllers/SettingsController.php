<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;

class SettingsController extends Controller
{
    /**
     * Get ini properties from config
     *
     * @return JsonResponse
     */
    public function getIniProperties(Request $request){
        $props = SettingsHelper::config('iniproperties');

        return response()->json($props, 200);
    }
}
