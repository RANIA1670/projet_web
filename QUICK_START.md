# ⚡ QUICK START - Corrélation Environnementale (5 minutes)

## 🎯 Objectif
Intégrer le système de détection automatique environnementale/météorologique dans votre forum en **5 étapes simples**.

---

## ⏱️ Les 5 étapes

### 1️⃣ Obtenir une clé API OpenWeatherMap (2 min)

**Lien :** https://openweathermap.org/api

**Étapes :**
1. Cliquez sur "Sign Up"
2. Créez un compte gratuit
3. Allez à "My API Keys"
4. Copiez votre clé
5. ⏳ Attendez 5-20 minutes pour l'activation

**Résultat :** Une clé qui ressemble à `a1b2c3d4e5f6g7h8i9j0`

---

### 2️⃣ Configurer la clé dans le service (1 min)

**Fichier :** `/services/EnvironmentalWeatherService.php`

**Ligne 20 :**
```php
private const OPENWEATHER_API_KEY = 'YOUR_OPENWEATHER_API_KEY_HERE';
```

**Remplacez par :**
```php
private const OPENWEATHER_API_KEY = 'a1b2c3d4e5f6g7h8i9j0';  // Votre clé
```

**Enregistrez le fichier.**

---

### 3️⃣ Exécuter la migration SQL (1 min)

**Fichier :** `/migrations/add_environmental_fields.sql`

**Option A : Via phpMyAdmin**
1. Ouvrez http://localhost/phpmyadmin
2. Sélectionnez la base `furum`
3. Allez à l'onglet **SQL**
4. Copiez-collez le contenu du fichier
5. Cliquez **Exécuter**

**Option B : Via terminal**
```bash
cd "c:\xampp\htdocs\web mardi"
mysql -u root furum < migrations/add_environmental_fields.sql
```

**Résultat :** 7 nouvelles colonnes créées dans la table `posts`

---

### 4️⃣ Ajouter le champ adresse au formulaire (1 min)

**Fichier :** Votre formulaire de création de post
**Exemple :** `/views/front_office/create_post.php`

**Ajoutez ce HTML :**
```html
<div class="form-group">
    <label for="address">📍 Adresse (optionnel - pour la météo)</label>
    <input 
        type="text" 
        id="address" 
        name="address" 
        placeholder="Ex: Paris, France"
    >
    <small>Laissez vide pour ignorer l'analyse environnementale</small>
</div>
```

---

### 5️⃣ Passer le paramètre au contrôleur (0 min)

**Fichier :** Votre logique de traitement du formulaire

**Avant :**
```php
$controller->createPost($userId, $title, $content);
```

**Après :**
```php
$address = trim($_POST['address'] ?? '');
$controller->createPost($userId, $title, $content, null, null, $address);
```

**Voilà ! 🎉**

---

## ✅ Vérification - Est-ce que ça marche ?

### Test 1 : Ouvrir la page de test

```
http://localhost/web%20mardi/test_environmental_complete.php
```

**6 boutons de test disponibles.** Cliquez sur chacun pour voir si tout fonctionne.

### Test 2 : Créer un post

1. Ouvrez votre formulaire de création
2. Remplissez :
   - Titre : "Alerte inondation à Paris"
   - Contenu : "L'eau monte partout!"
   - Adresse : "Paris, France"
3. Cliquez "Créer"

**Si ça marche :**
- ✅ Post créé
- ✅ Latitude/Longitude apparaissent
- ✅ Météo détectée
- ✅ Tag = "Inondation"
- ✅ Status = "Alerte Climatique" (si pluie) ou "Actif"

### Test 3 : Vérifier en base de données

```sql
-- Voir le dernier post
SELECT id, title, latitude, longitude, weather_current, ai_tag, status 
FROM posts 
ORDER BY id DESC 
LIMIT 1;
```

**Résultat attendu :**
```
id | title              | latitude | longitude | weather_current | ai_tag     | status
1  | Alerte inondation  | 48.8566  | 2.3522    | Rain            | Inondation | Alerte Climatique
```

---

## 🤖 Qu'est-ce qui se passe automatiquement ?

Quand vous créez un post **avec une adresse**, le système :

