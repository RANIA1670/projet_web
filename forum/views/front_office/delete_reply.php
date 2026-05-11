<?php
/**
 * Vue Front-Office : Suppression d'une réponse
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

if (!isset($_GET['id'], $_GET['post_id']) || !is_numeric($_GET['id']) || !is_numeric($_GET['post_id'])) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

$replyId = (int)$_GET['id'];
$postId = (int)$_GET['post_id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Reply.php';

$controller = new ForumController();
$reply = Reply::findById($replyId);

if (!$reply) {
    header('Location: ' . forum_post_url($postId));
    exit;
}

if ($reply->getUserId() !== $currentUserId && !$isAdmin) {
    header('Location: ' . forum_post_url($postId));
    exit;
}

if ($controller->deleteReply($replyId)) {
    header('Location: ' . forum_post_url($postId));
    exit;
}

header('Location: ' . forum_post_url($postId));
exit;
