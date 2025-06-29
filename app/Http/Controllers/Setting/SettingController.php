<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // In SettingController
    public function currency()
    {
        return response()->json(
            Setting::first(['currency_code', 'currency_symbol'])
        );
    }
}