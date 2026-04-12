<?php

declare(strict_types=1);

function bo_url(string $route, array $params = []): string
{
    $params['route'] = $route;
    return cityzen_asset('admin/equipment.php') . '?' . http_build_query($params);
}
