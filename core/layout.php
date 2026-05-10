<?php

require_once __DIR__ . '/data.php';

function cityzen_base_path(): string
{
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $lastSegment = basename($script);

    // Scripts under these folders live one level below site root; assets/ is at project root
    // (omit the folder from the URL prefix so cityzen_asset('assets/css/style.css') resolves).
    if (in_array($lastSegment, ['admin', 'api', 'includes', 'controller', 'equipment', 'forum', 'events'], true)) {
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

    // Keep the same host as the page (HTTP_HOST): do not rewrite localhost → LAN IP.
    // Otherwise absolute URLs used for inscription QR (« Ouvrir le lien QR ») open on a
    // different host, PHP uses a separate session cookie, and ?qr= no longer matches the
    // token stored during register.php loading. Pour un lien scannable depuis un téléphone,
    // définir CITYZEN_PUBLIC_ORIGIN dans storage/local.env (ex. http://192.168.1.X/projet_web).

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

function cityzen_render_head(string $title, array $extraStyles = [], string $bodyClass = ''): void
{
    $app = (string) (($GLOBALS['cityzen']['app_name'] ?? '') ?: (getenv('CITYZEN_APP_NAME') ?: 'projet'));
    $bodyClassAttr = $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass) . '"' : '';
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
      <?php foreach ($extraStyles as $href): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars((string) $href) ?>">
      <?php endforeach; ?>
    </head>
    <body<?= $bodyClassAttr ?>>
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
