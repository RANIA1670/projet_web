<?php

declare(strict_types=1);

/**
 * Back-office forum — routeur aligné sur la branche amine (admin.php).
 */

require_once __DIR__ . '/../core/Bootstrap.php';

App\Core\Bootstrap::init();

cityzen_require_agent();

cityzen_session_start();
$_SESSION['user_id'] = cityzen_current_user_id();
$_SESSION['is_admin'] = true;

$forumBase = __DIR__ . '/../forum';
$page = isset($_GET['page']) ? trim((string) $_GET['page']) : 'dashboard';

require_once $forumBase . '/config/ForumRedirect.php';
require_once $forumBase . '/config/Database.php';
require_once $forumBase . '/controllers/ForumController.php';
require_once $forumBase . '/models/Post.php';
require_once $forumBase . '/models/Reply.php';
require_once $forumBase . '/models/Like.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['admin_delete_post'])) {
    if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
        (new ForumController())->deletePost((int) $_POST['post_id']);
    }
    header('Location: ' . forum_admin_nav_base() . '?page=dashboard', true, 302);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['admin_delete_reply'])) {
    if (isset($_POST['reply_id']) && is_numeric($_POST['reply_id'])) {
        (new ForumController())->deleteReply((int) $_POST['reply_id']);
    }
    header('Location: ' . forum_admin_nav_base() . '?page=dashboard', true, 302);
    exit;
}

$views = $forumBase . '/views/back_office';

switch ($page) {
    case 'dashboard':
        include $views . '/dashboard.php';
        break;

    case 'edit_post':
        if (isset($_GET['id'])) {
            include $views . '/edit_post.php';
        } else {
            include $views . '/dashboard.php';
        }
        break;

    case 'edit_reply':
        if (isset($_GET['id'])) {
            include $views . '/edit_reply.php';
        } else {
            include $views . '/dashboard.php';
        }
        break;

    case 'statistics':
        include $views . '/statistics.php';
        break;

    case 'delete_post':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $forumAdmin = new ForumController();
            $forumAdmin->deletePost((int) $_GET['id']);
        }
        header('Location: ' . forum_admin_nav_base(), true, 302);
        exit;

    case 'delete_reply':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $forumAdmin = new ForumController();
            $forumAdmin->deleteReply((int) $_GET['id']);
        }
        header('Location: ' . forum_admin_nav_base(), true, 302);
        exit;

    default:
        include $views . '/dashboard.php';
        break;
}
