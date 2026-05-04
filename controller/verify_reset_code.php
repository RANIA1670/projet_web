<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Bootstrap.php';

App\Core\Bootstrap::init();

(new App\Controllers\AuthController())->verifyResetCode();
