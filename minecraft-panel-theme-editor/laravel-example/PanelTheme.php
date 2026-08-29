<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelTheme extends Model
{
    use HasFactory;

    protected $table = 'panel_themes';

    protected $fillable = [
        'theme_mode',
        'bg',
        'surface',
        'surface_alt',
        'panel',
        'text',
        'muted',
        'border',
        'accent',
        'accent_strong',
        'accent_soft',
        'button_start',
        'button_end',
        'button_text',
        'dropdown_bg',
        'dropdown_text',
        'banner_bg',
        'button_radius',
        'dropdown_radius',
        'banner_height',
        'logo_size',
        'banner_logo',
        'panel_title',
        'panel_subtitle',
    ];
}
