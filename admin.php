<?php
session_start();

// Point d'entrée de l'administration
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/ReplyController.php';

$action = $_GET['action'] ?? 'dashboard';

// Vérifier l'authentification pour toutes les pages sauf login
if ($action !== 'login') {
    AdminController::checkAuth();
}

$adminController = new AdminController();
$postController = new PostController();
$replyController = new ReplyController();

switch ($action) {
    case 'login':
        $adminController->login();
        break;

    case 'logout':
        $adminController->logout();
        break;

    case 'dashboard':
        $adminController->dashboard();
        break;

    // Posts
    case 'posts':
        $postController->adminIndex();
        break;

    case 'post_create':
        $postController->adminCreate();
        break;

    case 'post_store':
        $postController->adminStore();
        break;

    case 'post_edit':
        if (isset($_GET['id'])) {
            $postController->adminEdit($_GET['id']);
        } else {
            header('Location: /admin.php?action=posts');
        }
        break;

    case 'post_update':
        if (isset($_GET['id'])) {
            $postController->adminUpdate($_GET['id']);
        } else {
            header('Location: /admin.php?action=posts');
        }
        break;

    case 'post_delete':
        if (isset($_GET['id'])) {
            $postController->adminDelete($_GET['id']);
        } else {
            header('Location: /admin.php?action=posts');
        }
        break;

    // Replies
    case 'replies':
        $replyController->adminIndex();
        break;

    case 'reply_create':
        $replyController->adminCreate();
        break;

    case 'reply_store':
        $replyController->adminStore();
        break;

    case 'reply_edit':
        if (isset($_GET['id'])) {
            $replyController->adminEdit($_GET['id']);
        } else {
            header('Location: /admin.php?action=replies');
        }
        break;

    case 'reply_update':
        if (isset($_GET['id'])) {
            $replyController->adminUpdate($_GET['id']);
        } else {
            header('Location: /admin.php?action=replies');
        }
        break;

    case 'reply_delete':
        if (isset($_GET['id'])) {
            $replyController->adminDelete($_GET['id']);
        } else {
            header('Location: /admin.php?action=replies');
        }
        break;

    default:
        header('Location: /admin.php');
        break;
}
?>