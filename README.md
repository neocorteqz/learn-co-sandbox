# Custom Minecraft Panel

Foundation for a PHP/MariaDB Minecraft management panel and a local control daemon.

## Structure

- `public/`: HTTP entry point for the JSON API
- `includes/`: shared PHP configuration and database bootstrap
- `includes/file-uploader.php`: reusable upload form partial
- `database/`: MariaDB schema
- `daemon/`: restricted daemon that controls explicitly mapped systemd units
- `directadmin-plugin/`: DirectAdmin dashboard package for the panel API
- `storage/uploads/`: private upload storage outside the web root
- `deploy/`: PHP profiles and Nginx, Apache, Caddy, and deployment instructions

## Local setup

1. Create a MySQL/MariaDB, PostgreSQL, or SQLite database and restricted application user where applicable.
2. Apply the matching schema: `database/schema.sql`, `database/schema.pgsql.sql`, or `database/schema.sqlite.sql`.
3. Set `DB_DRIVER`, `DB_DSN`, `DB_USERNAME`, `DB_PASSWORD`, and `DAEMON_TOKEN` in the web process environment.
4. Run the API with a PHP-capable web server rooted at `public/`.
5. Run the daemon with `DAEMON_TOKEN` and a JSON `SERVER_UNITS` mapping, for example `{"1":"minecraft-001.service"}`.

## Web installer

Serve `public/` as the web root and open `/install.php` over HTTPS. The installer validates the database, administrator, daemon, and first server settings, applies `database/schema.sql`, creates the first user and server, and writes `storage/config.php` with mode `0600`. Ensure the web process can write `storage/` and `storage/uploads/` during installation. After a successful install, remove or deny access to `public/install.php`; the installer refuses to run while `storage/config.php` exists.

The installer stores the database and daemon credentials in `storage/config.php`, outside the `public/` web root. It supports MySQL/MariaDB, PostgreSQL, and SQLite. For MySQL/MariaDB and PostgreSQL, the installer can create the database when the checkbox is enabled and the supplied account has database-creation permission. For production, prefer a temporary administrator account for installation, then configure the panel with a restricted application account. Redis is not supported as a primary database because users, servers, mods, and metrics use SQL tables. It is still important to protect the server filesystem and use HTTPS while submitting the form.

File uploads are handled by `public/upload.php`. Set `UPLOAD_MODE=hardened` for configuration-only uploads up to 10 MiB, or `UPLOAD_MODE=relaxed` for approved Minecraft artifacts up to 512 MiB. Set PHP's `upload_max_filesize` and `post_max_size` high enough for the selected profile. Keep `UPLOAD_ROOT` outside the web root and ensure the PHP worker can write it.

The daemon only accepts `start`, `stop`, and `restart` through an allowlisted mapping and never accepts a unit name from the request body. Authentication, RBAC, CurseForge credentials, cgroup enforcement, WebSockets/SSE, and file-manager authorization remain application features to build on this foundation.

Web server examples for Nginx, Apache, and Caddy are in `deploy/README.md`. Each configuration serves only `public/` and forwards PHP requests to PHP 8.5-FPM.
