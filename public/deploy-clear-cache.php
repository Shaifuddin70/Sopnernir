<?php

/**
 * One-shot cache clearer for GUI-only shared hosting (no SSH).
 *
 * Usage (then DELETE this file):
 *   https://your-domain.com/deploy-clear-cache.php?token=YOUR_APP_KEY
 *
 * Token must match APP_KEY from .env (with or without the base64: prefix).
 */

declare(strict_types=1);

function deploy_clear_env(string $root): array
{
    $path = $root.'/.env';
    if (! is_file($path)) {
        return [];
    }

    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $vars[trim($key)] = trim($value, " \t\"'");
    }

    return $vars;
}

function deploy_clear_tree(string $path): int
{
    if (! file_exists($path)) {
        return 0;
    }

    if (is_file($path) || is_link($path)) {
        return unlink($path) ? 1 : 0;
    }

    $count = 0;
    $items = scandir($path) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $count += deploy_clear_tree($path.DIRECTORY_SEPARATOR.$item);
    }

    return $count;
}

$root = dirname(__DIR__);
$env = deploy_clear_env($root);
$appKey = (string) ($env['APP_KEY'] ?? '');
$token = (string) ($_GET['token'] ?? '');

$candidates = array_values(array_filter([
    $appKey,
    str_starts_with($appKey, 'base64:') ? substr($appKey, 7) : null,
    $appKey !== '' && ! str_starts_with($appKey, 'base64:') ? 'base64:'.$appKey : null,
]));

$ok = $appKey !== '' && $token !== '' && array_reduce(
    $candidates,
    static fn (bool $carry, string $candidate): bool => $carry || hash_equals($candidate, $token),
    false
);

header('Content-Type: text/plain; charset=UTF-8');

if (! $ok) {
    http_response_code(403);
    echo "Forbidden. Pass ?token= matching APP_KEY from .env\n";
    exit;
}

$removed = 0;
$targets = [
    $root.'/bootstrap/cache/config.php',
    $root.'/bootstrap/cache/routes-v7.php',
    $root.'/bootstrap/cache/routes.php',
    $root.'/bootstrap/cache/events.php',
];

foreach ($targets as $target) {
    if (is_file($target) && unlink($target)) {
        $removed++;
        echo "Removed {$target}\n";
    }
}

foreach (glob($root.'/bootstrap/cache/routes-*.php') ?: [] as $routeCache) {
    if (is_file($routeCache) && unlink($routeCache)) {
        $removed++;
        echo "Removed {$routeCache}\n";
    }
}

$viewDir = $root.'/storage/framework/views';
$viewCount = deploy_clear_tree($viewDir);
$removed += $viewCount;
echo "Cleared {$viewCount} compiled view file(s) in storage/framework/views\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset requested\n";
}

echo "Done. Removed/cleared {$removed} item(s).\n";
echo "Delete public/deploy-clear-cache.php now.\n";
