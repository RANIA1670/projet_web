<?php
/**
 * Vue Front-Office : Suppression d'une réponse
 */

// Vérification que l'utilisateur est connecté
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($currentUserId === 0) {
    header('Location: login.php');
    exit;
}

// Vérifier si les IDs sont fournis
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    header('Location: list_posts.php');
    exit;
}

$replyId = (int)$_GET['id'];
$postId = (int)$_GET['post_id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Reply.php';

$controller = new ForumController();
$reply = Reply::findById($replyId);

// Vérifier que la réponse existe
if (!$reply) {
    header('Location: view_post.php?id=' . $postId);
    exit;
}

// Vérifier que l'utilisateur est l'auteur de la réponse
if ($reply->getUserId() !== $currentUserId) {
    header('Location: view_post.php?id=' . $postId);
    exit;
}

// Supprimer la réponse
if ($controller->deleteReply($replyId)) {
    header('Location: view_post.php?id=' . $postId);
    exit;
} else {
    header('Location: view_post.php?id=' . $postId);
    exit;
}
