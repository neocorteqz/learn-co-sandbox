@php
    $theme = $settings ?? \App\Models\PanelTheme::firstOrCreate(['id' => 1], config('panel-theme.default'));
@endphp

@extends('layouts.app')

@section('content')
    <style>
        :root {
            --bg: {{ $theme->bg }};
            --surface: {{ $theme->surface }};
            --surface-alt: {{ $theme->surface_alt }};
            --panel: {{ $theme->panel }};
            --text: {{ $theme->text }};
            --muted: {{ $theme->muted }};
            --border: {{ $theme->border }};
            --accent: {{ $theme->accent }};
            --accent-strong: {{ $theme->accent_strong }};
            --accent-soft: {{ $theme->accent_soft }};
            --button-bg: linear-gradient(135deg, {{ $theme->button_start }}, {{ $theme->button_end }});
            --button-text: {{ $theme->button_text }};
            --button-radius: {{ $theme->button_radius }}px;
            --dropdown-bg: {{ $theme->dropdown_bg }};
            --dropdown-text: {{ $theme->dropdown_text }};
            --dropdown-radius: {{ $theme->dropdown_radius }}px;
            --banner-height: {{ $theme->banner_height }}px;
            --banner-bg: linear-gradient(135deg, {{ $theme->accent_soft }}, {{ $theme->banner_bg }});
            --logo-size: {{ $theme->logo_size }}px;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .theme-editor-layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            min-height: 100vh;
        }

        .theme-editor-sidebar {
            padding: 24px;
            border-right: 1px solid var(--border);
            background: rgba(11, 16, 28, 0.8);
        }

        .theme-editor-preview {
            padding: 28px;
        }

        .panel-shell {
            min-height: 760px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--panel);
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(1, 10, 22, 0.42);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: var(--banner-height);
            padding: 16px 24px;
            background: var(--banner-bg);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: var(--logo-size);
            height: var(--logo-size);
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: rgba(0,0,0,0.18);
        }

        .brand-name strong {
            display: block;
            font-size: 1.25rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .brand-name span {
            color: var(--muted);
            font-size: 0.74rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .primary-btn, .secondary-btn, .dropdown-box select, .theme-btn {
            border-radius: var(--button-radius);
        }

        .primary-btn {
            background: var(--button-bg);
            color: var(--button-text);
            border: none;
            padding: 11px 18px;
            font-weight: 700;
        }

        .secondary-btn {
            background: rgba(255,255,255,0.02);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 11px 18px;
        }

        .dropdown-box select {
            appearance: none;
            min-width: 180px;
            background: var(--dropdown-bg);
            color: var(--dropdown-text);
            border: 1px solid var(--border);
            padding: 10px 38px 10px 14px;
        }
    </style>

    <div class="theme-editor-layout">
        <aside class="theme-editor-sidebar">
            <h1>Theme Builder</h1>

            @if (session('success'))
                <div style="margin-bottom: 16px; color: #a7ffbf;">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.theme.store') }}">
                @csrf

                <div style="display: grid; gap: 12px; margin-bottom: 18px;">
                    <label>
                        Theme mode
                        <select name="theme_mode" style="width: 100%; padding: 8px 10px; border-radius: 10px; background: transparent; color: var(--text); border: 1px solid var(--border);">
                            <option value="dark" {{ $theme->theme_mode === 'dark' ? 'selected' : '' }}>Dark</option>
                            <option value="light" {{ $theme->theme_mode === 'light' ? 'selected' : '' }}>Light</option>
                        </select>
                    </label>

                    <label>
                        Panel title
                        <input name="panel_title" value="{{ old('panel_title', $theme->panel_title) }}" style="width:100%; margin-top:6px; padding:8px 10px; border:1px solid var(--border); border-radius:10px; background:rgba(255,255,255,0.02); color:var(--text);" />
                    </label>

                    <label>
                        Panel subtitle
                        <input name="panel_subtitle" value="{{ old('panel_subtitle', $theme->panel_subtitle) }}" style="width:100%; margin-top:6px; padding:8px 10px; border:1px solid var(--border); border-radius:10px; background:rgba(255,255,255,0.02); color:var(--text);" />
                    </label>
                </div>

                <div style="display: grid; gap: 10px;">
                    @foreach([
                        ['bg', 'Background'],
                        ['surface', 'Surface'],
                        ['surface_alt', 'Surface Alt'],
                        ['panel', 'Panel'],
                        ['text', 'Text'],
                        ['muted', 'Muted'],
                        ['border', 'Border'],
                        ['accent', 'Accent'],
                        ['accent_strong', 'Accent Strong'],
                        ['accent_soft', 'Accent Soft'],
                        ['button_start', 'Button Start'],
                        ['button_end', 'Button End'],
                        ['button_text', 'Button Text'],
                        ['dropdown_bg', 'Dropdown BG'],
                        ['dropdown_text', 'Dropdown Text'],
                        ['banner_bg', 'Banner BG'],
                    ] as [$field, $label])
                        <label style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                            <span>{{ $label }}</span>
                            <input type="color" name="{{ $field }}" value="{{ old($field, $theme->{$field}) }}" />
                        </label>
                    @endforeach

                    <label>
                        Banner logo URL
                        <input name="banner_logo" value="{{ old('banner_logo', $theme->banner_logo) }}" style="width:100%; margin-top:6px; padding:8px 10px; border:1px solid var(--border); border-radius:10px; background:rgba(255,255,255,0.02); color:var(--text);" />
                    </label>

                    <label>
                        Button radius
                        <input type="range" name="button_radius" min="0" max="32" value="{{ old('button_radius', $theme->button_radius) }}" style="width:100%;" />
                    </label>

                    <label>
                        Dropdown radius
                        <input type="range" name="dropdown_radius" min="0" max="24" value="{{ old('dropdown_radius', $theme->dropdown_radius) }}" style="width:100%;" />
                    </label>

                    <label>
                        Banner height
                        <input type="range" name="banner_height" min="80" max="220" value="{{ old('banner_height', $theme->banner_height) }}" style="width:100%;" />
                    </label>

                    <label>
                        Logo size
                        <input type="range" name="logo_size" min="48" max="160" value="{{ old('logo_size', $theme->logo_size) }}" style="width:100%;" />
                    </label>
                </div>

                <div style="margin-top: 18px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="primary-btn">Save Theme</button>
                </div>
            </form>
        </aside>

        <main class="theme-editor-preview">
            <div class="panel-shell">
                <header class="panel-header">
                    <div class="brand">
                        <img class="brand-logo" src="{{ $theme->banner_logo ?: 'https://placehold.co/96x96/0d1d36/ffffff?text=MC' }}" alt="Brand logo" />
                        <div class="brand-name">
                            <strong>{{ $theme->panel_title }}</strong>
                            <span>{{ $theme->panel_subtitle }}</span>
                        </div>
                    </div>

                    <nav style="display:flex; gap:12px;">
                        <a href="#" style="color:var(--muted); text-decoration:none;">Overview</a>
                        <a href="#" style="color:var(--muted); text-decoration:none;">Servers</a>
                        <a href="#" style="color:var(--muted); text-decoration:none;">Console</a>
                    </nav>

                    <button class="primary-btn" type="button">Create Server</button>
                </header>

                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding:18px 24px; border-bottom:1px solid var(--border);">
                    <h2 style="margin:0; color:var(--muted); text-transform:uppercase; letter-spacing:0.06em; font-size:1.05rem;">Server Dashboard</h2>
                    <div style="display:flex; gap:12px; align-items:center;">
                        <div class="dropdown-box">
                            <select>
                                <option selected>Server-001</option>
                                <option>Server-002</option>
                            </select>
                        </div>
                        <button class="secondary-btn" type="button">Restart</button>
                        <button class="primary-btn" type="button">Start</button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:18px; padding:22px 24px 28px;">
                    @for ($i = 0; $i < 3; $i++)
                        <article style="background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border:1px solid var(--border); border-radius:18px; overflow:hidden;">
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 18px; border-bottom:1px solid var(--border); background:rgba(15,27,45,0.86);">
                                <strong>Lobby</strong>
                                <span style="width:10px; height:10px; border-radius:50%; background:#5eea8a; display:inline-block; box-shadow:0 0 12px rgba(94,234,138,0.8);"></span>
                            </div>
                            <div style="padding:18px; display:grid; gap:12px;">
                                <div style="display:flex; justify-content:space-between; color:var(--muted);"><span>Players</span><span style="color:var(--text); font-weight:600;">32 / 64</span></div>
                                <div style="display:flex; justify-content:space-between; color:var(--muted);"><span>TPS</span><span style="color:var(--text); font-weight:600;">19.98</span></div>
                                <div style="display:flex; justify-content:space-between; color:var(--muted);"><span>Uptime</span><span style="color:var(--text); font-weight:600;">18h 42m</span></div>
                                <div style="display:flex; gap:10px; margin-top:8px;">
                                    <button class="secondary-btn" type="button" style="flex:1;">Console</button>
                                    <button class="primary-btn" type="button" style="flex:1;">Manage</button>
                                </div>
                            </div>
                        </article>
                    @endfor
                </div>
            </div>
        </main>
    </div>
@endsection
