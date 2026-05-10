# 🔗 API Reference - Corrélation Environnementale

## 📋 Table des matières
1. [Service Principal](#service-principal)
2. [API Nominatim](#api-nominatim)
3. [API OpenWeatherMap](#api-openweathermap)
4. [Analyse IA](#analyse-ia)
5. [Exemples cURL](#exemples-curl)

---

## Service Principal

### Classe : `EnvironmentalWeatherService`

**Fichier :** `/services/EnvironmentalWeatherService.php`

**Constantes :**
```php
CURL_TIMEOUT = 10                          // Timeout en secondes
OPENWEATHER_API_KEY = 'YOUR_KEY_HERE'      // À configurer!
NOMINATIM_USER_AGENT = 'MyForum/1.0'       // User-Agent obligatoire
```

---

## API Nominatim

### Méthode : `geocodeAddress(string $address)`

**Description :** Convertit une adresse en coordonnées GPS

**Signature :**
```php
public static function geocodeAddress(string $address): ?array
```

**Paramètres :**
| Nom | Type | Exemple | Requis |
|-----|------|---------|--------|
| $address | string | "Paris, France" | ✅ |

**Réponse (succès) :**
```php
[
    'latitude' => 48.8566,
    'longitude' => 2.3522,
    'display_name' => 'Paris, Île-de-France, France'
]
```

**Réponse (erreur) :**
```php
null  // Retourne null si adresse non trouvée
```

**Exemples d'adresses valides :**
- "Paris, France"
- "5 Avenue des Champs-Élysées, Paris"
- "Barcelona, Spain"
- "Eiffel Tower, Paris"
- "10 Downing Street, London"

**API Utilisée :**
```
https://nominatim.openstreetmap.org/search
?q=Paris, France
&format=json
&limit=1
```

**Status HTTP :**
- 200 : OK
- 404 : Adresse non trouvée
- 429 : Rate limit (1 req/sec)

**Timeout :** 10 secondes

---

## API OpenWeatherMap

### Méthode : `getWeatherCondition(float $lat, float $lng)`

**Description :** Récupère la météo actuelle pour des coordonnées GPS

**Signature :**
```php
public static function getWeatherCondition(float $lat, float $lng): ?array
```

**Paramètres :**
| Nom | Type | Exemple | Plage | Requis |
|-----|------|---------|-------|--------|
| $lat | float | 48.8566 | -90 à 90 | ✅ |
| $lng | float | 2.3522 | -180 à 180 | ✅ |

**Réponse (succès) :**
```php
[
    'main' => 'Rain',                    // Condition météo
    'description' => 'moderate rain',   // Description détaillée
    'temperature' => 15.5,              // En Celsius
    'humidity' => 78                    // En pourcentage
]
```

**Réponse (erreur) :**
```php
null  // Retourne null si erreur API
```

**Conditions météo possibles (`main`) :**
| Condition | Exemple | Code |
|-----------|---------|------|
| Clouds | Nuageux | 04d |
| Clear | Ciel clair | 01d |
| Rain | Pluie | 10d |
| Thunderstorm | Orage | 11d |
| Snow | Neige | 13d |
| Drizzle | Bruine | 09d |
| Mist | Brume | 50d |

**API Utilisée :**
```
https://api.openweathermap.org/data/2.5/weather
?lat=48.8566
&lon=2.3522
&appid=YOUR_KEY
&units=metric
```

**Paramètres optionnels :**
- `units=metric` : Retourne température en Celsius
- `lang=fr` : Description en français (optionnel)

**Status HTTP :**
- 200 : OK
- 400 : Paramètres invalides
- 401 : Clé API invalide
- 429 : Rate limit dépassé

**Plan gratuit :**
- 1000 appels/jour
- Mise à jour: Toutes les 10 minutes

**Timeout :** 10 secondes

---

## Analyse IA

### Méthode : `analyzeContentWithAI(string $content, string $title)`

**Description :** Détecte des tags environnementaux dans le texte (simulation regex)

**Signature :**
```php
public static function analyzeContentWithAI(string $content, string $title): ?string
```

**Paramètres :**
| Nom | Type | Exemple | Requis |
|-----|------|---------|--------|
| $content | string | "L'eau monte..." | ✅ |
| $title | string | "Inondation" | ✅ |

**Réponse (succès) :**
```php
"Inondation"  // Tag détecté, ou null
```

**Tags détectables :**

| Tag | Mots clés (regex) |
|-----|------------------|
| Inondation | eau, inondation, inondé, submergé, noyade, flood, inundation, waterflow |
| Tempête | tempête, orage, tonnerre, éclair, storm, lightning, thunderstorm, downpour |
| Sécheresse | sécheresse, sec, aride, drought, dryness, parched, arid, désertion |
| Neige | neige, enneigé, verglas, avalanche, snow, blizzard, icy, avalanche, snowstorm |

**Sensibilité :** Insensible à la casse (case-insensitive)

**Priorité de détection (dans l'ordre) :**
1. Inondation (eau + variables)
2. Tempête (storm + variables)
3. Sécheresse (sec + variables)
4. Neige (snow + variables)

**Exemple :**
```php
$tag = analyzeContentWithAI(
    "Il y a beaucoup d'eau qui monte!",
    "Alerte inondation"
);
// Retour : "Inondation"
```

---

## Logique Métier

### Méthode : `determinePostStatus(string $tag, string $weather)`

**Description :** Détermine le statut du post basé sur corrélation tag/météo

**Signature :**
```php
public static function determinePostStatus(string $tag, string $weather): string
```

**Paramètres :**
| Nom | Type | Exemple | Requis |
|-----|------|---------|--------|
| $tag | string | "Inondation" | ✅ |
| $weather | string | "Rain" | ✅ |

**Réponse :**
```php
"Alerte Climatique"  // Si corrélation
"Actif"              // Par défaut
```

**Matrice de corrélation :**

| Tag | Météo | Résultat |
|-----|-------|----------|
| Inondation | Rain | ⚠️ **Alerte Climatique** |
| Inondation | Thunderstorm | ⚠️ **Alerte Climatique** |
| Inondation | Clear | Actif |
| Tempête | Rain | Actif |
| Tempête | Thunderstorm | ⚠️ **Alerte Climatique** |
| Sécheresse | Clear | ⚠️ **Alerte Climatique** |
| Sécheresse | Clouds | Actif |
| Neige | Snow | ⚠️ **Alerte Climatique** |
| * | * | Actif (défaut) |

**Logique complète :**
```php
IF ($tag === 'Inondation' && in_array($weather, ['Rain', 'Thunderstorm']))
    return 'Alerte Climatique';
ELSEIF ($tag === 'Tempête' && $weather === 'Thunderstorm')
    return 'Alerte Climatique';
ELSEIF ($tag === 'Sécheresse' && $weather === 'Clear')
    return 'Alerte Climatique';
ELSEIF ($tag === 'Neige' && $weather === 'Snow')
    return 'Alerte Climatique';
ELSE
    return 'Actif';
```

---

## Workflow Complet

### Méthode : `processEnvironmentalData(string $address, string $content, string $title)`

**Description :** Orchestre l'ensemble du workflow (géocoding → météo → IA → logique)

**Signature :**
```php
public static function processEnvironmentalData(
    string $address, 
    string $content, 
    string $title
): array
```

**Paramètres :**
| Nom | Type | Exemple | Requis |
|-----|------|---------|--------|
| $address | string | "Paris, France" | ✅ |
| $content | string | "L'eau monte..." | ✅ |
| $title | string | "Alerte" | ✅ |

**Réponse (succès) :**
```php
[
    'success' => true,
    'latitude' => 48.8566,
    'longitude' => 2.3522,
    'weather_main' => 'Rain',
    'weather_description' => 'moderate rain',
    'temperature' => 15.5,
    'humidity' => 78,
    'ai_tag' => 'Inondation',
    'display_address' => 'Paris, France',
    'status' => 'Alerte Climatique',
    'processing_time_ms' => 1250,
    'geocoding_time_ms' => 350,
    'weather_time_ms' => 450,
    'ai_time_ms' => 50
]
```

**Réponse (erreur) :**
```php
[
    'success' => false,
    'error' => 'Adresse non trouvée',
    'latitude' => null,
    'longitude' => null,
    'weather_main' => null,
    'ai_tag' => null,
    'status' => 'Actif'  // Status par défaut
]
```

---

## Exemples cURL

### 1. Tester Nominatim

```bash
curl -X GET \
  "https://nominatim.openstreetmap.org/search?q=Paris,France&format=json&limit=1" \
  -H "User-Agent: MyForum/1.0"
```

**Réponse :**
```json
[
  {
    "lat": "48.8565056",
    "lon": "2.3521884",
    "display_name": "Paris, Île-de-France, France"
  }
]
```

---

### 2. Tester OpenWeatherMap

**⚠️ Remplacez `YOUR_KEY` par votre clé API**

```bash
curl -X GET \
  "https://api.openweathermap.org/data/2.5/weather?lat=48.8566&lon=2.3522&appid=YOUR_KEY&units=metric"
```

**Réponse :**
```json
{
  "main": {
    "temp": 15.5,
    "humidity": 78,
    "pressure": 1013
  },
  "weather": [
    {
      "main": "Rain",
      "description": "moderate rain"
    }
  ]
}
```

---

### 3. Test en PHP

```php
<?php
require 'services/EnvironmentalWeatherService.php';

// Test workflow complet
$result = EnvironmentalWeatherService::processEnvironmentalData(
    "Paris, France",
    "L'eau monte, c'est une inondation!",
    "ALERTE : Inondation"
);

echo json_encode($result, JSON_PRETTY_PRINT);
?>
```

---

## Gestion des erreurs

### Erreurs possibles

| Erreur | Code | Cause | Solution |
|--------|------|-------|----------|
| No results from Nominatim | 400 | Adresse invalide | Vérifier l'adresse |
| API key not configured | 401 | Clé manquante | Configurer clé OpenWeatherMap |
| HTTP 401 Unauthorized | 401 | Clé invalide | Régénérer la clé |
| Operation timed out | 504 | Connexion lente | Vérifier internet |
| Rate limit exceeded | 429 | Trop d'appels | Attendre/cache |

### Try/Catch implémenté

```php
try {
    $result = EnvironmentalWeatherService::processEnvironmentalData(
        $address,
        $content,
        $title
    );
} catch (Throwable $e) {
    error_log("Erreur: " . $e->getMessage());
    // Continue avec status par défaut
}
```

---

## Performance

### Temps d'exécution par composant

| Composant | Temps | Timeout |
|-----------|-------|---------|
| Nominatim Geocoding | ~350ms | 10s |
| OpenWeatherMap API | ~450ms | 10s |
| Analyse IA (regex) | ~50ms | Synchro |
| Logique métier | ~10ms | Synchro |
| **Total workflow** | ~860ms | 10s |

### Optimisations

- ✅ Timeout 10s par API
- ✅ cURL asynchrone optionnel
- ✅ Mise en cache recommandée
- ✅ Rate limiting implicite (1 req/sec Nominatim)

---

## Sécurité API

### Headers requis

```php
'User-Agent: MyForum/1.0'       // Nominatim REQUIS
'Accept: application/json'      // Standard
'Timeout: 10'                   // cURL
```

### Validation

```php
// Validation d'adresse
if (strlen($address) < 3 || strlen($address) > 255) {
    throw new Exception("Adresse invalide");
}

// Validation de coordonnées
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    throw new Exception("Coordonnées invalides");
}
```

### Clé API

- ✅ Stockée en constante (pas de .env)
- ✅ Pas exposée aux clients
- ✅ À régénérer si compromise

---

## Monitoring

### Logs générés

```
[EnvironmentalWeatherService::geocodeAddress] Processing: Paris, France
[EnvironmentalWeatherService::getWeatherCondition] Retrieved: Rain (15.5°C)
[EnvironmentalWeatherService::analyzeContentWithAI] Detected tag: Inondation
[EnvironmentalWeatherService::determinePostStatus] Status: Alerte Climatique
```

### Métriques à tracker

- Nombre d'appels API par jour
- Succès/erreur par composant
- Temps de réponse moyen
- Posts créés avec corrélation détectée

---

**Dernière mise à jour :** Mai 2026  
**Version :** 1.0.0