1. **📍 Géocode l'adresse**
   - "Paris, France" → Latitude: 48.8566, Longitude: 2.3522

2. **🌤️ Récupère la météo actuelle**
   - Appel OpenWeatherMap → "Rain, 15°C, 78% humidity"

3. **🤖 Analyse le contenu**
   - Cherche des mots clés → "inondation" détecté → Tag: "Inondation"

4. **⚙️ Applique la logique métier**
   - IF (Tag="Inondation" AND Météo="Rain") → Status="Alerte Climatique"
   - ELSE → Status="Actif"

5. **💾 Enregistre tout en base**
   - Latitude, longitude, météo, tag, status dans la table posts

---

## 🎯 Cas d'usage

### ✅ Cas 1 : Inondation + Pluie = ALERTE

```
Titre: "Inondation à Paris"
Contenu: "L'eau monte!!!"
Adresse: "Paris, France"
Météo: "Rain"

Résultat:
✅ Tag IA: "Inondation"
✅ Status: "Alerte Climatique"
✅ Admin reçoit notification
```

### ✅ Cas 2 : Inondation + Beau temps = Normal

```
Titre: "Hier il y avait une inondation"
Contenu: "La tempête d'hier a causé une inondation."
Adresse: "Marseille"
Météo: "Clear"

Résultat:
✅ Tag IA: "Inondation"
✅ Status: "Actif" (pas de corrélation)
```

### ✅ Cas 3 : Sans adresse = Mode simple

```
Titre: "Mon premier post"
Contenu: "Contenu normal"
Adresse: [VIDE]

Résultat:
✅ Post créé normalement
✅ Status: "Actif" (par défaut)
✅ Pas d'appel API
```

---

## 🚨 Erreurs courantes

| Erreur | Cause | Solution |
|--------|-------|----------|
| ❌ "API key not configured" | Clé manquante | Configurez votre clé ligne 20 |
| ❌ "No results from Nominatim" | Adresse invalide | Vérifiez l'adresse (ex: "Paris, France") |
| ❌ Page de test vide | Fichier manquant | Assurez-vous que test_environmental_complete.php existe |
| ❌ Migration échoue | Syntaxe SQL | Vérifiez que la table posts existe |

---

## 📊 Données stockées

**Colonnes ajoutées à `posts` :**

```
latitude          : 48.8566        (Coordonnée GPS)
longitude         : 2.3522         (Coordonnée GPS)
weather_current   : "Rain"         (Condition météo)
ai_tag            : "Inondation"   (Tag détecté)
display_address   : "Paris, France" (Adresse formatée)
metadata_env      : {...}          (JSON avec tous les détails)
status            : "Alerte Climatique"  (Statut auto)
```

---

## 📚 Besoin d'aide ?

| Besoin | Fichier |
|--------|---------|
| Documentation complète | `/docs/ENVIRONMENTAL_WEATHER_GUIDE.md` |
| Exemple de formulaire | `/docs/FORM_INTEGRATION_EXAMPLE.php` |
| Résumé implémentation | `/IMPLEMENTATION_SUMMARY.md` |
| Tests interactifs | `/test_environmental_complete.php` |

---

## 🎓 Pour aller plus loin

**Après ces 5 minutes :**

1. Lisez `/docs/ENVIRONMENTAL_WEATHER_GUIDE.md` pour la logique complète
2. Explorez les méthodes individuelles du service
3. Implémentez la mise en cache avec Redis
4. Ajoutez des webhooks pour les alertes en temps réel

---

## ✨ Résumé

| Étape | Durée | Fichier | Action |
|-------|-------|---------|--------|
| 1 | 2 min | openweathermap.org | Générer clé API |
| 2 | 1 min | `EnvironmentalWeatherService.php` | Configurer clé |
| 3 | 1 min | `add_environmental_fields.sql` | Migration BD |
| 4 | 1 min | Votre formulaire | Ajouter input address |
| 5 | 0 min | Votre contrôleur | Passer $address |

**Total : ~5 minutes** ⏱️

---

**Bon développement ! 🚀**

*Pour les questions, consultez ENVIRONMENTAL_WEATHER_GUIDE.md*
