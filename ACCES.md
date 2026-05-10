# 🚀 Guide d'Accès - Forum CityZen

## ✅ URLs pour Tester

### 1. **Page d'Accueil**
```
http://localhost/web-mardi/
```
Page d'accueil avec liens vers le frontend et backend.

### 2. **Frontend (Interface Publique)**
```
http://localhost/web-mardi/front.php
```
- Consulter les discussions publiques
- Créer des messages
- Voir les réponses
- Liker les publications

### 3. **Backend/Admin (Interface d'Administration)**
```
http://localhost/web-mardi/admin.php
```
- Dashboard d'administration
- Modérer les posts et réponses
- Voir les statistiques
- Éditer/Supprimer du contenu

---

## 📁 Structure du Projet

```
web-mardi/
├── index.html          ← Page d'accueil (POINT DE DÉPART)
├── front.php           ← Interface Frontend
├── admin.php           ← Interface Backend/Admin
├── index.php           ← Ancien (remplacé par front.php)
├── config/
│   └── Database.php
├── controllers/
├── models/
├── views/
│   ├── front_office/   ← Pages du public
│   └── back_office/    ← Pages d'admin
└── ...
```

---

## 🔧 Routes Frontend

| URL | Description |
|-----|-------------|
| `front.php` | Liste des discussions |
| `front.php?page=post&id=1` | Voir une discussion |
| `front.php?page=create` | Créer une discussion |
| `front.php?page=edit&id=1` | Éditer une discussion |
| `front.php?page=delete&id=1` | Supprimer une discussion |

## 🔧 Routes Backend

| URL | Description |
|-----|-------------|
| `admin.php` | Dashboard admin |
| `admin.php?page=dashboard` | Dashboard |
| `admin.php?page=edit_post&id=1` | Éditer un post |
| `admin.php?page=edit_reply&id=1` | Éditer une réponse |
| `admin.php?page=statistics` | Voir les statistiques |

---

## ⚡ Démarrage Rapide

1. **Ouvrir XAMPP** et démarrer Apache + MySQL
2. **Créer la base de données** (voir README.md)
3. **Accéder à**: http://localhost/web-mardi/
4. **Choisir Frontend ou Backend**

---

## 🔐 Notes de Sécurité

⚠️ **IMPORTANT**: En production, ajouter:
- ✅ Système d'authentification réel
- ✅ Vérifications de permissions
- ✅ Protection CSRF
- ✅ Validation d'entrée stricte

---

**Bon test! 🎉**
