# Forum CityZen - Guide de démarrage

## Configuration requise
- PHP 7.4+
- MySQL 5.7+
- Apache avec mod_rewrite activé
- XAMPP/WAMP/LAMP ou serveur PHP équivalent

## Installation & Configuration

### 1. **Placer le projet dans le répertoire web**
```
- XAMPP: c:\xampp\htdocs\web mardi\
- WAMP: c:\wamp64\www\web mardi\
```

### 2. **Créer la base de données**

#### Méthode 1: Via phpMyAdmin (recommandé)
1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Aller dans l'onglet **SQL**
3. Copier le contenu du fichier `init_database.sql`
4. Exécuter le script

#### Méthode 2: Via phpMyAdmin (Import)
1. Ouvrir phpMyAdmin
2. Sélectionner la base de données **furum** (ou exécuter `init_database.sql` / `config/align_furum_schema.sql`)
3. Cliquer sur **Importer**
4. Sélectionner le fichier `init_database.sql`
5. Cliquer sur **Exécuter**

### 3. **Vérifier la connexion à la base de données**
- Ouvrir `c:\Users\msi\Desktop\web mardi\config\Database.php`
- Vérifier les paramètres:
  - `DB_HOST`: localhost ✓
  - `DB_NAME`: furum ✓
  - `DB_USER`: root ✓
  - `DB_PASSWORD`: (vide) ✓

## Accéder à l'application

### Depuis XAMPP/WAMP
```
http://localhost/web%20mardi/
```

### Depuis le serveur PHP intégré
```bash
# Dans le répertoire du projet:
php -S localhost:8000
```

Puis accéder à: `http://localhost:8000`

## Utilisation

### Page d'accueil
- Affiche la liste de tous les posts
- Recherche et filtrage par date
- Triage par titre, date de création, ou nombre de vues

### Créer un post
- Cliquer sur "Créer une discussion"
- Remplir le titre et le contenu
- Cliquer sur "Publier"

### Consulter un post
- Cliquer sur le titre du post
- Voir les détails et les réponses
- Ajouter une réponse
- Liker le post ou les réponses

### Éditer un post/réponse
- Cliquer sur le bouton "Éditer" (pour vos propres messages)
- Modifier le contenu
- Cliquer sur "Mettre à jour"

### Supprimer un post/réponse
- Cliquer sur le bouton "Supprimer" (pour vos propres messages)
- Confirmer la suppression

## Structure du projet

```
web mardi/
├── config/
│   └── Database.php         # Configuration PDO
├── controllers/
│   ├── ForumController.php  # Logique du forum
│   └── FormValidator.php    # Validation des formulaires
├── models/
│   ├── Post.php             # Modèle Post
│   ├── Reply.php            # Modèle Reply
│   └── Like.php             # Modèle Like
├── views/
│   ├── front_office/        # Vues publiques
│   └── back_office/         # Vues administrateur
├── index.php                # Point d'entrée
├── .htaccess                # Réécriture d'URLs
└── init_database.sql        # Script d'initialisation BD
```

## Utilisateur de test

**Connexion automatique** avec l'ID utilisateur: `1`

Pour créer d'autres utilisateurs, ajouter des entrées dans la table `users`:

```sql
INSERT INTO users (username, email, password) VALUES 
('user2', 'user2@test.com', MD5('password123'));
```

## Troubleshooting

### ❌ "Erreur de connexion à la base de données"
- Vérifier que MySQL est démarré
- Vérifier les paramètres dans `config/Database.php`
- Vérifier que la base **furum** existe et que la table `posts` contient les colonnes `title`, `content`, etc. (`check.php`, script `config/align_furum_schema.sql`)

### ❌ "Aucun post affiché"
- Vérifier que la table `posts` existe
- Vérifier que `init_database.sql` a été exécuté
- Vérifier les logs d'erreur Apache

### ❌ "Erreur 404 sur les pages"
- Vérifier que mod_rewrite est activé dans Apache
- Vérifier que le fichier `.htaccess` existe

### ❌ "Les formulaires ne se soumettent pas"
- Vérifier les permissions du serveur
- Vérifier les logs d'erreur PHP

## Support

Pour plus d'informations, consultez les fichiers de contrôleur et de modèle qui contiennent une documentation détaillée.

---

**Dernière mise à jour**: Mai 2026
