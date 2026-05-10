# 🌍 Corrélation Environnementale et Météorologique - Documentation Complète

## 📋 Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Configuration des APIs](#configuration-des-apis)
4. [Workflow détaillé](#workflow-détaillé)
5. [Installation](#installation)
6. [Utilisation](#utilisation)
7. [Exemples](#exemples)
8. [Gestion des erreurs](#gestion-des-erreurs)
9. [Tests](#tests)

---

## Vue d'ensemble

Ce module implémente une **logique métier avancée** qui établit une corrélation entre le contenu des posts et les conditions environnementales/météorologiques réelles.

### Fonctionnalités principales

✅ **Géocoding** : Convertit une adresse en coordonnées (latitude/longitude)  
✅ **Météo en temps réel** : Récupère les conditions météo actuelles  
✅ **Analyse IA** : Détecte des thèmes environnementaux dans le contenu  
✅ **Logique métier** : Assigne automatiquement un statut "Alerte Climatique" si corrélation détectée  
✅ **Persistance** : Stocke toutes les données dans la base de données avec structure JSON  

---

## Architecture

### Structure des fichiers

```
/services/EnvironmentalWeatherService.php   ← Service principal (APIs + logique)
/controllers/ForumController.php             ← Contrôleur enrichi avec la logique métier
/models/Post.php                             ← Modèle Post enrichi avec nouvelles colonnes
/migrations/add_environmental_fields.sql     ← Migration BD
/docs/FORM_INTEGRATION_EXAMPLE.php           ← Exemple d'intégration formulaire
```

### Flux de données

```
Formulaire (Titre, Contenu, Adresse)
    ↓
ForumController::createPost()
    ↓
EnvironmentalWeatherService::processEnvironmentalData()
    ├─→ geocodeAddress()           [Nominatim API]
    ├─→ getWeatherCondition()      [OpenWeatherMap API]
    ├─→ analyzeContentWithAI()     [Analyse locale]
    └─→ determinePostStatus()      [Logique métier]
    ↓
Post::save()                          [Insertion en BDD]
```

---

## Configuration des APIs

### 1️⃣ API OpenStreetMap Nominatim (Gratuit, sans clé)

**URL** : `https://nominatim.openstreetmap.org/search`

✅ **Avantages**
- Gratuit et illimité
- Pas de clé API requise
- Données précises
- Respecte le droit d'auteur (OSM)

**Contrainte** : User-Agent obligatoire

**Documentation** : https://nominatim.org/

---

### 2️⃣ API OpenWeatherMap (Gratuit avec clé)

**URL** : `https://api.openweathermap.org/data/2.5/weather`

#### 📝 Étapes d'installation

1. Visitez https://openweathermap.org/api
2. Créez un compte gratuit
3. Générez une clé API
4. Attendez quelques minutes l'activation (parfois 10-20 min)
5. Copiez la clé dans `EnvironmentalWeatherService.php` ligne 20 :

```php
private const OPENWEATHER_API_KEY = 'YOUR_KEY_HERE';
```

**Exemple de clé** : `a1b2c3d4e5f6g7h8i9j0`

#### ✅ Plan gratuit

- 1000 appels/jour
- Accès à la météo actuelle
- Mise en cache recommandée (voir plus bas)

---

## Workflow détaillé

### Étape 1 : Géocoding (Nominatim)

```php
$geoResult = EnvironmentalWeatherService::geocodeAddress("5 Avenue des Champs, Paris");

// Résultat
[
    'latitude' => 48.8698,
    'longitude' => 2.3076,
    'display_name' => 'Avenue des Champs-Élysées, Paris, Île-de-France, 75008, France'
]
```

---

### Étape 2 : Récupération Météo (OpenWeatherMap)

```php
$weather = EnvironmentalWeatherService::getWeatherCondition(48.8698, 2.3076);

// Résultat
[
    'main' => 'Rain',
    'description' => 'moderate rain',
    'temperature' => 15.5,
    'humidity' => 78
]
```

---

### Étape 3 : Analyse IA (Locale - Simulation)

```php
$tag = EnvironmentalWeatherService::analyzeContentWithAI(
    "L'eau monte dangereusement, il y a des inondations partout...",
    "URGENCE : Inondation à Paris"
);

// Résultat
$tag = "Inondation"  // Détecté via regex sur keywords
```

**Tags disponibles**
- `Inondation` : eau, inondation, inondé, noyade, flood, etc.
- `Tempête` : tempête, orage, tonnerre, éclair, storm, etc.
- `Sécheresse` : sécheresse, sec, aride, drought, etc.
- `Neige` : neige, enneigé, avalanche, snow, etc.

---

### Étape 4 : Logique Métier (Détermination du statut)

```php
$status = EnvironmentalWeatherService::determinePostStatus(
    'Inondation',  // Tag IA
    'Rain'         // Condition météo
);

// Résultat
$status = 'Alerte Climatique'  // Corrélation détectée!
```

**Règle** :
```
IF (Tag IA == 'Inondation') 
   AND (Météo IN ['Rain', 'Thunderstorm'])
THEN Status = 'Alerte Climatique'
ELSE Status = 'Actif'
```

---

## Installation

### 1. Migration de la base de données

Exécutez le script SQL pour ajouter les colonnes manquantes :

```bash
cd /xampp/htdocs/web\ mardi
mysql -u root furum < migrations/add_environmental_fields.sql
```

**Colonnes ajoutées à `posts`**
- `latitude` (DECIMAL 10,8)
- `longitude` (DECIMAL 11,8)
- `weather_current` (VARCHAR 50)
- `ai_tag` (VARCHAR 100)
- `display_address` (VARCHAR 255)
- `metadata_env` (JSON)
- `status` (VARCHAR 50)

### 2. Configuration de l'API OpenWeatherMap

1. Inscrivez-vous sur https://openweathermap.org
2. Générez votre clé API
3. Éditez `services/EnvironmentalWeatherService.php` ligne 20 :

```php
private const OPENWEATHER_API_KEY = 'your_actual_key_here';
```

### 3. Vérification

```bash
# Test : Vérifier que les fichiers sont créés
ls -la services/EnvironmentalWeatherService.php
ls -la migrations/add_environmental_fields.sql
```

---

## Utilisation

### Méthode 1 : Via le contrôleur (Approche recommandée)

```php
// Dans votre logique de formulaire
$controller = new ForumController();

$success = $controller->createPost(
    userId: 1,
    title: "Post avec détection environnementale",
    content: "L'eau monte...",
    address: "5 Avenue des Champs, Paris"  // ← Optionnel
);

if ($success) {
    echo "✅ Post créé avec analyse environnementale!";
    // Le post a un status automatique basé sur la corrélation
}
```

### Méthode 2 : Appel direct du service

```php
use EnvironmentalWeatherService as EWS;

// Workflow complet
$result = EWS::processEnvironmentalData(
    address: "Paris, France",
    content: "Il y a beaucoup d'eau...",
    title: "Inondation"
);

echo "Status: " . $result['status'];           // "Alerte Climatique"
echo "Tag: " . $result['ai_tag'];              // "Inondation"
echo "Weather: " . $result['weather_main'];    // "Rain"
echo "Coordinates: " . $result['latitude'] . ", " . $result['longitude'];
```

### Méthode 3 : Appels unitaires

```php
// Juste le géocoding
$geo = EWS::geocodeAddress("Paris");

// Juste la météo
$weather = EWS::getWeatherCondition(48.8566, 2.3522);

// Juste l'analyse IA
$tag = EWS::analyzeContentWithAI("texte contenant inondation");

// Juste la logique métier
$status = EWS::determinePostStatus($tag, $weather['main']);
```

---

## Exemples

### Exemple 1 : Post sur une inondation sous la pluie

```php
$result = EnvironmentalWeatherService::processEnvironmentalData(
    "Paris, France",
    "L'eau monte dangereusement, les rues sont inondées!",
    "ALERTE : Inondation à Paris"
);

// Résultat
[
    'success' => true,
    'latitude' => 48.8566,
    'longitude' => 2.3522,
    'weather_main' => 'Rain',        // ← Pluie
    'ai_tag' => 'Inondation',        // ← Détecté
    'status' => 'Alerte Climatique'  // ← Corrélation!
]
```

### Exemple 2 : Post sur une inondation par beau temps

```php
$result = EnvironmentalWeatherService::processEnvironmentalData(
    "Marseille, France",
    "Hier, il y avait une inondation due aux travaux.",
    "Post historique"
);

// Résultat
[
    'success' => true,
    'latitude' => 43.2965,
    'longitude' => 5.3698,
    'weather_main' => 'Clear',    // ← Beau temps
    'ai_tag' => 'Inondation',     // ← Détecté
    'status' => 'Actif'           // ← Pas de corrélation (pas de pluie)
]
```

### Exemple 3 : Post sans adresse

```php
$success = $controller->createPost(
    userId: 1,
    title: "Mon post",
    content: "Contenu normal"
    // address omis ou null
);

// Résultat
// Post créé avec status par défaut 'Actif'
// Pas d'appel API
```

---

## Gestion des erreurs

### Erreurs courantes

#### 1. Clé API non configurée
```
ERROR: EnvironmentalWeatherService::getWeatherCondition - API key not configured
```
**Solution** : Configurez votre clé OpenWeatherMap dans le fichier service (ligne 20)

#### 2. Adresse non trouvée
```
ERROR: EnvironmentalWeatherService::geocodeAddress - No results from Nominatim
```
**Solution** : L'adresse est invalide ou Nominatim ne la reconnaît pas. Essayez une autre adresse.

#### 3. Timeout API
```
ERROR: EnvironmentalWeatherService::geocodeAddress - cURL Error: Operation timed out
```
**Solution** : Vérifiez votre connexion internet ou augmentez le timeout (ligne 29 : `CURL_TIMEOUT`)

#### 4. Erreur HTTP
```
ERROR: EnvironmentalWeatherService::getWeatherCondition - HTTP 401
```
**Solution** : Votre clé API est invalide ou expirée. Régénérez-la.

---

## Tests

### Test 1 : Géocoding

```php
<?php
require_once 'services/EnvironmentalWeatherService.php';

$result = EnvironmentalWeatherService::geocodeAddress("Eiffel Tower, Paris");
var_dump($result);
// Doit afficher latitude, longitude, display_name
?>
```

**Exécution** :
```bash
php -r "require 'services/EnvironmentalWeatherService.php'; 
var_dump(EnvironmentalWeatherService::geocodeAddress('Paris'));"
```

### Test 2 : Analyse IA

```php
<?php
require_once 'services/EnvironmentalWeatherService.php';

$tag = EnvironmentalWeatherService::analyzeContentWithAI(
    "L'eau monte, c'est une inondation terrible!"
);

echo $tag;  // "Inondation"
?>
```

### Test 3 : Workflow complet

```php
<?php
require_once 'services/EnvironmentalWeatherService.php';

$result = EnvironmentalWeatherService::processEnvironmentalData(
    "Barcelona, Spain",
    "Aujourd'hui, un orage avec beaucoup de tonnerre était visible.",
    "Tempête à Barcelone"
);

echo "Status: " . $result['status'] . "\n";
echo "Tag: " . $result['ai_tag'] . "\n";
echo "Weather: " . $result['weather_main'] . "\n";
?>
```

### Test 4 : Via le formulaire

1. Ouvrez `/docs/FORM_INTEGRATION_EXAMPLE.php`
2. Remplissez le formulaire avec :
   - Titre : "Inondation à Paris"
   - Contenu : "L'eau monte dangereusement..."
   - Adresse : "Paris, France"
3. Soumettez

**Résultat attendu** :
- ✅ Post créé
- 📍 Coordonnées géocodées
- 🌤️ Météo récupérée
- 🤖 Tag IA détecté
- 🚨 Status = "Alerte Climatique" (si pluie)

---

## 🔐 Sécurité

### Bonnes pratiques

✅ **Validations**
- Entrées utilisateur filtrées avec `htmlspecialchars()`
- Longueurs limitées (adresse max 255 char)
- PDO prepared statements

✅ **Gestion des erreurs**
- Try/catch pour tous les appels API
- Logs d'erreur dans `error_log()`
- Continuité du service même en cas d'erreur API

✅ **Rate limiting**
- Nominatim : 1 req/sec par défaut
- OpenWeatherMap : 1000/jour en plan gratuit
- Implémentez une mise en cache pour éviter les doublons

---

## 📊 Logs et monitoring

### Fichier de log

Les messages sont loggés dans le fichier error_log PHP :
```bash
tail -f /var/log/php-errors.log

# Ou pour Windows
type C:\xampp\php\logs\php_error.log
```

### Messages importants

```
EnvironmentalWeatherService::analyzeContentWithAI - Tag detected: Inondation
EnvironmentalWeatherService::determinePostStatus - Climate alert triggered
EnvironmentalWeatherService::processEnvironmentalData - Result: {...}
ForumController::createPost - Environmental data processed successfully
```

---

## 🚀 Optimisations futures

1. **Mise en cache** : Cache Redis des coordonnées et météo
2. **Machine Learning** : Remplacer la regex par un vrai modèle NLP
3. **Webhooks** : Notifications en temps réel pour les alertes climatiques
4. **API alternatives** : Support pour d'autres fournisseurs de météo
5. **Historique météo** : Stocker l'historique pour analyses

---

## 📚 Références

- **OpenWeatherMap API** : https://openweathermap.org/api
- **Nominatim OSM** : https://nominatim.org/
- **PHP cURL** : https://www.php.net/manual/en/book.curl.php
- **PDO** : https://www.php.net/manual/en/book.pdo.php

---

## 💬 Support

Pour des questions ou problèmes :
1. Vérifiez les logs dans `error_log`
2. Testez les APIs individuellement
3. Vérifiez votre clé API OpenWeatherMap
4. Vérifiez votre connexion Internet

---

**Dernière mise à jour** : Mai 2026  
**Version** : 1.0.0  
**Auteur** : Expert PHP Architecture MVC
