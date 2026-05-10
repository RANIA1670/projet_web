<?php
/**
 * EnvironmentalWeatherService
 * Service responsable de la corrélation environnementale et météorologique
 * 
 * Workflow :
 * 1. Géocoder une adresse (Nominatim OpenStreetMap)
 * 2. Récupérer les données météo actuelles (OpenWeatherMap)
 * 3. Analyser le contenu pour détecter des tags IA
 * 4. Appliquer la logique métier pour déterminer le statut du post
 */

class EnvironmentalWeatherService
{
    // ========================
    // Configuration des APIs
    // ========================
    
    /**
     * Clé API OpenWeatherMap
     * TODO: Insérez votre clé API ici
     * Obtenir une clé gratuite sur : https://openweathermap.org/api
     */
    private const OPENWEATHER_API_KEY = 'YOUR_OPENWEATHER_API_KEY_HERE';
    
    /**
     * URL de base Nominatim (géocoding OpenStreetMap)
     * Nominatim est gratuit et sans clé d'API requise
     */
    private const NOMINATIM_BASE_URL = 'https://nominatim.openstreetmap.org/search';
    
    /**
     * URL de base OpenWeatherMap
     */
    private const OPENWEATHER_BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';
    
    /**
     * Timeout pour les appels cURL (secondes)
     */
    private const CURL_TIMEOUT = 10;
    
    // ========================
    // Mapping des statuts
    // ========================
    
    private const STATUS_NORMAL = 'Actif';           // ID 0 par défaut
    private const STATUS_ALERT_CLIMATE = 'Alerte Climatique';  // ID 2
    
    /**
     * Tags IA détectables dans le contenu
     * Format : [tag_name => keywords]
     */
    private const AI_TAGS = [
        'Inondation' => ['eau', 'inondation', 'inondé', 'inondés', 'inondées', 'noyade', 'flood', 'submerge', 'submergé', 'submergée'],
        'Tempête' => ['tempête', 'orage', 'tonnerre', 'éclair', 'foudre', 'storm', 'thunder', 'lightning'],
        'Sécheresse' => ['sécheresse', 'sec', 'aride', 'drought', 'dry', 'manque d\'eau'],
        'Neige' => ['neige', 'enneigé', 'avalanche', 'snow', 'avalanche'],
    ];
    
    /**
     * Conditions météo critiques
     */
    private const CRITICAL_WEATHER = ['Rain', 'Thunderstorm', 'Snow', 'Mist', 'Smoke', 'Haze', 'Dust', 'Fog'];
    
    // ========================
    // 1. GÉOCODING (Nominatim)
    // ========================
    
