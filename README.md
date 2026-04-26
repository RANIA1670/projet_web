# CityZen — Smart Intervention Management

> Plateforme intelligente de gestion des interventions et signalements urbains  
> **PHP MVC · PDO · MySQL · AJAX · Responsive Design**

---

## 📋 Prérequis

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+
- Apache avec `mod_rewrite` activé (XAMPP / Laragon)
- Navigateur moderne

---

## 🚀 Installation rapide

### 1. Cloner / Déposer le projet

Placez le dossier `fati/` dans votre répertoire web :
- **XAMPP** → `C:\xampp\htdocs\fati\`
- **Laragon** → `C:\laragon\www\fati\`

### 2. Créer la base de données

```sql
-- Ouvrez phpMyAdmin → SQL → Exécutez :
SOURCE /path/to/fati/database/cityzen.sql
```

Ou dans l'onglet **Import** de phpMyAdmin, importez le fichier `database/cityzen.sql`.

### 3. Configurer la connexion

Éditez `config/config.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cityzen_db');
define('DB_USER', 'root');      // votre utilisateur MySQL
define('DB_PASS', '');          // votre mot de passe MySQL
define('APP_URL', 'http://localhost/fati');  // URL de base
```

### 4. Vérifier le `.htaccess`

Dans `public/.htaccess`, vérifiez que `RewriteBase` correspond à votre chemin :

```apache
RewriteBase /fati/public/
```

### 5. Accéder à l'application

Ouvrez : **http://localhost/fati/public/**

---

## 🔑 Comptes de démonstration

| Rôle       | Email                    | Mot de passe |
|------------|--------------------------|--------------|
| Admin       | admin@cityzen.ma         | password     |
| Technicien  | tech@cityzen.ma          | password     |
| Citoyen     | jean.martin@email.com    | password     |

---

## 📁 Structure du projet

```
fati/
├── app/
│   ├── core/
│   │   ├── Controller.php      # Contrôleur de base
│   │   ├── Database.php        # Connexion PDO (Singleton)
│   │   ├── Model.php           # Modèle de base (ORM)
│   │   └── Router.php          # Routeur HTTP
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ContactController.php
│   │   ├── HomeController.php
│   │   ├── InterventionController.php
│   │   ├── SignalementController.php
│   │   └── SuiviController.php
│   ├── models/
│   │   ├── CategorieModel.php
│   │   ├── ContactModel.php
│   │   ├── DemandeInterventionModel.php
│   │   ├── InterventionModel.php
│   │   ├── SignalementModel.php
│   │   └── UserModel.php
│   ├── views/
│   │   ├── auth/           # Connexion & Inscription
│   │   ├── contact/        # Page contact
│   │   ├── errors/         # Pages d'erreur (404)
│   │   ├── home/           # Page d'accueil
│   │   ├── intervention/   # Interventions
│   │   ├── layouts/        # Layout principal (HTML)
│   │   ├── signalement/    # Signalements (CRUD)
│   │   └── suivi/          # Suivi des interventions
│   └── routes.php          # Définition des routes
├── config/
│   └── config.php          # Configuration globale
├── database/
│   └── cityzen.sql         # Script SQL complet
├── public/
│   ├── assets/
│   │   ├── css/main.css    # Design system premium
│   │   └── js/main.js      # JavaScript / AJAX
│   ├── uploads/            # Images uploadées
│   ├── .htaccess
│   └── index.php           # Point d'entrée unique
├── .htaccess
└── README.md
```

---

## 🎨 Charte graphique

| Couleur     | Code     | Usage               |
|-------------|----------|---------------------|
| Bleu foncé  | `#2C3E50`| Couleur principale  |
| Vert        | `#27AE60`| Couleur secondaire  |
| Orange      | `#E67E22`| Alertes             |
| Rouge       | `#E74C3C`| Danger/Urgent       |

- **Typographie** : Montserrat (titres), Inter (corps)
- **Design** : Glassmorphism, gradients, micro-animations

---

## 📄 Pages disponibles

| URL                          | Description                |
|------------------------------|----------------------------|
| `/`                          | Accueil avec stats live    |
| `/signalements`              | Liste des signalements     |
| `/signalement/creer`         | Formulaire de signalement  |
| `/signalement/{id}`          | Détail d'un signalement    |
| `/signalement/{id}/modifier` | Modification               |
| `/interventions`             | Liste des interventions    |
| `/intervention/demande`      | Demande d'intervention     |
| `/suivi`                     | Suivi par référence        |
| `/suivi/{id}`                | Détail du suivi            |
| `/contact`                   | Formulaire de contact      |
| `/auth/connexion`            | Connexion                  |
| `/auth/inscription`          | Inscription                |
| `/api/signalements`          | API JSON signalements      |
| `/api/stats`                 | API JSON statistiques      |

---

## 🛡️ Sécurité

- `htmlspecialchars()` sur toutes les sorties
- PDO avec requêtes préparées (anti SQL injection)
- `password_hash()` / `password_verify()` pour les mots de passe
- Validation côté serveur de tous les formulaires
- Upload sécurisé avec vérification d'extension et MIME

---

## ⚙️ Technologies

- **Backend** : PHP 8.0+ OOP strict
- **Pattern** : MVC (Model-View-Controller)
- **Base de données** : MySQL via PDO uniquement
- **Frontend** : HTML5, CSS3 Vanilla, JavaScript ES6+
- **Requêtes asynchrones** : AJAX natif (Fetch API)
- **Design** : Responsive, mobile-first

---

*© 2026 CityZen — Smart Intervention Management*
