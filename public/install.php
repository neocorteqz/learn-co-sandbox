<?php

declare(strict_types=1);

if (($_SERVER['HTTPS'] ?? '') === '' || strtolower((string) $_SERVER['HTTPS']) === 'off') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The installer requires HTTPS.';
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => ($_SERVER['HTTPS'] ?? '') !== '',
]);
session_start();

$configPath = dirname(__DIR__) . '/storage/config.php';
$installed = is_file($configPath);
$errors = [];
$success = false;

if (!isset($_SESSION['install_csrf'])) {
    $_SESSION['install_csrf'] = bin2hex(random_bytes(32));
}

$values = [
    'panel_name' => 'Minecraft Panel',
    'panel_url' => '',
    'db_driver' => 'mysql',
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'minecraft_panel',
    'db_username' => 'minecraft_panel',
    'db_password' => '',
    'db_create' => '1',
    'db_path' => 'storage/panel.sqlite',
    'admin_email' => '',
    'admin_password' => '',
    'server_name' => '',
    'server_address' => '',
    'server_port' => '25565',
    'minecraft_version' => '',
    'loader' => 'vanilla',
    'memory_mb' => '2048',
    'systemd_unit' => 'minecraft-001.service',
    'daemon_url' => 'http://127.0.0.1:8765',
    'daemon_token' => '',
    'upload_mode' => 'hardened',
];

function installEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function installField(string $name): string
{
    global $values;
    return installEscape((string) ($values[$name] ?? ''));
}

function installRequired(string $name, string $label, int $maxLength = 255): void
{
    global $values, $errors;
    $value = trim((string) ($values[$name] ?? ''));
    $values[$name] = $value;
    if ($value === '') {
        $errors[] = $label . ' is required.';
    } elseif (strlen($value) > $maxLength) {
        $errors[] = $label . ' is too long.';
    }
}

