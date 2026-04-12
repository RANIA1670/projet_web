<?php

require_once __DIR__ . '/data.php';

function cityzen_base_path(): string
{
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $lastSegment = basename($script);

    if (in_array($lastSegment, ['admin', 'api', 'includes'], true)) {
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

/**
 * @param  list<string>  $extraStylesheets  URLs absolues ou chemins (seront échappés)
 */
function cityzen_render_head(string $title, array $extraStylesheets = [], string $bodyClass = ''): void
{
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= htmlspecialchars($title) ?> | CityZen</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="<?= htmlspecialchars(cityzen_asset('assets/css/style.css')) ?>">
      <?php foreach ($extraStylesheets as $href): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
      <?php endforeach; ?>
    </head>
    <body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
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
