# 🎯 CityZen - Fonctionnalités Avancées

## 📍 1. Système de Localisation Géographique

### Fonctionnalités
- **Géolocalisation automatique** : Les utilisateurs peuvent cliquer sur le bouton pour obtenir automatiquement leurs coordonnées
- **Auto-complétion d'adresses** : Suggestions en temps réel en tapant une adresse
- **Géocodage inversé** : Conversion adresse ↔️ coordonnées via API OpenStreetMap (Nominatim)
- **Mini-carte interactive** : Prévisualisation de la localisation sur une carte Leaflet
- **Carte globale** : Visualisation de tous les signalements sur une carte interactive avec clustering

### Fichiers Modifiés/Créés
```
app/controllers/MapController.php          ✨ Nouveau
app/views/map/index.php                    ✨ Nouveau
app/views/signalement/create.php           ✏️ Mis à jour avec géolocalisation
database/migrations.sql                    ✏️ Colonnes latitude/longitude
app/routes.php                             ✏️ Routes carte + API géocodage
```

### Utilisation
1. **Formulaire de signalement** : Cliquez sur "Me localiser automatiquement"
2. **Auto-complétion** : Tapez une adresse → Les suggestions apparaissent
3. **Carte globale** : Accédez à `/carte` pour voir tous les signalements sur une carte

### API Endpoints
```
GET /carte                          - Page interactive de la carte
GET /api/signalements/carte         - Récupérer les signalements (GeoJSON)
GET /api/zones/stats                - Statistiques par zone géographique
GET /api/geocode?address=...        - Géocodage d'une adresse
```

---

## 🔔 2. Système d'Alertes et Notifications en Temps Réel

### Fonctionnalités
- **Notifications non-lues** : Widget dans la navbar affichant les notifications non lues
- **Types de notifications** : Nouveau signalement, changement de statut, intervention assignée
- **Historique** : Page complète pour consulter tout l'historique des notifications
- **Marquer comme lu** : Interface pour marquer les notifications individuellement ou en masse
- **Escalade automatique** : Base pour un système d'escalade (à compléter)

### Fichiers Modifiés/Créés
```
app/services/NotificationService.php        ✨ Nouveau
app/controllers/NotificationController.php  ✨ Nouveau
app/views/notification/index.php            ✨ Nouveau
app/views/layouts/default.php               ✏️ Widget notifications dans navbar
database/migrations.sql                     ✏️ Table notifications + escalations
app/routes.php                              ✏️ Routes notifications
```

### Utilisation
1. **Widget navbar** : Icône 🔔 avec badge de notifications non lues
2. **Page notifications** : Accédez à `/notifications` pour voir l'historique complet
3. **Marquer comme lu** : Cliquez sur une notification ou "Marquer tout comme lu"

### API Endpoints
```
GET /api/notifications/unread               - Récupérer les non-lues (JSON)
GET /api/notifications/widget                - Widget simplifié pour navbar
POST /api/notifications/mark-read            - Marquer une notification comme lue
POST /api/notifications/mark-all             - Marquer toutes comme lues
GET /notifications                           - Page d'historique des notifications
```

### Intégration dans le Projet
Les notifications peuvent être créées depuis les contrôleurs :
```php
$notificationService = new NotificationService();

// Notifier les techniciens d'un nouveau signalement
$notificationService->notifyTechnicians($signalementId, 'urgente');

// Notifier le citoyen d'un changement de statut
$notificationService->notifyStatusChange($signalementId, 'nouveau', 'en_cours');

// Créer une notification personnalisée
$notificationService->create(
    $userId,
    'type_notification',
    'Titre',
    'Message',
    $signalementId
);
```

---

## 🔧 Installation et Configuration

### 1. Exécuter les migrations SQL
```sql
-- Exécuter le contenu de database/migrations.sql dans votre base de données
-- Cela va créer/modifier les tables necessaires
```

### 2. Points d'intégration dans le code existant

#### SignalementController (créer)
```php
// Après avoir créé un signalement
$notificationService = new NotificationService();
$notificationService->notifyTechnicians($signalementId, $data['priorite']);
```

#### SignalementController (mettre à jour le statut)
```php
// Après modification du statut
$notificationService->notifyStatusChange($signalementId, $ancienStatut, $nouveauStatut);
```

---

## 📱 Technologie

### Frontend
- **Leaflet.js** : Cartes interactives
- **OpenStreetMap** : Cartes de base
- **Nominatim API** : Géocodage (gratuit, pas d'authentification)
- **Leaflet MarkerCluster** : Clustering des marqueurs

### Backend
- **PHP** : Services et contrôleurs
- **MySQL** : Stockage des notifications
- **AJAX/Fetch** : Communication client-serveur

---

## 🚀 Améliorations Futures

### Pour la Cartographie
- [ ] Heatmaps des zones critiques
- [ ] Filtrage avancé par catégorie/date
- [ ] Export des données cartographiques
- [ ] Itinéraires optimisés pour les techniciens

### Pour les Notifications
- [ ] WebSockets pour temps réel vrai (Socket.IO)
- [ ] Notifications push (Service Workers)
- [ ] Notifications SMS/Email
- [ ] Système d'escalade complet avec règles
- [ ] Modèles de notifications personnalisables

---

## 📊 Statistiques et Performance

- API géocodage : ~200-500ms par requête
- Chargement de la carte : ~1-2s pour 500 signalements
- Widget notifications : Actualisation toutes les 30s

---

## ✅ Checklist d'Intégration

- [x] Tables de base de données créées
- [x] Services et contrôleurs implémentés
- [x] Routes configurées
- [x] Vues créées
- [x] Widget notifications dans navbar
- [ ] Intégration dans SignalementController (à faire)
- [ ] Tests unitaires (à faire)
- [ ] Documentation utilisateur (à faire)

