# DirectAdmin plugin

This plugin adds a small DirectAdmin dashboard for the Minecraft panel. It reads server data from `GET /api/servers` and keeps the panel API token on the server.

## Install

1. Copy `minecraft-panel/` to `/usr/local/directadmin/plugins/`.
2. Copy `config.php.example` to `config.php` and set the HTTPS API URL and a read-only panel API token.
3. Set ownership to the DirectAdmin service account and permissions to `640` for `config.php`.
4. Enable the plugin from DirectAdmin's plugin manager, then assign it to the intended user or reseller level.

The plugin does not accept unit names, shell commands, or API credentials from browser input. The API token must never be placed in JavaScript or exposed in the rendered page. Control actions should be added only after the panel has authenticated action routes and server ownership checks.
