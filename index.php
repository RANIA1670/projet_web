<?php
session_start();

// Point d'entrée du front office
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/ReplyController.php';

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$postController = new PostController();
$replyController = new ReplyController();

switch ($action) {
    case 'index':
        $postController->index();
        break;

    case 'show':
        if ($id) {
            $postController->show($id);
        } else {
            header('Location: /index.php');
        }
        break;

    case 'create':
        $postController->create();
        break;

    case 'store':
        $postController->store();
        break;

    case 'reply_store':
        $replyController->store();
        break;

    default:
        // Page 404
        require_once __DIR__ . '/views/front/404.php';
        break;
}
?>