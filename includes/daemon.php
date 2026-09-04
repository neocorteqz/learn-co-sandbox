<?php

declare(strict_types=1);

function daemonRequest(string $method, string $path, ?string $action = null): array
{
    $config = require __DIR__ . '/config.php';
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $config['daemon_token'],
    ];
    if ($action !== null) {
        $headers[] = 'X-Action: ' . $action;
    }

    $request = curl_init(rtrim($config['daemon_url'], '/') . $path);
    curl_setopt_array($request, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($request);
    $status = (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE);
    $error = curl_error($request);
    curl_close($request);

    if ($body === false || $error !== '') {
        throw new RuntimeException('Management daemon unavailable');
    }

    $payload = json_decode($body, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Management daemon returned invalid JSON');
    }

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException((string) ($payload['error'] ?? 'Management daemon request failed'));
    }

    return $payload;
}