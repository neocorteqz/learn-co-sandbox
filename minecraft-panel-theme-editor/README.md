# Minecraft Panel Theme Editor

This project provides a lightweight Web-based theme editor for a Minecraft server admin panel.

## Features

- Default dark and light themes
- Full visual token customization for backgrounds, accents, text, panels, buttons, and dropdowns
- Custom logo URL support for banner branding
- Live preview of dashboard cards and server controls
- CSS export to copy directly into a Laravel Blade file or frontend stylesheet

## Run locally

Open the `index.html` file in a browser, or serve the folder with a static web server:

```bash
cd minecraft-panel-theme-editor
python -m http.server 8000
```

Then open `http://localhost:8000`.

## Files

- `index.html` — dashboard mockup and control panel
- `theme-editor.css` — base theme styling and layout
- `theme-editor.js` — dark/light presets and customization logic
