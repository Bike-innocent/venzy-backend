<?php
namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SettingGroup;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/admin/settings
     * List all setting groups with their settings
     */
    public function index()
    {
        $groups = SettingGroup::with('settings')->get();

        $data = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'slug' => $group->slug,
                'name' => $group->name,
                'settings' => $group->settings->map(fn($s) => [
                    'key' => $s->key,
                    'value' => $s->value,
                ]),
            ];
        });

        return response()->json($data);
    }

    /**
     * GET /api/admin/settings/{slug}
     * Get settings for a specific group
     */
    public function show($slug)
    {
        $group = SettingGroup::where('slug', $slug)->with('settings')->firstOrFail();

        $settings = $group->settings->mapWithKeys(fn($setting) => [
            $setting->key => $setting->value,
        ]);

        return response()->json([
            'id' => $group->id,
            'slug' => $group->slug,
            'name' => $group->name,
            'settings' => $settings,
        ]);
    }

    /**
     * PUT /api/admin/settings/{slug}
     * Update settings for a specific group
     */
    public function update(Request $request, $slug)
    {
        $group = SettingGroup::where('slug', $slug)->firstOrFail();

        $data = $request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['setting_group_id' => $group->id, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}