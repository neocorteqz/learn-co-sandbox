<?php

declare(strict_types=1);

$configPath = __DIR__ . '/config.php';
$config = is_readable($configPath) ? require $configPath : [];
$apiUrl = rtrim((string) ($config['api_url'] ?? ''), '/');
$apiToken = (string) ($config['api_token'] ?? '');
$servers = [];
$error = null;

if ($apiUrl === '' || $apiToken === '') {
    $error = 'The plugin is not configured yet.';
} else {
    $request = curl_init($apiUrl . '/servers');
    curl_setopt_array($request, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $apiToken],
        CURLOPT_CONNECTTIMEOUT => (int) ($config['connect_timeout'] ?? 3),
        CURLOPT_TIMEOUT => (int) ($config['timeout'] ?? 8),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($request);
    $status = (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE);
    curl_close($request);

    if ($body === false || $status < 200 || $status >= 300) {
        $error = 'The panel API could not be reached.';
    } else {
        $payload = json_decode($body, true);
        $servers = is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minecraft Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="panel">
        <header class="header">
            <div>
                <p class="eyebrow">DirectAdmin plugin</p>
                <h1>Minecraft servers</h1>
            </div>
            <a class="button" href="<?= escape($apiUrl) ?>" target="_blank" rel="noopener">Open panel</a>
        </header>

        <?php if ($error !== null): ?>
            <div class="notice error"><?= escape($error) ?></div>
        <?php elseif ($servers === []): ?>
            <div class="notice">No Minecraft servers are assigned to this account.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Name</th><th>Version</th><th>Loader</th><th>Status</th><th>Port</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($servers as $server): ?>
                        <tr>
                            <td><?= escape((string) ($server['name'] ?? 'Unnamed server')) ?></td>
                            <td><?= escape((string) ($server['minecraft_version'] ?? 'Unknown')) ?></td>
                            <td><?= escape((string) ($server['loader'] ?? 'vanilla')) ?></td>
                            <td><span class="status status-<?= escape((string) ($server['status'] ?? 'unknown')) ?>"><?= escape((string) ($server['status'] ?? 'unknown')) ?></span></td>
                            <td><?= escape((string) ($server['port'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
