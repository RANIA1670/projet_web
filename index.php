<?php
/**
 * Point d'entrée principal de l'application Forum
 * Routeur simple pour les pages front-office
 */

session_start();

// Simulation session (à adapter en production)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = true;
}

// Déterminer la page demandée
$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

// Inclure les fichiers nécessaires
require_once __DIR__ . '/config/ForumRedirect.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ForumController.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Reply.php';
require_once __DIR__ . '/models/Like.php';

// Router simple
switch ($page) {
    case 'post':
        if (isset($_GET['id'])) {
            include __DIR__ . '/views/front_office/view_post.php';
        } else {
            include __DIR__ . '/views/front_office/list_posts.php';
        }
        break;

    case 'create':
        include __DIR__ . '/views/front_office/create_post.php';
        break;

    case 'edit':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=edit_post&id=' . (int)$_GET['id']);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'delete':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=delete_post&id=' . (int)$_GET['id']);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'edit_reply':
        if (isset($_GET['id'], $_GET['post_id']) && is_numeric($_GET['id']) && is_numeric($_GET['post_id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=edit_reply&id=' . (int)$_GET['id'] . '&post_id=' . (int)$_GET['post_id']);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'delete_reply':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=delete_reply&id=' . (int)$_GET['id']);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'home':
    default:
        include __DIR__ . '/views/front_office/list_posts.php';
        break;
}
?>
