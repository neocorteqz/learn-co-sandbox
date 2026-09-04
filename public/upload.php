<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../includes/config.php';
$mode = (string) ($config['uploads']['mode'] ?? 'hardened');
$profiles = [
    'hardened' => [
        'max_bytes' => 10 * 1024 * 1024,
        'extensions' => ['cfg', 'conf', 'json', 'properties', 'txt', 'yml', 'yaml'],
    ],
    'relaxed' => [
        'max_bytes' => 512 * 1024 * 1024,
        'extensions' => ['cfg', 'conf', 'jar', 'json', 'log', 'properties', 'schematic', 'txt', 'yml', 'yaml', 'zip'],
    ],
];

function upload_response(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    upload_response(405, ['error' => 'POST is required']);
}

if (!isset($profiles[$mode])) {
    upload_response(500, ['error' => 'Invalid upload mode']);
}

$file = $_FILES['file'] ?? null;
if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    upload_response(400, ['error' => 'A valid file upload is required']);
}

$originalName = (string) ($file['name'] ?? '');
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$size = (int) ($file['size'] ?? 0);
$temporaryPath = (string) ($file['tmp_name'] ?? '');
$serverId = (string) ($_POST['server_id'] ?? '');

if (!preg_match('/^\d+$/', $serverId)) {
    upload_response(422, ['error' => 'A numeric server_id is required']);
}

if ($size < 1 || $size > $profiles[$mode]['max_bytes']) {
    upload_response(413, ['error' => 'File exceeds the limit for the configured upload mode']);
}

if (!in_array($extension, $profiles[$mode]['extensions'], true)) {
    upload_response(415, ['error' => 'File type is not allowed in the configured upload mode']);
}

if (!is_uploaded_file($temporaryPath)) {
    upload_response(400, ['error' => 'Upload validation failed']);
}

$uploadRoot = (string) ($config['uploads']['root'] ?? dirname(__DIR__) . '/storage/uploads');
$targetDirectory = $uploadRoot . DIRECTORY_SEPARATOR . $serverId;
if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
    upload_response(500, ['error' => 'Upload directory could not be created']);
}

$storedName = bin2hex(random_bytes(16)) . '.' . $extension;
$targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $storedName;
if (!move_uploaded_file($temporaryPath, $targetPath)) {
    upload_response(500, ['error' => 'File could not be stored']);
}

chmod($targetPath, 0640);
upload_response(201, [
    'ok' => true,
    'mode' => $mode,
    'server_id' => (int) $serverId,
    'filename' => $storedName,
    'bytes' => $size,
]);
