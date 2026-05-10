<?php
// ================================================
//  FICHIER  : services/WeatherMapService.php
//  RÔLE     : Récupère la météo et fournit les URLs de cartographie
// ================================================

class WeatherMapService
{
    public static function getWeatherForLocation(string $location): ?array
    {
        $apiKey = self::getWeatherApiKey();
        if (empty($apiKey)) {
            return null;
        }

        $query = urlencode($location);
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$query}&units=metric&lang=fr&appid={$apiKey}";
        $data = self::fetchJson($url);

        if (empty($data['weather'][0]) || empty($data['main']['temp'])) {
            return null;
        }

        return [
            'temp' => round($data['main']['temp']),
            'description' => ucfirst($data['weather'][0]['description'] ?? ''),
            'icon' => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png',
        ];
    }

    public static function getStaticMapUrl(string $location): string
    {
        $query = urlencode($location);
        $apiKey = self::getGoogleMapsApiKey();

        $url = 'https://maps.googleapis.com/maps/api/staticmap';
        $params = [
            'center' => $query,
            'zoom' => 13,
            'size' => '400x220',
            'scale' => 2,
            'markers' => 'color:red|' . $query,
            'maptype' => 'roadmap',
        ];

        if (!empty($apiKey)) {
            $params['key'] = $apiKey;
        }

        return $url . '?' . http_build_query($params);
    }

    public static function getGoogleMapsLink(string $location): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($location);
    }

    private static function getWeatherApiKey(): string
    {
        return defined('WEATHER_API_KEY') ? WEATHER_API_KEY : '';
    }

    private static function getGoogleMapsApiKey(): string
    {
        return defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
    }

    private static function fetchJson(string $url): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            return null;
        }

        $json = json_decode($response, true);
        return is_array($json) ? $json : null;
    }
}
