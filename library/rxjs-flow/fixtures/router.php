<?php

declare(strict_types=1);

/**
 * Fixture router for `php -S`.
 * Serves static files from this directory; /fail returns 404 for a deliberate error item.
 * Small artificial delays make concurrent overlap visible in the trace.
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/fail' || $path === '/fail.png') {
    usleep(80_000);
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "not found\n";

    return true;
}

$delays = [
    '/image-a.png' => 200_000,
    '/image-b.png' => 50_000,
    '/image-c.png' => 150_000,
    '/image-d.png' => 80_000,
    '/image-e.png' => 120_000,
];

if (isset($delays[$path])) {
    usleep($delays[$path]);
}

$file = __DIR__.$path;
if ($path !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = [
        'png' => 'image/png',
        'bin' => 'application/octet-stream',
        'txt' => 'text/plain',
    ];
    header('Content-Type: '.$types[$ext] ?? 'application/octet-stream');
    header('Content-Length: '.(string) filesize($file));
    readfile($file);

    return true;
}

http_response_code(404);
header('Content-Type: text/plain');
echo "missing\n";

return true;
