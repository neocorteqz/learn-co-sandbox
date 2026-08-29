<?php

namespace App\Http\Controllers;

use App\Models\PanelTheme;
use Illuminate\Http\Request;

class ThemeSettingsController extends Controller
{
    public function index()
    {
        $settings = PanelTheme::firstOrCreate(
            ['id' => 1],
            config('panel-theme.default')
        );

        return view('admin.theme-settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'theme_mode' => ['required', 'in:dark,light'],
            'bg' => ['required', 'string'],
            'surface' => ['required', 'string'],
            'surface_alt' => ['required', 'string'],
            'panel' => ['required', 'string'],
            'text' => ['required', 'string'],
            'muted' => ['required', 'string'],
            'border' => ['required', 'string'],
            'accent' => ['required', 'string'],
            'accent_strong' => ['required', 'string'],
            'accent_soft' => ['required', 'string'],
            'button_start' => ['required', 'string'],
            'button_end' => ['required', 'string'],
            'button_text' => ['required', 'string'],
            'dropdown_bg' => ['required', 'string'],
            'dropdown_text' => ['required', 'string'],
            'banner_bg' => ['required', 'string'],
            'button_radius' => ['required', 'integer', 'min:0', 'max:32'],
            'dropdown_radius' => ['required', 'integer', 'min:0', 'max:24'],
            'banner_height' => ['required', 'integer', 'min:80', 'max:220'],
            'logo_size' => ['required', 'integer', 'min:48', 'max:160'],
            'banner_logo' => ['nullable', 'string', 'max:255'],
            'panel_title' => ['required', 'string', 'max:80'],
            'panel_subtitle' => ['required', 'string', 'max:80'],
        ]);

        $settings = PanelTheme::updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'Theme settings saved.')->with('settings', $settings);
    }
}
