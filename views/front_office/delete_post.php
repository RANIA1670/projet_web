<?php
/**
 * Vue Front-Office : Suppression d'un post
 */

// Vérification que l'utilisateur est connecté
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($currentUserId === 0) {
    header('Location: login.php');
    exit;
}

// Vérifier si un ID de post a été fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list_posts.php');
    exit;
}

$postId = (int)$_GET['id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Post.php';

$controller = new ForumController();
$post = Post::findById($postId);

// Vérifier que le post existe
if (!$post) {
    header('Location: list_posts.php');
    exit;
}

// Vérifier que l'utilisateur est l'auteur du post
if ($post->getUserId() !== $currentUserId) {
    header('Location: view_post.php?id=' . $postId);
    exit;
}

// Supprimer le post
if ($controller->deletePost($postId)) {
    header('Location: list_posts.php');
    exit;
} else {
    header('Location: view_post.php?id=' . $postId);
    exit;
}
