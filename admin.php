<?php
/**
 * Point d'entrée BACK-OFFICE - Admin Dashboard
 * Routeur pour l'interface d'administration
 * Accès: http://localhost/web-mardi/admin.php
 */

session_start();

// Vérification authentification admin
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Pour les tests - RETIRER EN PRODUCTION
if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = true;
    $_SESSION['user_id'] = 1;
}

// Déterminer la page demandée
$page = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';

// Inclure les fichiers nécessaires
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/ForumRedirect.php';
require_once __DIR__ . '/controllers/ForumController.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Reply.php';
require_once __DIR__ . '/models/Like.php';

// Suppression en POST : reste dans l’admin (pas d’ouverture d’URL « vide » en GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['admin_delete_post'])) {
    if ($isAdmin && isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
        (new ForumController())->deletePost((int)$_POST['post_id']);
    }
    header('Location: ' . forum_admin_nav_base() . '?page=dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['admin_delete_reply'])) {
    if ($isAdmin && isset($_POST['reply_id']) && is_numeric($_POST['reply_id'])) {
        (new ForumController())->deleteReply((int)$_POST['reply_id']);
    }
    header('Location: ' . forum_admin_nav_base() . '?page=dashboard');
    exit;
}

// Router pour le back-office
switch ($page) {
    case 'dashboard':
        include __DIR__ . '/views/back_office/dashboard.php';
        break;

    case 'edit_post':
        if (isset($_GET['id'])) {
            include __DIR__ . '/views/back_office/edit_post.php';
        } else {
            include __DIR__ . '/views/back_office/dashboard.php';
        }
        break;

    case 'edit_reply':
        if (isset($_GET['id'])) {
            include __DIR__ . '/views/back_office/edit_reply.php';
        } else {
            include __DIR__ . '/views/back_office/dashboard.php';
        }
        break;

    case 'statistics':
        include __DIR__ . '/views/back_office/statistics.php';
        break;

    case 'delete_post':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $delId = (int)$_GET['id'];
            $forumAdmin = new ForumController();
            $forumAdmin->deletePost($delId);
        }
        header('Location: ' . forum_admin_nav_base());
        exit;

    case 'delete_reply':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $forumAdmin = new ForumController();
            $forumAdmin->deleteReply((int)$_GET['id']);
        }
        header('Location: ' . forum_admin_nav_base());
        exit;

    default:
        include __DIR__ . '/views/back_office/dashboard.php';
        break;
}
?>
