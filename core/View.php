<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $path = __DIR__ . '/../view/' . $view . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Vue introuvable: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}
