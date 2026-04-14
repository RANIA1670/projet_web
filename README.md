<?php
/**
 * Installation et configuration du projet CityZen Forum
 *
 * Instructions d'installation :
 *
 * 1. Créer la base de données :
 *    - Ouvrir phpMyAdmin
 *    - Créer une base de données nommée 'cityzen_forum'
 *    - Importer le fichier database.sql
 *
 * 2. Configuration de la base de données :
 *    - Modifier config/Database.php si nécessaire
 *    - Par défaut : localhost, root, '', cityzen_forum
 *
 * 3. Permissions :
 *    - S'assurer que les dossiers suivants sont accessibles en écriture :
 *      - /public/uploads/ (si vous ajoutez des uploads)
 *
 * 4. URLs :
 *    - Front office : http://localhost/cityzen/index.php
 *    - Administration : http://localhost/cityzen/admin.php
 *    - Identifiants admin par défaut : admin / password
 *
 * 5. Structure MVC :
 *    - Modèles : /models/ (logique métier et accès BDD)
 *    - Vues : /views/ (templates d'affichage)
 *    - Contrôleurs : /controllers/ (gestion des requêtes)
 *    - Configuration : /config/ (paramètres globaux)
 *    - Assets : /public/ (CSS, JS, images)
 *
 * Fonctionnalités implémentées :
 *    ✅ CRUD complet pour les posts et replies
 *    ✅ Séparation front office / back office
 *    ✅ Authentification administrateur
 *    ✅ Validation et modération des contenus
 *    ✅ Interface responsive
 *    ✅ Statistiques en temps réel
 *    ✅ Gestion des statuts (Publié, En révision, etc.)
 *
 * Technologies utilisées :
 *    - PHP 7.4+ avec PDO
 *    - MySQL/MariaDB
 *    - HTML5, CSS3, JavaScript vanilla
 *    - Architecture MVC
 *
 * Sécurité :
 *    - Échappement des données (htmlspecialchars)
 *    - Préparation des requêtes SQL (PDO)
 *    - Validation des formulaires côté serveur
 *    - Sessions pour l'authentification
 */