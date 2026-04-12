<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

cityzen_agent_logout();

header('Location: ' . cityzen_asset('index.php'), true, 302);
exit;
