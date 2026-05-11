<?php

declare(strict_types=1);

/**
 * Forum public — routeur aligné sur la branche amine (RANIA1670/projet_web).
 */

require_once __DIR__ . '/../core/Bootstrap.php';

App\Core\Bootstrap::init();

cityzen_session_start();
$_SESSION['user_id'] = cityzen_current_user_id() > 0 ? cityzen_current_user_id() : 0;
$_SESSION['is_admin'] = cityzen_is_agent();

$page = isset($_GET['page']) ? trim((string) $_GET['page']) : 'home';

require_once __DIR__ . '/config/ForumRedirect.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ForumController.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Reply.php';
require_once __DIR__ . '/models/Like.php';

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
            header('Location: ' . forum_admin_nav_base() . '?page=edit_post&id=' . (int) $_GET['id'], true, 302);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'delete':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=delete_post&id=' . (int) $_GET['id'], true, 302);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'edit_reply':
        if (isset($_GET['id'], $_GET['post_id']) && is_numeric($_GET['id']) && is_numeric($_GET['post_id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=edit_reply&id=' . (int) $_GET['id'] . '&post_id=' . (int) $_GET['post_id'], true, 302);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'delete_reply':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            header('Location: ' . forum_admin_nav_base() . '?page=delete_reply&id=' . (int) $_GET['id'], true, 302);
            exit;
        }
        include __DIR__ . '/views/front_office/list_posts.php';
        break;

    case 'home':
    default:
        include __DIR__ . '/views/front_office/list_posts.php';
        break;
}
