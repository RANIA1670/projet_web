<?php
/**
 * Ancienne vue post dans app.php — redirige vers la page publique complète (likes, vues, épinglage).
 */
require_once __DIR__ . '/../../../config/ForumRedirect.php';

if (!isset($postId) || $postId <= 0) {
    header('Location: ' . forum_front_url('page=home'));
    exit;
}

header('Location: ' . forum_post_url($postId));
exit;
