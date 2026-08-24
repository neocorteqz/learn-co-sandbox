# Minecraft Panel Progress

## Foundation

- [x] Debian 13 and DirectAdmin environment confirmed
- [x] Nginx-only web stack configured
- [x] Laravel 12 installed
- [x] PHP 8.5 configured for the panel
- [x] MariaDB database and restricted Laravel database user created
- [x] Laravel connected to MariaDB
- [x] HTTPS certificate configured for `panel.neocorteqz.com`
- [x] Laravel served from the `public` directory
- [x] Vite frontend dependencies installed and built

## Authentication

- [x] Laravel Breeze installed
- [x] Administrator account registered
- [x] Administrator login working
- [x] Public registration disabled

## Minecraft Test Server

- [x] Dedicated `minecraft-agent` system user created
- [x] Dedicated `minecraft-001` server user created
- [x] Server directory created under `/opt/minecraft/servers/server-001`
- [x] Java installed
- [x] Minecraft server downloaded
- [x] Minecraft EULA accepted
- [x] `minecraft-001.service` created
- [x] Minecraft server starts successfully
- [x] Minecraft port configured and listening
- [x] Firewall access configured

## Panel Server Management

- [x] Minecraft server database table created
- [x] Server record linked to `minecraft-001.service`
- [x] Restricted sudoers permissions created
- [x] Hardened local management agent created
- [x] Unix control socket created
- [x] Start, stop, and restart controls added to the panel
- [ ] Verify the panel restart button end to end
- [ ] Add live systemd status endpoint
- [ ] Display live status instead of stored `offline` value
- [ ] Add server action success and failure messages
- [ ] Add console log viewing

## Server Creation

- [x] Server list page created
- [x] Create-server form created
- [x] Minecraft version field added
- [x] Loader field added
- [x] Port validation added
- [x] Memory validation added
- [ ] Generate a Linux user safely for each new server
- [ ] Generate a systemd unit safely for each new server
- [ ] Create server directories automatically
- [ ] Download Vanilla, Paper, Fabric, Forge, and NeoForge servers
- [ ] Add Java version selection and compatibility checks
- [ ] Add CurseForge modpack support

## Panel Features

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
- [ ] Production deployment and rollback documentation

## Security Review

- [ ] Confirm Laravel `APP_DEBUG=false`
- [ ] Confirm `.env` is not web-accessible
- [ ] Keep PHP process execution functions disabled
- [ ] Restrict management agent to local Unix socket access
- [ ] Validate every systemd unit against an allowlist
- [ ] Test cross-user server isolation
- [ ] Test backup permissions
- [ ] Review firewall rules before production launch
