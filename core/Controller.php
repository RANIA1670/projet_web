<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function redirect(string $to, int $status = 302): never
    {
        header('Location: ' . $to, true, $status);
        exit;
    }
}
