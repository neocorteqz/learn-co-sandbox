<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/daemon.php';

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET' && $path === '/api/health') {
        database()->query('SELECT 1');
        echo json_encode(['ok' => true, 'database' => 'connected']);
        exit;
    }

    if ($method === 'GET' && $path === '/api/servers') {
        $servers = database()->query(
            'SELECT id, name, address, minecraft_version, loader, status, memory_mb, port FROM servers ORDER BY id DESC'
        )->fetchAll();

        foreach ($servers as &$server) {
            try {
                $liveStatus = daemonRequest('GET', '/servers/' . (int) $server['id'] . '/status');
                $server['status'] = (string) ($liveStatus['status'] ?? 'unknown');
            } catch (Throwable) {
                $server['status'] = 'unknown';
            }
        }
        unset($server);

        echo json_encode(['data' => $servers]);
        exit;
    }

    if ($method === 'GET' && preg_match('#^/api/servers/(\d+)/status$#', $path, $matches) === 1) {
        $serverId = (int) $matches[1];
        $server = database()->prepare('SELECT id FROM servers WHERE id = ?');
        $server->execute([$serverId]);
        if ($server->fetch() === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Server not found']);
            exit;
        }

        echo json_encode(['data' => daemonRequest('GET', '/servers/' . $serverId . '/status')]);
        exit;
    }

    if ($method === 'POST' && preg_match('#^/api/servers/(\d+)/(start|stop|restart)$#', $path, $matches) === 1) {
        $serverId = (int) $matches[1];
        $action = $matches[2];
        $server = database()->prepare('SELECT id FROM servers WHERE id = ?');
        $server->execute([$serverId]);
        if ($server->fetch() === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Server not found']);
            exit;
        }

        echo json_encode(['data' => daemonRequest('POST', '/servers/' . $serverId . '/actions', $action)]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
} catch (Throwable $error) {
    http_response_code(503);
    echo json_encode(['error' => 'Service unavailable']);
}
