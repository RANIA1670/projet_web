<?php
/**
 * Vue Front-Office : Suppression d'un post
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/ForumRedirect.php';

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = !empty($_SESSION['is_admin']);

if ($currentUserId === 0) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

$postId = (int)$_GET['id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Post.php';

$controller = new ForumController();
$post = Post::findById($postId);

if (!$post) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

if ($post->getUserId() !== $currentUserId && !$isAdmin) {
    header('Location: ' . forum_post_url($postId));
    exit;
}

if ($controller->deletePost($postId)) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

header('Location: ' . forum_post_url($postId));
exit;
