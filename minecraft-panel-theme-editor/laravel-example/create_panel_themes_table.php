<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panel_themes', function (Blueprint $table) {
            $table->id();
            $table->string('theme_mode')->default('dark');
            $table->string('bg')->default('#0a1220');
            $table->string('surface')->default('#111d2f');
            $table->string('surface_alt')->default('#182842');
            $table->string('panel')->default('#0f1b2d');
            $table->string('text')->default('#e8f2ff');
            $table->string('muted')->default('#9cb4d1');
            $table->string('border')->default('#93b6ff33');
            $table->string('accent')->default('#6ee7ff');
            $table->string('accent_strong')->default('#4fc3f7');
            $table->string('accent_soft')->default('#6ee7ff2e');
            $table->string('button_start')->default('#44d3ff');
            $table->string('button_end')->default('#5d9bff');
            $table->string('button_text')->default('#09131c');
            $table->string('dropdown_bg')->default('#162338');
            $table->string('dropdown_text')->default('#edf6ff');
            $table->string('banner_bg')->default('#6ee7ff29');
            $table->unsignedInteger('button_radius')->default(12);
            $table->unsignedInteger('dropdown_radius')->default(10);
            $table->unsignedInteger('banner_height')->default(132);
            $table->unsignedInteger('logo_size')->default(96);
            $table->string('banner_logo')->nullable();
            $table->string('panel_title')->default('Neocore');
            $table->string('panel_subtitle')->default('Server Control');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_themes');
    }
};
