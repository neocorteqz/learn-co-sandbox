# Minecraft Panel Progress

## Foundation

- [x] Shared `includes` directory established
- [x] PHP backend API skeleton created
- [x] MariaDB schema defined for users, servers, mods, and metrics
- [ ] Debian 13 and DirectAdmin environment confirmed
- [x] Nginx, Apache, and Caddy PHP-FPM configurations added
- [ ] Laravel 12 installed
- [x] PHP 8.5 configured for the panel
- [x] MySQL/MariaDB, PostgreSQL, and SQLite schemas added
- [x] One-time web installer added
- [ ] MariaDB database and restricted Laravel database user created
- [ ] Laravel connected to MariaDB
- [ ] HTTPS certificate configured for `panel.neocorteqz.com`
- [ ] Laravel served from the `public` directory
- [ ] Vite frontend dependencies installed and built

## Authentication

- [ ] Laravel Breeze installed
- [ ] Administrator account registered
- [ ] Administrator login working
- [ ] Public registration disabled

## Minecraft Test Server

- [ ] Dedicated `minecraft-agent` system user created
- [ ] Dedicated `minecraft-001` server user created
- [ ] Server directory created under `/opt/minecraft/servers/server-001`
- [ ] Java installed
- [ ] Minecraft server downloaded
- [ ] Minecraft EULA accepted
- [ ] `minecraft-001.service` created
- [ ] Minecraft server starts successfully
- [ ] Minecraft port configured and listening
- [ ] Firewall access configured

## Panel Server Management

- [x] Hardened local management daemon scaffold created
- [x] Explicit systemd unit allowlist added to the daemon
- [ ] Minecraft server database table created
- [ ] Server record linked to `minecraft-001.service`
- [ ] Restricted sudoers permissions created
- [ ] Hardened local management agent created
- [ ] Unix control socket created
- [x] Start, stop, and restart API controls added to the panel
- [ ] Verify the panel restart button end to end
- [x] Add live systemd status endpoint
- [x] Display live status instead of stored `offline` value
- [ ] Add server action success and failure messages
- [ ] Add console log viewing

## Server Creation

- [ ] Server list page created
- [ ] Create-server form created
- [x] Minecraft version field added to the installer
- [x] Loader field added to the installer
- [x] Port validation added to the installer
- [x] Memory validation added to the installer
- [ ] Generate a Linux user safely for each new server
- [ ] Generate a systemd unit safely for each new server
- [ ] Create server directories automatically
- [ ] Download Vanilla, Paper, Fabric, Forge, and NeoForge servers
- [ ] Add Java version selection and compatibility checks
- [ ] Add CurseForge modpack support

## Panel Features

- [x] DirectAdmin plugin dashboard created
- [x] Reusable file uploader include created
- [x] Hardened PHP upload profile added
- [x] Relaxed PHP upload profile added
- [x] Private upload storage outside the web root added
- [ ] Server details page
- [ ] File manager with path restrictions
- [ ] Backup and restore system
- [ ] Scheduled tasks
- [ ] Player and resource statistics
- [ ] Server permissions and ownership controls
- [ ] Audit log
- [ ] Rate limiting and action cooldowns
- [ ] Theme builder with database-backed CSS variables
- [ ] Light and dark themes
- [x] Web server deployment documentation added

## Security Review

- [ ] Deploy and configure the DirectAdmin plugin
- [ ] Configure `UPLOAD_MODE` and PHP upload limits
- [ ] Add authentication and ownership checks to upload routes
- [ ] Confirm Laravel `APP_DEBUG=false`
- [ ] Confirm `.env` is not web-accessible
- [ ] Keep PHP process execution functions disabled
- [ ] Restrict management agent to local Unix socket access
- [ ] Validate every systemd unit against an allowlist
- [ ] Test cross-user server isolation
- [ ] Test backup permissions
- [ ] Review firewall rules before production launch
