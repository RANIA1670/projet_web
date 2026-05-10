<?php

declare(strict_types=1);

/**
 * Clés optionnelles météo / cartes pour le tableau de bord événements.
 * Vous pouvez aussi définir OPENWEATHER_API_KEY dans storage/local.env.
 */

$wz = getenv('OPENWEATHER_API_KEY');
$gm = getenv('GOOGLE_MAPS_API_KEY');

if (!defined('WEATHER_API_KEY')) {
    define('WEATHER_API_KEY', is_string($wz) ? trim($wz) : '');
}
if (!defined('GOOGLE_MAPS_API_KEY')) {
    define('GOOGLE_MAPS_API_KEY', is_string($gm) ? trim($gm) : '');
}