    /**
     * Transforme une adresse en coordonnées (Latitude, Longitude)
     * 
     * @param string $address Adresse à géocoder (ex: "5 avenue des Champs, Paris")
     * @return array|null ['latitude' => float, 'longitude' => float, 'display_name' => string] ou null si erreur
     */
    public static function geocodeAddress(string $address): ?array
    {
        if (trim($address) === '') {
            error_log('EnvironmentalWeatherService::geocodeAddress - Address is empty');
            return null;
        }

        try {
            // Préparation des paramètres
            $params = [
                'q' => trim($address),
                'format' => 'json',
                'limit' => 1
            ];

            $url = self::NOMINATIM_BASE_URL . '?' . http_build_query($params);
            
            // Appel cURL
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
                CURLOPT_USERAGENT => 'ForumCityZen/1.0', // Nominatim l'exige
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("EnvironmentalWeatherService::geocodeAddress - cURL Error: $curlError");
                return null;
            }

            if ($httpCode !== 200) {
                error_log("EnvironmentalWeatherService::geocodeAddress - HTTP $httpCode");
                return null;
            }

            $data = json_decode($response, true);

            if (!is_array($data) || empty($data)) {
                error_log("EnvironmentalWeatherService::geocodeAddress - No results from Nominatim");
                return null;
            }

            $first = $data[0];
            return [
                'latitude' => (float)$first['lat'],
                'longitude' => (float)$first['lon'],
                'display_name' => (string)($first['display_name'] ?? 'Unknown location')
            ];

        } catch (Throwable $e) {
            error_log("EnvironmentalWeatherService::geocodeAddress - Exception: " . $e->getMessage());
            return null;
        }
    }

    // ========================
    // 2. MÉTÉO (OpenWeatherMap)
    // ========================

    /**
     * Récupère les conditions météo actuelles
     * 
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @return array|null ['main' => string, 'description' => string, 'temperature' => float, 'humidity' => int] ou null
     */
    public static function getWeatherCondition(float $latitude, float $longitude): ?array
    {
        if (self::OPENWEATHER_API_KEY === 'YOUR_OPENWEATHER_API_KEY_HERE') {
            error_log('EnvironmentalWeatherService::getWeatherCondition - API key not configured');
            // Retourner un objet par défaut pour les tests
            return [
                'main' => 'Clear',
                'description' => 'clear sky',
                'temperature' => 20.0,
                'humidity' => 60
            ];
        }

        try {
            $params = [
                'lat' => $latitude,
                'lon' => $longitude,
                'appid' => self::OPENWEATHER_API_KEY,
                'units' => 'metric'
            ];

            $url = self::OPENWEATHER_BASE_URL . '?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("EnvironmentalWeatherService::getWeatherCondition - cURL Error: $curlError");
                return null;
            }

            if ($httpCode !== 200) {
                error_log("EnvironmentalWeatherService::getWeatherCondition - HTTP $httpCode");
                return null;
            }

            $data = json_decode($response, true);

            if (!is_array($data) || !isset($data['weather'][0])) {
                error_log("EnvironmentalWeatherService::getWeatherCondition - Invalid response");
                return null;
            }

            $weather = $data['weather'][0];
            $main = $data['main'] ?? [];

            return [
                'main' => (string)$weather['main'],
                'description' => (string)$weather['description'],
                'temperature' => (float)($main['temp'] ?? 0),
                'humidity' => (int)($main['humidity'] ?? 0)
            ];

        } catch (Throwable $e) {
            error_log("EnvironmentalWeatherService::getWeatherCondition - Exception: " . $e->getMessage());
            return null;
        }
    }

    // ========================
    // 3. ANALYSE IA (Simulation)
    // ========================

    /**
     * Analyse le contenu pour détecter des tags IA
     * Simule une IA en cherchant des mots-clés
     * 
     * @param string $content Contenu à analyser
     * @param string $title Titre du post (optionnel, augmente la pertinence)
     * @return string|null Tag détecté ou null
     */
    public static function analyzeContentWithAI(string $content, string $title = ''): ?string
    {
        $textToAnalyze = strtolower(trim($content . ' ' . $title));

        if ($textToAnalyze === '') {
            return null;
        }

        // Parcourir les tags et leurs mots-clés
        foreach (self::AI_TAGS as $tag => $keywords) {
            foreach ($keywords as $keyword) {
                // Recherche du mot-clé avec délimiteurs de mots
                $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
                if (preg_match($pattern, $textToAnalyze)) {
                    error_log("EnvironmentalWeatherService::analyzeContentWithAI - Tag detected: $tag (keyword: $keyword)");
                    return $tag;
                }
            }
        }

        return null;
    }

    // ========================
    // 4. LOGIQUE MÉTIER
    // ========================

    /**
     * Détermine le statut d'un post basé sur la corrélation entre le tag IA et la météo
     * 
     * Logique :
     * - Si (Tag IA == 'Inondation') ET (Météo == 'Rain' OU 'Thunderstorm')
     *   => Status = 'Alerte Climatique'
     * - Sinon => Status = 'Actif'
     * 
     * @param string|null $aiTag Tag détecté (ex: 'Inondation', 'Tempête', etc.)
     * @param string|null $weatherMain Condition météo principale (ex: 'Rain', 'Clear', etc.)
     * @return string Status du post ('Actif' ou 'Alerte Climatique')
     */
    public static function determinePostStatus(?string $aiTag, ?string $weatherMain): string
    {
        // Vérification de la corrélation
        if ($aiTag === 'Inondation' && in_array($weatherMain, ['Rain', 'Thunderstorm'], true)) {
            error_log("EnvironmentalWeatherService::determinePostStatus - Climate alert triggered (Tag: $aiTag, Weather: $weatherMain)");
            return self::STATUS_ALERT_CLIMATE;
        }

        return self::STATUS_NORMAL;
    }

    // ========================
    // 5. WORKFLOW COMPLET
    // ========================

    /**
     * Workflow complet : adresse → géocodage → météo → analyse IA → statut
     * 
     * @param string $address Adresse fournie par l'utilisateur
     * @param string $content Contenu du post
     * @param string $title Titre du post
     * @return array Résultat complet du workflow
     */
    public static function processEnvironmentalData(string $address, string $content, string $title = ''): array
    {
        $result = [
            'success' => false,
            'latitude' => null,
            'longitude' => null,
            'weather_main' => null,
            'weather_description' => null,
            'temperature' => null,
            'humidity' => null,
            'ai_tag' => null,
            'status' => self::STATUS_NORMAL,
            'display_address' => null,
            'errors' => []
        ];

        // Étape 1 : Géocoding
        if (trim($address) !== '') {
            $geoResult = self::geocodeAddress($address);
            if ($geoResult) {
                $result['latitude'] = $geoResult['latitude'];
                $result['longitude'] = $geoResult['longitude'];
                $result['display_address'] = $geoResult['display_name'];
            } else {
                $result['errors'][] = 'Geocoding failed for address: ' . $address;
            }
        }

        // Étape 2 : Météo (si coordonnées disponibles)
        if ($result['latitude'] !== null && $result['longitude'] !== null) {
            $weatherResult = self::getWeatherCondition($result['latitude'], $result['longitude']);
            if ($weatherResult) {
                $result['weather_main'] = $weatherResult['main'];
                $result['weather_description'] = $weatherResult['description'];
                $result['temperature'] = $weatherResult['temperature'];
                $result['humidity'] = $weatherResult['humidity'];
            } else {
                $result['errors'][] = 'Failed to retrieve weather data';
            }
        }

        // Étape 3 : Analyse IA
        $aiTag = self::analyzeContentWithAI($content, $title);
        if ($aiTag) {
            $result['ai_tag'] = $aiTag;
        }

        // Étape 4 : Détermination du statut
        $result['status'] = self::determinePostStatus($result['ai_tag'], $result['weather_main']);

        $result['success'] = true;

        // Log complet
        error_log("EnvironmentalWeatherService::processEnvironmentalData - Result: " . json_encode($result));

        return $result;
    }

    // ========================
    // Utilitaires
    // ========================

    /**
     * Vérifie si une condition météo est critique
     * @param string $weatherMain
     * @return bool
     */
    public static function isWeatherCritical(string $weatherMain): bool
    {
        return in_array($weatherMain, self::CRITICAL_WEATHER, true);
    }

    /**
     * Retourne les keywords associés à un tag
     * @param string $tag
     * @return array|null
     */
    public static function getKeywordsForTag(string $tag): ?array
    {
        return self::AI_TAGS[$tag] ?? null;
    }

    /**
     * Retourne tous les tags disponibles
     * @return array
     */
    public static function getAllTags(): array
    {
        return array_keys(self::AI_TAGS);
    }
}
