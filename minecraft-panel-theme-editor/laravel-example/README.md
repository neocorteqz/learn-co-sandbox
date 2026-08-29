# Laravel-ready theme example

This example turns the static theme editor into a Laravel Blade implementation that uses CSS variables and a persisted panel settings table.

## Files

- `theme-settings.blade.php` — admin settings screen and preview panel
- `ThemeSettingsController.php` — save and load theme settings
- `theme-config.php` — default theme values
- `PanelTheme.php` — model for a single row of settings
- `create_panel_themes_table.php` — migration example

## Typical flow

1. Create the database table.
2. Add the routes for the settings page and save action.
3. Include the generated CSS variables in the main layout.
4. Use the saved theme values on the dashboard and control panel screens.

## Example route usage

```php
Route::get('/admin/theme', [ThemeSettingsController::class, 'index'])->name('admin.theme');
Route::post('/admin/theme', [ThemeSettingsController::class, 'store'])->name('admin.theme.store');
```

## Additional notes

- The UI stores the values as CSS variables in the root scope.
- A `PanelTheme` row can be reused across the app to keep the visual theme consistent.
- This pattern is easy to extend with logo uploads, preset switching, and per-server overrides.