function installInteger(string $name, string $label, int $minimum, int $maximum): void
{
    global $values, $errors;
    $value = filter_var($values[$name] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value < $minimum || $value > $maximum) {
        $errors[] = $label . ' must be between ' . $minimum . ' and ' . $maximum . '.';
    } else {
        $values[$name] = (string) $value;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    foreach ($values as $name => $_) {
        if (isset($_POST[$name])) {
            $values[$name] = is_string($_POST[$name]) ? trim($_POST[$name]) : '';
        }
    }
    $values['db_create'] = isset($_POST['db_create']) ? '1' : '0';

    if (!hash_equals($_SESSION['install_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'The installation form expired. Reload the page and try again.';
    }

    installRequired('panel_name', 'Panel name', 120);
    installRequired('panel_url', 'Panel URL', 255);
    if (!in_array($values['db_driver'], ['mysql', 'pgsql', 'sqlite'], true)) {
        $errors[] = 'Database driver selection is invalid. Redis is an optional cache, not a primary database.';
    }
    if ($values['db_driver'] === 'sqlite') {
        installRequired('db_path', 'SQLite database path', 255);
        if (str_contains($values['db_path'], '..')) {
            $errors[] = 'SQLite database path cannot contain parent-directory segments.';
        }
    } else {
        installRequired('db_host', 'Database host', 255);
        installRequired('db_name', 'Database name', 64);
        installRequired('db_username', 'Database username', 128);
    }
    installRequired('admin_email', 'Administrator email', 255);
    installRequired('server_name', 'Server name', 120);
    installRequired('server_address', 'Server address', 255);
    installRequired('minecraft_version', 'Minecraft version', 32);
    installRequired('systemd_unit', 'systemd unit', 255);
    installRequired('daemon_url', 'Daemon URL', 255);
    installRequired('daemon_token', 'Daemon token', 255);

    if (filter_var($values['panel_url'], FILTER_VALIDATE_URL) === false || parse_url($values['panel_url'], PHP_URL_SCHEME) !== 'https') {
        $errors[] = 'Panel URL must be a valid https URL.';
    }
    if (filter_var($values['daemon_url'], FILTER_VALIDATE_URL) === false || !in_array(parse_url($values['daemon_url'], PHP_URL_SCHEME), ['http', 'https'], true)) {
        $errors[] = 'Daemon URL must be a valid http or https URL.';
    }
    if ($values['db_driver'] !== 'sqlite' && !preg_match('/^[A-Za-z0-9_.:-]+$/', $values['db_host'])) {
        $errors[] = 'Database host contains unsupported characters.';
    }
    if ($values['db_driver'] !== 'sqlite' && !preg_match('/^[A-Za-z0-9_$-]+$/', $values['db_name'])) {
        $errors[] = 'Database name contains unsupported characters.';
    }
    if ($values['db_driver'] !== 'sqlite' && !preg_match('/^[A-Za-z0-9_$-]+$/', $values['db_username'])) {
        $errors[] = 'Database username contains unsupported characters.';
    }
    if (!filter_var($values['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Administrator email must be valid.';
    }
    if (strlen($values['admin_password']) < 12) {
        $errors[] = 'Administrator password must be at least 12 characters.';
    }
    if ($values['db_driver'] !== 'sqlite') {
        installInteger('db_port', 'Database port', 1, 65535);
    }
    installInteger('server_port', 'Server port', 1, 65535);
    installInteger('memory_mb', 'Memory', 512, 1048576);

    if (!in_array($values['loader'], ['vanilla', 'paper', 'fabric', 'forge', 'neoforge'], true)) {
        $errors[] = 'Loader selection is invalid.';
    }
    if (!in_array($values['upload_mode'], ['hardened', 'relaxed'], true)) {
        $errors[] = 'Upload mode selection is invalid.';
    }

    if ($errors === []) {
        $pdo = null;
        try {
            if ($values['db_driver'] === 'sqlite') {
                $sqlitePath = $values['db_path'];
                if (!str_starts_with($sqlitePath, '/')) {
                    $sqlitePath = dirname(__DIR__) . '/' . ltrim($sqlitePath, '/');
                }
                $dsn = 'sqlite:' . $sqlitePath;
                $schemaPath = dirname(__DIR__) . '/database/schema.sqlite.sql';
                $databaseUsername = null;
                $databasePassword = null;
            } elseif ($values['db_driver'] === 'pgsql') {
                $dsn = 'pgsql:host=' . $values['db_host'] . ';port=' . $values['db_port'] . ';dbname=' . $values['db_name'];
                $schemaPath = dirname(__DIR__) . '/database/schema.pgsql.sql';
                $databaseUsername = $values['db_username'];
                $databasePassword = $values['db_password'];
            } else {
                $dsn = 'mysql:host=' . $values['db_host'] . ';port=' . $values['db_port'] . ';dbname=' . $values['db_name'] . ';charset=utf8mb4';
                $schemaPath = dirname(__DIR__) . '/database/schema.sql';
                $databaseUsername = $values['db_username'];
                $databasePassword = $values['db_password'];
            }
            try {
                $pdo = new PDO($dsn, $databaseUsername, $databasePassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (Throwable $connectionError) {
                if ($values['db_driver'] === 'sqlite' || $values['db_create'] !== '1') {
                    throw $connectionError;
                }

                $serverDsn = $values['db_driver'] === 'pgsql'
                    ? 'pgsql:host=' . $values['db_host'] . ';port=' . $values['db_port']
                    : 'mysql:host=' . $values['db_host'] . ';port=' . $values['db_port'];
                $adminPdo = new PDO($serverDsn, $databaseUsername, $databasePassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                if ($values['db_driver'] === 'pgsql') {
                    $databaseExists = $adminPdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
                    $databaseExists->execute([$values['db_name']]);
                    if ($databaseExists->fetchColumn() === false) {
                        $quotedName = '"' . str_replace('"', '""', $values['db_name']) . '"';
                        $adminPdo->exec('CREATE DATABASE ' . $quotedName);
                    }
                } else {
                    $quotedName = '`' . str_replace('`', '``', $values['db_name']) . '`';
                    $adminPdo->exec('CREATE DATABASE IF NOT EXISTS ' . $quotedName . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                }

                $pdo = new PDO($dsn, $databaseUsername, $databasePassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }

            $schema = file_get_contents($schemaPath);
            if ($schema === false) {
                throw new RuntimeException('The database schema could not be read.');
            }
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
                $pdo->exec($statement);
            }

            if ($values['db_driver'] === 'mysql') {
                $addressColumn = $pdo->query("SHOW COLUMNS FROM servers LIKE 'address'")->fetch();
            } elseif ($values['db_driver'] === 'pgsql') {
                $addressColumn = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'servers' AND column_name = 'address'")->fetch();
            } else {
                $addressColumn = null;
                foreach ($pdo->query('PRAGMA table_info(servers)')->fetchAll() as $column) {
                    if (($column['name'] ?? '') === 'address') {
                        $addressColumn = $column;
                        break;
                    }
                }
            }
            if ($addressColumn === false) {
                $pdo->exec($values['db_driver'] === 'mysql'
                    ? 'ALTER TABLE servers ADD address VARCHAR(255) NOT NULL AFTER name'
                    : 'ALTER TABLE servers ADD COLUMN address VARCHAR(255) NOT NULL');
            } elseif ($values['db_driver'] === 'sqlite' && $addressColumn === null) {
                $pdo->exec('ALTER TABLE servers ADD COLUMN address VARCHAR(255) NOT NULL DEFAULT \'\'');
            }

            $pdo->beginTransaction();
            $user = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $user->execute([$values['admin_email'], password_hash($values['admin_password'], PASSWORD_DEFAULT)]);
            $ownerId = $values['db_driver'] === 'pgsql'
                ? (int) $pdo->query('SELECT currval(pg_get_serial_sequence(\'users\', \'id\'))')->fetchColumn()
                : (int) $pdo->lastInsertId();
            $server = $pdo->prepare(
                'INSERT INTO servers (owner_id, name, address, minecraft_version, loader, systemd_unit, daemon_url, memory_mb, port) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $server->execute([
                $ownerId,
                $values['server_name'],
                $values['server_address'],
                $values['minecraft_version'],
                $values['loader'],
                $values['systemd_unit'],
                rtrim($values['daemon_url'], '/'),
                $values['memory_mb'],
                $values['server_port'],
            ]);
            $pdo->commit();

            $runtimeConfig = [
                'installed_at' => gmdate('c'),
                'panel' => ['name' => $values['panel_name'], 'url' => rtrim($values['panel_url'], '/')],
                'database' => ['dsn' => $dsn, 'username' => $values['db_username'], 'password' => $values['db_password']],
                'daemon_url' => rtrim($values['daemon_url'], '/'),
                'daemon_token' => $values['daemon_token'],
                'uploads' => ['mode' => $values['upload_mode'], 'root' => dirname(__DIR__) . '/storage/uploads'],
            ];
            $runtimeConfig['database']['driver'] = $values['db_driver'];
            $temporaryPath = $configPath . '.tmp.' . bin2hex(random_bytes(8));
            if (file_put_contents($temporaryPath, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($runtimeConfig, true) . ";\n", LOCK_EX) === false) {
                throw new RuntimeException('The runtime config could not be written.');
            }
            chmod($temporaryPath, 0600);
            if (!rename($temporaryPath, $configPath)) {
                throw new RuntimeException('The runtime config could not be activated.');
            }
            $success = true;
            $installed = true;
        } catch (Throwable $error) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Installation failed: ' . $error->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install Minecraft Panel</title>
    <style>
        :root { color-scheme: dark; font-family: system-ui, sans-serif; background: #111827; color: #e5e7eb; }
        body { margin: 0; } main { max-width: 900px; margin: 0 auto; padding: 32px 20px 64px; }
        h1 { margin-bottom: 8px; } p { color: #9ca3af; } fieldset { margin: 24px 0; padding: 20px; border: 1px solid #374151; border-radius: 8px; }
        legend { padding: 0 8px; font-weight: 700; } label { display: block; margin: 14px 0 6px; font-weight: 600; }
        input, select { box-sizing: border-box; width: 100%; padding: 10px 12px; border: 1px solid #4b5563; border-radius: 5px; background: #1f2937; color: #f9fafb; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 18px; } .notice { padding: 14px; border-radius: 6px; background: #1f2937; }
        .error { border: 1px solid #b91c1c; color: #fecaca; } button { padding: 11px 16px; border: 0; border-radius: 5px; background: #2563eb; color: white; font-weight: 700; cursor: pointer; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <h1>Install Minecraft Panel</h1>
    <?php if ($installed): ?>
        <div class="notice"><?= $success ? 'Installation completed. Remove or protect public/install.php before using the panel.' : 'This panel is already installed. Delete or protect public/install.php.' ?></div>
    <?php else: ?>
        <p>Enter the database, administrator, daemon, and first Minecraft server settings.</p>
        <?php if ($errors !== []): ?><div class="notice error"><ul><?php foreach ($errors as $error): ?><li><?= installEscape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= installEscape($_SESSION['install_csrf']) ?>">
            <fieldset><legend>Panel</legend><div class="grid">
                <div><label for="panel_name">Panel name</label><input id="panel_name" name="panel_name" value="<?= installField('panel_name') ?>" required></div>
                <div><label for="panel_url">Panel URL</label><input id="panel_url" name="panel_url" type="url" placeholder="https://panel.example.com" value="<?= installField('panel_url') ?>" required></div>
            </div></fieldset>
            <fieldset><legend>Database</legend><div class="grid">
                <div><label for="db_driver">Database type</label><select id="db_driver" name="db_driver"><option value="mysql"<?= $values['db_driver'] === 'mysql' ? ' selected' : '' ?>>MySQL / MariaDB</option><option value="pgsql"<?= $values['db_driver'] === 'pgsql' ? ' selected' : '' ?>>PostgreSQL</option><option value="sqlite"<?= $values['db_driver'] === 'sqlite' ? ' selected' : '' ?>>SQLite</option></select></div>
                <div><label for="db_host">Host</label><input id="db_host" name="db_host" value="<?= installField('db_host') ?>" required></div>
                <div><label for="db_port">Port</label><input id="db_port" name="db_port" type="number" min="1" max="65535" value="<?= installField('db_port') ?>" required></div>
                <div><label for="db_name">Database name</label><input id="db_name" name="db_name" value="<?= installField('db_name') ?>" required></div>
                <div><label for="db_username">Username</label><input id="db_username" name="db_username" value="<?= installField('db_username') ?>" required></div>
                <div><label for="db_password">Password</label><input id="db_password" name="db_password" type="password"></div>
                <div><label><input type="checkbox" name="db_create" value="1"<?= $values['db_create'] === '1' ? ' checked' : '' ?>> Create the database if it does not exist</label><p>The database account must have permission to create databases. Disable this when using a restricted application account.</p></div>
                <div><label for="db_path">SQLite path</label><input id="db_path" name="db_path" value="<?= installField('db_path') ?>"><p>Used only with SQLite. Relative paths are inside the project directory.</p></div>
            </div></fieldset>
            <fieldset><legend>Administrator</legend><div class="grid">
                <div><label for="admin_email">Email</label><input id="admin_email" name="admin_email" type="email" value="<?= installField('admin_email') ?>" required></div>
                <div><label for="admin_password">Password</label><input id="admin_password" name="admin_password" type="password" minlength="12" required></div>
            </div></fieldset>
            <fieldset><legend>First Minecraft server</legend><div class="grid">
                <div><label for="server_name">Name</label><input id="server_name" name="server_name" value="<?= installField('server_name') ?>" required></div>
                <div><label for="server_address">Address</label><input id="server_address" name="server_address" placeholder="mc.example.com" value="<?= installField('server_address') ?>" required></div>
                <div><label for="server_port">Port</label><input id="server_port" name="server_port" type="number" min="1" max="65535" value="<?= installField('server_port') ?>" required></div>
                <div><label for="minecraft_version">Minecraft version</label><input id="minecraft_version" name="minecraft_version" placeholder="1.21.1" value="<?= installField('minecraft_version') ?>" required></div>
                <div><label for="loader">Loader</label><select id="loader" name="loader"><?php foreach (['vanilla', 'paper', 'fabric', 'forge', 'neoforge'] as $loader): ?><option value="<?= $loader ?>"<?= $values['loader'] === $loader ? ' selected' : '' ?>><?= ucfirst($loader) ?></option><?php endforeach; ?></select></div>
                <div><label for="memory_mb">Memory (MB)</label><input id="memory_mb" name="memory_mb" type="number" min="512" max="1048576" value="<?= installField('memory_mb') ?>" required></div>
                <div><label for="systemd_unit">systemd unit</label><input id="systemd_unit" name="systemd_unit" value="<?= installField('systemd_unit') ?>" required></div>
            </div></fieldset>
            <fieldset><legend>Management daemon</legend><div class="grid">
                <div><label for="daemon_url">Daemon URL</label><input id="daemon_url" name="daemon_url" type="url" value="<?= installField('daemon_url') ?>" required></div>
                <div><label for="daemon_token">Daemon token</label><input id="daemon_token" name="daemon_token" type="password" required></div>
                <div><label for="upload_mode">Upload mode</label><select id="upload_mode" name="upload_mode"><option value="hardened"<?= $values['upload_mode'] === 'hardened' ? ' selected' : '' ?>>Hardened</option><option value="relaxed"<?= $values['upload_mode'] === 'relaxed' ? ' selected' : '' ?>>Relaxed</option></select></div>
            </div></fieldset>
            <button type="submit">Install panel</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>