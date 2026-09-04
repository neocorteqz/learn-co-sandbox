# Web server deployment

All web servers must use `/var/www/minecraft-panel/public` as the document root. Do not use the repository root: it contains the database schema, PHP includes, daemon code, and private storage.

Replace `panel.example.com` and `/var/www/minecraft-panel` in the example before enabling the site. The examples use the PHP 8.5-FPM socket at `/run/php/php8.5-fpm.sock`.

## Common preparation

```sh
sudo mkdir -p /var/www/minecraft-panel
sudo chown -R root:www-data /var/www/minecraft-panel
sudo chmod 0750 /var/www/minecraft-panel/storage
sudo chmod 0750 /var/www/minecraft-panel/storage/uploads
sudo cp deploy/php/hardened.ini /etc/php/8.5/fpm/conf.d/99-minecraft-panel.ini
sudo systemctl restart php8.5-fpm
```

Use `relaxed.ini` instead of `hardened.ini` only when large approved Minecraft artifacts are required. The web worker must be able to write `storage/` and `storage/uploads/`, while the runtime config should be mode `0600` after installation.

## Nginx

Install Nginx and enable the PHP-FPM site:

```sh
sudo apt install nginx
sudo cp deploy/nginx/minecraft-panel.conf /etc/nginx/sites-available/minecraft-panel
sudo ln -s /etc/nginx/sites-available/minecraft-panel /etc/nginx/sites-enabled/minecraft-panel
sudo nginx -t
sudo systemctl reload nginx
```

Use Certbot or the host’s certificate manager to add HTTPS. After HTTPS is active, change the server block to listen on 443 and configure the certificate paths, or use the distribution’s Certbot Nginx integration.

## Apache

Enable the required modules and site:

```sh
sudo apt install apache2 libapache2-mod-fcgid
sudo a2enmod proxy proxy_fcgi rewrite setenvif
sudo cp deploy/apache/minecraft-panel.conf /etc/apache2/sites-available/minecraft-panel.conf
sudo a2ensite minecraft-panel.conf
sudo apachectl configtest
sudo systemctl reload apache2
```

Use Certbot’s Apache integration or the host’s certificate manager for HTTPS. The PHP-FPM handler keeps PHP out of the Apache process.

## Caddy

Install Caddy, copy the example into the active Caddy configuration, and reload:

```sh
sudo cp deploy/caddy/Caddyfile /etc/caddy/Caddyfile
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

Caddy obtains and renews HTTPS automatically when DNS points `panel.example.com` at the server and ports 80 and 443 are reachable.

## Installation order

1. Configure one web server and PHP-FPM.
2. Open `https://panel.example.com/install.php`.
3. Complete the installer and verify the panel health endpoint.
4. Remove or deny access to `public/install.php` after installation.
5. Confirm `APP_DEBUG`-equivalent production settings, firewall rules, and upload limits before opening the panel publicly.
