<?php

require_once __DIR__ . '/data.php';

function cityzen_base_path(): string
{
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $lastSegment = basename($script);

    if (in_array($lastSegment, ['admin', 'api', 'includes', 'controller', 'controller', 'api'], true)) {
        $script = dirname($script);
    }

    $script = str_replace('\\', '/', $script);

    if ($script === '.' || $script === '\\' || $script === '/') {
        return '';
    }

    return rtrim($script, '/');
}

function cityzen_asset(string $path): string
{
    return cityzen_base_path() . '/' . ltrim($path, '/');
}

function cityzen_request_origin(): string
{
    $publicOrigin = trim((string) (getenv('CITYZEN_PUBLIC_ORIGIN') ?: getenv('CITYZEN_PUBLIC_BASE_URL')));
    if ($publicOrigin !== '' && preg_match('~^https?://~i', $publicOrigin) === 1) {
        return rtrim($publicOrigin, '/');
    }

    $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    $https = $forwardedProto === 'https'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

    $scheme = $https ? 'https' : 'http';
    $host = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));

    if ($host === '') {
        $serverName = trim((string) ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        $port = (int) ($_SERVER['SERVER_PORT'] ?? ($https ? 443 : 80));
        $isDefaultPort = ($https && $port === 443) || (!$https && $port === 80);
        $host = $serverName;
        if ($serverName !== '' && !$isDefaultPort) {
            $host .= ':' . $port;
        }
    }

    $hostNoPort = strtolower((string) preg_replace('~:\d+$~', '', $host));
    if (in_array($hostNoPort, ['localhost', '127.0.0.1', '::1'], true)) {
        $ip = trim((string) ($_SERVER['SERVER_ADDR'] ?? ''));
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1') {
            $ip = gethostbyname(gethostname());
        }
        if (is_string($ip) && $ip !== '' && $ip !== '127.0.0.1') {
            $port = (int) ($_SERVER['SERVER_PORT'] ?? ($https ? 443 : 80));
            $isDefaultPort = ($https && $port === 443) || (!$https && $port === 80);
            $host = $ip . ($isDefaultPort ? '' : ':' . $port);
        }
    }

    return $scheme . '://' . $host;
}

function cityzen_absolute_url(string $path): string
{
    if (preg_match('~^https?://~i', $path) === 1) {
        return $path;
    }

    return cityzen_request_origin() . '/' . ltrim($path, '/');
}

function cityzen_icon(string $status): string
{
    return match ($status) {
        'urgent' => '!',
        'progress' => '+',
        'new' => 'N',
        'done' => 'R',
        default => '.',
    };
}

function cityzen_render_head(string $title): void
{
    $app = (string) (($GLOBALS['cityzen']['app_name'] ?? '') ?: (getenv('CITYZEN_APP_NAME') ?: 'projet'));
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= htmlspecialchars($title) ?> | <?= htmlspecialchars($app) ?></title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="<?= htmlspecialchars(cityzen_asset('assets/css/style.css')) ?>">
    </head>
    <body>
    <?php
}

function cityzen_render_footer(): void
{
    ?>
      <script src="<?= htmlspecialchars(cityzen_asset('assets/js/app.js')) ?>"></script>
    </body>
    </html>
    <?php
}

require_once __DIR__ . '/auth.php';
