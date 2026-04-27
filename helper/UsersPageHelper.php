<?php

declare(strict_types=1);

namespace App\Helpers;

final class UsersPageHelper
{
    public static function buildQuery(array $get, array $override): string
    {
        $allowed = ['q', 'sort', 'dir', 'page', 'per_page', 'edit'];
        $params = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $override)) {
                $value = (string) $override[$key];
                if ($value !== '') {
                    $params[$key] = $value;
                }
                continue;
            }

            if (isset($get[$key]) && (string) $get[$key] !== '') {
                $params[$key] = (string) $get[$key];
            }
        }

        return http_build_query($params);
    }

    public static function url(string $base, array $get, array $override): string
    {
        $query = self::buildQuery($get, $override);

        return $query === '' ? $base : $base . '?' . $query;
    }

    public static function nextDir(string $column, string $currentSort, string $currentDir): string
    {
        if ($column === $currentSort) {
            return $currentDir === 'ASC' ? 'DESC' : 'ASC';
        }

        return ($column === 'id' || $column === 'created_at') ? 'DESC' : 'ASC';
    }
}
