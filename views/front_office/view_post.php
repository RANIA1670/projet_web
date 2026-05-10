<?php
/**
 * Vue Front-Office : Affichage détaillé d'un post avec ses réponses
 * Likes : AJAX (pas de rechargement = pas d’incrément parasite des vues). Sans JS : POST + session pour ignorer 1 vue.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    require_once __DIR__ . '/../../config/ForumRedirect.php';
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

$postId = (int)$_GET['id'];

require_once __DIR__ . '/../../config/ForumRedirect.php';
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Reply.php';
require_once __DIR__ . '/../../models/Favorite.php';
require_once __DIR__ . '/../../models/Poll.php';
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../models/EmailSubscription.php';
require_once __DIR__ . '/../../models/Report.php';
$controller = new ForumController();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strcasecmp(trim((string)$_SERVER['HTTP_X_REQUESTED_WITH']), 'XMLHttpRequest') === 0;

if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if ($currentUserId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'auth']);
        exit;
    }
    if (isset($_POST['toggle_post_like'])) {
        $postObj = Post::findById($postId);
        $controller->toggleLike($currentUserId, $postId, 0);
        if ($postObj && $postObj->getUserId() > 0 && $postObj->getUserId() !== $currentUserId) {
            Notification::create(
                $postObj->getUserId(),
                'post_like',
                'Quelqu\'un a aimé votre publication.',
                $currentUserId,
                $postId,
                0
            );
        }
        echo json_encode([
            'ok'    => true,
            'likes' => $controller->countPostLikes($postId),
            'liked' => $controller->hasUserLikedPost($postId, $currentUserId),
        ]);
        exit;
    }
    if (isset($_POST['toggle_favorite'])) {
        Favorite::toggle($currentUserId, $postId);
        echo json_encode([
            'ok' => true,
            'favorited' => Favorite::exists($currentUserId, $postId),
            'favorites' => Favorite::countByPostId($postId),
        ]);
        exit;
    }
    if (isset($_POST['report_post'])) {
        $reason = trim((string)($_POST['reason'] ?? 'Contenu inapproprié'));
        $ok = Report::create($postId, $currentUserId, $reason);
        echo json_encode([
            'ok' => $ok,
            'message' => $ok ? 'Signalement envoyé au modérateur.' : 'Impossible d\'envoyer le signalement.',
        ]);
        exit;
    }
    if (isset($_POST['submit_poll_vote'], $_POST['poll_id'], $_POST['option_id'])) {
        $pollId = (int)$_POST['poll_id'];
        $optionId = (int)$_POST['option_id'];
        $poll = Poll::getByPostId($postId);
        if (!$poll || (int)$poll['id'] !== $pollId) {
            echo json_encode(['ok' => false, 'error' => 'poll_not_found']);
            exit;
        }
        if (!Poll::vote($pollId, $optionId, $currentUserId)) {
            echo json_encode(['ok' => false, 'error' => 'vote_failed']);
            exit;
        }
        $postObj = Post::findById($postId);
        if ($postObj && $postObj->getUserId() > 0 && $postObj->getUserId() !== $currentUserId) {
            Notification::create(
                $postObj->getUserId(),
                'poll_vote',
                'Votre sondage a reçu un nouveau vote.',
                $currentUserId,
                $postId,
                0
            );
        }
        $updatedPoll = Poll::getByPostId($postId);
        echo json_encode([
            'ok' => true,
            'poll' => $updatedPoll,
            'userVoteOptionId' => Poll::getUserVoteOptionId($pollId, $currentUserId),
        ]);
        exit;
    }
    if (isset($_POST['notifications_fetch'])) {
        echo json_encode([
            'ok' => true,
            'count' => Notification::countUnread($currentUserId),
            'items' => Notification::fetchLatest($currentUserId, 10),
        ]);
        exit;
    }
    if (isset($_POST['notifications_mark_read'])) {
        Notification::markAllRead($currentUserId);
        echo json_encode(['ok' => true]);
        exit;
    }
    if (isset($_POST['toggle_reply_like'], $_POST['reply_id']) && is_numeric($_POST['reply_id'])) {
        $rid = (int)$_POST['reply_id'];
        $replyObj = Reply::findById($rid);
        $controller->toggleLike($currentUserId, 0, $rid);
        if ($replyObj && $replyObj->getUserId() > 0 && $replyObj->getUserId() !== $currentUserId) {
            Notification::create(
                $replyObj->getUserId(),
                'reply_like',
                'Quelqu\'un a aimé votre réponse.',
                $currentUserId,
                $postId,
                $rid
            );
        }
        echo json_encode([
            'ok'      => true,
            'replyId' => $rid,
            'likes'   => $controller->countReplyLikes($rid),
            'liked'   => $controller->hasUserLikedReply($rid, $currentUserId),
        ]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_request']);
    exit;
}

if (!$isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_post_like']) && $currentUserId > 0) {
    $controller->toggleLike($currentUserId, $postId, 0);
    $_SESSION['forum_skip_view_increment_post_id'] = $postId;
    header('Location: ' . forum_post_url($postId));
    exit;
}

if (!$isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_reply_like'], $_POST['reply_id']) && $currentUserId > 0) {
    $rid = (int)$_POST['reply_id'];
    $controller->toggleLike($currentUserId, 0, $rid);
    $_SESSION['forum_skip_view_increment_post_id'] = $postId;
    header('Location: ' . forum_post_url($postId));
    exit;
}

$incrementViews = ($_SERVER['REQUEST_METHOD'] === 'GET');
if ($incrementViews
    && isset($_SESSION['forum_skip_view_increment_post_id'])
    && (int)$_SESSION['forum_skip_view_increment_post_id'] === $postId) {
    $incrementViews = false;
    unset($_SESSION['forum_skip_view_increment_post_id']);
}

$post = $controller->showPost($postId, $incrementViews);
if (!$post) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

$replies = $controller->listRepliesByPost($postId);
$postLikeCount = $controller->countPostLikes($postId);
$userHasLikedPost = $currentUserId > 0 ? $controller->hasUserLikedPost($postId, $currentUserId) : false;
$postFavoriteCount = Favorite::countByPostId($postId);
$userHasFavoritedPost = $currentUserId > 0 ? Favorite::exists($currentUserId, $postId) : false;
$poll = Poll::getByPostId($postId);
$userVoteOptionId = ($poll && $currentUserId > 0) ? Poll::getUserVoteOptionId((int)$poll['id'], $currentUserId) : 0;
$notificationUnreadCount = $currentUserId > 0 ? Notification::countUnread($currentUserId) : 0;
$latestNotifications = $currentUserId > 0 ? Notification::fetchLatest($currentUserId, 8) : [];
$postViewCount = (int)$post->getViewCount();
$likeRatePercent = $postViewCount > 0 ? min(100, round(($postLikeCount / $postViewCount) * 100)) : 0;

$likePostUrl = forum_post_url($postId);

// Traiter l'ajout d'une réponse
$submitError   = '';
$submitSuccess = '';
$replyOldText  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'])) {
    if ($currentUserId === 0) {
        $submitError = 'Vous devez être connecté pour répondre.';
    } else {
        $replyContent = trim((string)($_POST['reply_content'] ?? ''));
        $replyOldText = $replyContent;
        $v = (new FormValidator())
            ->required('reply_content',          $replyContent, 'La réponse')
            ->minLength('reply_content',         $replyContent, 5,    'La réponse')
            ->maxLength('reply_content',         $replyContent, 2000, 'La réponse')
            ->noScript('reply_content',          $replyContent, 'La réponse')
            ->noExcessiveRepeat('reply_content', $replyContent, 7,    'La réponse')
            ->hasMeaningfulContent('reply_content', $replyContent, 'La réponse');
        if ($v->fails()) {
            $submitError = $v->firstError();
        } elseif ($controller->createReply($postId, $currentUserId, $replyContent)) {
            $submitSuccess = 'Votre réponse a été publiée avec succès !';
            $replyOldText = '';
            $replies = $controller->listRepliesByPost($postId);
            $preview = function_exists('mb_substr')
                ? mb_substr($replyContent, 0, 140, 'UTF-8')
                : substr($replyContent, 0, 140);
            EmailSubscription::notifyNewReply($postId, $post->getTitle(), $preview);
            if ($post->getUserId() > 0 && $post->getUserId() !== $currentUserId) {
                Notification::create(
                    $post->getUserId(),
                    'new_reply',
                    'Vous avez reçu une nouvelle réponse sur votre publication.',
                    $currentUserId,
                    $postId,
                    0
                );
            }
        } else {
            $submitError = 'Une erreur est survenue lors de la publication de votre réponse.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post->getTitle()); ?> - Forum CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #2ECC71;
            --accent-dark: #27ae60;
            --navy: #34495E;
            --bg: #F4F6F8;
            --surface: #FFFFFF;
            --text: #2C3E50;
            --muted: #7F8C8D;
            --border: #E8ECF0;
            --like-on: #e63946;
            --like-off: #ff6b6b;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background-color: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .breadcrumb {
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .post-detail {
            background-color: var(--surface);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        .post-title {
            font-size: 2em;
            color: var(--navy);
            margin-bottom: 20px;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.95em;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .post-content {
            font-size: 1.05em;
            line-height: 1.8;
            color: var(--text);
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f8fafb;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }

        .post-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .like-hint {
            font-size: 0.78rem;
            color: var(--muted);
            width: 100%;
            margin: 0 0 8px 0;
        }

        .btn {
            padding: 10px 20px;
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95em;
            font-family: inherit;
            font-weight: 600;
            transition: background-color 0.2s, opacity 0.2s;
        }

        .btn:hover {
            background-color: var(--accent-dark);
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #666;
        }

        .btn-secondary:hover {
            background-color: #555;
        }

        .btn-like {
            background-color: var(--like-off);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-like:hover:not(:disabled) {
            filter: brightness(0.95);
        }

        .btn-like.liked {
            background-color: var(--like-on);
        }
        .btn-fav {
            background-color: #f1c40f;
            color: #fff;
        }
        .btn-fav.favorited {
            background-color: #d4ac0d;
        }

        .stats {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            color: #666;
            font-size: 0.95em;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .like-rate-widget {
            --pct: 0;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: conic-gradient(var(--accent) calc(var(--pct) * 1%), #e9edf2 0);
            display: grid;
            place-items: center;
            position: relative;
            margin-left: auto;
        }

        .like-rate-widget::before {
            content: '';
            position: absolute;
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid var(--border);
        }

        .like-rate-value {
            position: relative;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--navy);
        }

        .like-rate-label {
            position: relative;
            font-size: 0.62rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .message.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .replies-section {
            margin-top: 40px;
        }

        .replies-title {
            font-size: 1.5em;
            color: var(--navy);
            margin-bottom: 20px;
        }

        .reply-form {
            background-color: var(--surface);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 0.95em;
            resize: vertical;
            min-height: 120px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
        }

        .replies-list {
            display: grid;
            gap: 20px;
        }

        .reply-card {
            background-color: var(--surface);
            border-left: 4px solid var(--accent);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        .reply-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #666;
        }

        .reply-author {
            font-weight: bold;
            color: var(--navy);
        }

        .reply-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .reply-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
            padding-top: 15px;
            font-size: 0.9em;
        }

        .reply-action-btn {
            background: none;
            border: none;
            color: var(--accent-dark);
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            font-size: inherit;
        }

        .reply-action-btn:hover:not(:disabled) {
            text-decoration: underline;
        }

        .reply-action-btn:disabled {
            opacity: 0.55;
            cursor: wait;
        }

        .empty-replies {
            text-align: center;
            padding: 30px;
            color: #999;
        }
        .poll-box {
            background: #f8fafb;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            margin: 14px 0 18px;
        }
        .poll-question {
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 10px;
        }
        .poll-options {
            display: grid;
            gap: 8px;
        }
        .poll-option-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: .9rem;
            padding: 8px 10px;
            border: 1px solid #e6ebef;
            border-radius: 8px;
            background: #fff;
        }
        .poll-bar {
            height: 6px;
            border-radius: 99px;
            background: #e9edf2;
            overflow: hidden;
            margin-top: 4px;
        }
        .poll-bar > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #2ECC71, #7DDEAD);
            width: 0;
        }
        .poll-meta {
            color: var(--muted);
            font-size: .78rem;
            margin-top: 8px;
        }
        .notif-wrap {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 80;
        }
        .notif-toggle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: var(--navy);
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            position: relative;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .2);
        }
        .notif-count {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: #e74c3c;
            color: #fff;
            font-size: .68rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .notif-panel {
            display: none;
            width: 320px;
            max-height: 360px;
            overflow-y: auto;
            margin-bottom: 8px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(0, 0, 0, .15);
        }
        .notif-panel.open { display: block; }
        .notif-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            font-size: .82rem;
            color: var(--muted);
        }
        .notif-item {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f3f5;
            font-size: .85rem;
        }
        .notif-item.unread {
            background: #f5fbf7;
        }

        @media (max-width: 768px) {
            .post-title {
                font-size: 1.5em;
            }

            .post-meta {
                flex-direction: column;
                gap: 8px;
            }

            .post-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
            .notif-wrap {
                right: 10px;
                bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= htmlspecialchars(forum_list_url('page=home')) ?>">← Retour au forum</a>
        </div>

        <div class="post-detail">
            <h1 class="post-title"><?php echo htmlspecialchars($post->getTitle()); ?></h1>

            <div class="post-meta">
                <span>👤 Utilisateur #<?php echo $post->getUserId(); ?></span>
                <span>📅 <?php echo date('d/m/Y à H:i', strtotime($post->getCreatedAt())); ?></span>
                <?php if ($post->getCreatedAt() !== $post->getUpdatedAt()): ?>
                    <span>✏️ Modifié le <?php echo date('d/m/Y à H:i', strtotime($post->getUpdatedAt())); ?></span>
                <?php endif; ?>
            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post->getContent())); ?>
            </div>

            <?php if (forum_is_front_office()): ?>
            <div class="post-actions">
                <?php if ($currentUserId > 0): ?>
                    <form method="POST" action="<?= htmlspecialchars($likePostUrl) ?>" style="display:inline;">
                        <button type="submit" name="toggle_favorite" value="1" class="btn btn-fav<?= $userHasFavoritedPost ? ' favorited' : '' ?>"
                                id="postFavoriteBtn"
                                data-fav-url="<?= htmlspecialchars($likePostUrl) ?>">
                            ⭐ <span id="postFavoriteLabel"><?= $userHasFavoritedPost ? 'Retirer favori' : 'Ajouter favoris' ?></span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" id="reportPostBtn">🚩 Signaler</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($poll): ?>
                <div class="poll-box" id="pollBox" data-poll-id="<?= (int)$poll['id'] ?>">
                    <div class="poll-question">📊 <?= htmlspecialchars((string)$poll['question']) ?></div>
                    <div class="poll-options" id="pollOptions">
                        <?php foreach ($poll['options'] as $opt):
                            $votes = (int)$opt['vote_count'];
                            $pct = (int)$poll['total_votes'] > 0 ? round(($votes / (int)$poll['total_votes']) * 100) : 0;
                        ?>
                        <label class="poll-option-row">
                            <span>
                                <input type="radio" name="poll_option" value="<?= (int)$opt['id'] ?>" <?= $userVoteOptionId === (int)$opt['id'] ? 'checked' : '' ?>>
                                <?= htmlspecialchars((string)$opt['option_text']) ?>
                                <div class="poll-bar"><span style="width: <?= (int)$pct ?>%"></span></div>
                            </span>
                            <strong><?= $votes ?> (<?= (int)$pct ?>%)</strong>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($currentUserId > 0): ?>
                        <button type="button" class="btn" id="pollVoteBtn" style="margin-top:10px;">Voter / Changer vote</button>
                    <?php endif; ?>
                    <div class="poll-meta" id="pollMeta"><?= (int)$poll['total_votes'] ?> vote(s)</div>
                </div>
            <?php endif; ?>

            <div class="stats">
                <div class="stat">
                    <span>👁️</span>
                    <span id="statViewLine"><?php echo $postViewCount; ?> vue<?php echo $postViewCount > 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </div>

        <div class="replies-section">
            <h2 class="replies-title">Réponses (<?php echo count($replies); ?>)</h2>

            <?php if (!empty($submitSuccess)): ?>
                <div class="message success"><?php echo htmlspecialchars($submitSuccess); ?></div>
            <?php endif; ?>

            <?php if (!empty($submitError)): ?>
                <div class="message error"><?php echo htmlspecialchars($submitError); ?></div>
            <?php endif; ?>

            <div class="reply-form">
                <h3 style="margin-bottom: 15px; color: var(--navy);">Ajouter une réponse</h3>
                <?php if ($currentUserId === 0): ?>
                    <p style="color: #666; margin-bottom: 15px;">
                        <a href="login.php">Connectez-vous</a> pour participer à la discussion.
                    </p>
                <?php else: ?>
                    <form method="POST" id="replyPostForm" action="<?= htmlspecialchars(forum_post_url($postId)) ?>" novalidate>
                        <div class="form-group">
                            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px;">
                                <label for="reply_content" style="font-weight:600;">Votre réponse</label>
                                <span id="rCount" style="font-size:.78rem;color:#999;">0 / 2000</span>
                            </div>
                            <textarea id="reply_content" name="reply_content"
                                      placeholder="Tapez votre réponse… (5 à 2 000 caractères)"
                                      maxlength="2000"
                                      required><?= htmlspecialchars($replyOldText) ?></textarea>
                            <div style="height:3px;border-radius:99px;background:#eee;margin-top:6px;overflow:hidden;">
                                <div id="rBar" style="height:100%;border-radius:99px;width:0;transition:width .3s,background .3s;"></div>
                            </div>
                            <div id="rError" style="display:none;font-size:.78rem;color:#e63946;margin-top:5px;"></div>
                        </div>
                        <button type="submit" class="btn" id="replySubmit">📤 Publier votre réponse</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($replies)): ?>
                <div class="empty-replies">
                    <p>Aucune réponse pour le moment. Soyez le premier à répondre !</p>
                </div>
            <?php else: ?>
                <div class="replies-list">
                    <?php foreach ($replies as $reply):
                        $replyLikeCount = $controller->countReplyLikes($reply->getId());
                        $userHasLikedReply = $currentUserId > 0 ? $controller->hasUserLikedReply($reply->getId(), $currentUserId) : false;
                    ?>
                        <div class="reply-card">
                            <div class="reply-meta">
                                <div>
                                    <span class="reply-author">👤 Utilisateur #<?php echo $reply->getUserId(); ?></span>
                                    <span style="color: #999; margin-left: 20px;">
                                        📅 <?php echo date('d/m/Y à H:i', strtotime($reply->getCreatedAt())); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="reply-content">
                                <?php echo nl2br(htmlspecialchars($reply->getContent())); ?>
                            </div>

                            <div class="reply-actions">
                                <?php if (forum_is_front_office()): ?>
                                    <?php if ($currentUserId > 0): ?>
                                        <button type="button"
                                                class="reply-action-btn js-reply-like"
                                                data-like-url="<?= htmlspecialchars($likePostUrl) ?>"
                                                data-reply-id="<?= (int)$reply->getId() ?>"
                                                data-liked="<?= $userHasLikedReply ? '1' : '0' ?>">
                                            👍 <span class="js-reply-count"><?php echo (int)$replyLikeCount; ?></span>
                                            · <span class="js-reply-label"><?= $userHasLikedReply ? 'Retirer' : 'J\'aime' ?></span>
                                        </button>
                                    <?php else: ?>
                                        <span class="reply-action-btn" style="opacity:.6;">👍 <?php echo $replyLikeCount; ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($currentUserId > 0): ?>
    <div class="notif-wrap">
        <div class="notif-panel" id="notifPanel">
            <div class="notif-head">
                <span>Notifications</span>
                <button type="button" id="markNotifReadBtn" class="reply-action-btn">Tout lire</button>
            </div>
            <div id="notifItems">
                <?php foreach ($latestNotifications as $n): ?>
                    <div class="notif-item <?= ((int)$n['is_read'] === 0) ? 'unread' : '' ?>">
                        <?= htmlspecialchars((string)$n['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="button" class="notif-toggle" id="notifToggleBtn" title="Notifications">
            🔔
            <span class="notif-count" id="notifCount"><?= (int)$notificationUnreadCount ?></span>
        </button>
    </div>
    <?php endif; ?>

    <script>
        /* ── Validation réponse en temps réel (soumission normale POST) ── */
        (function () {
            var form    = document.getElementById('replyPostForm');
            var ta      = document.getElementById('reply_content');
            var btn     = document.getElementById('replySubmit');
            var countEl = document.getElementById('rCount');
            var barEl   = document.getElementById('rBar');
            var errEl   = document.getElementById('rError');
            if (!ta || !form) return;

            var checks = [
                function(v){ return v.trim().length === 0  ? 'La réponse est obligatoire.'    : null; },
                function(v){ return v.trim().length < 5    ? 'Minimum 5 caractères.'          : null; },
                function(v){ return v.trim().length > 2000 ? 'Maximum 2 000 caractères.'      : null; },
            ];

            function runValidation() {
                var val = ta.value, len = val.length;
                countEl.textContent = len + ' / 2000';
                countEl.style.color = len >= 2000 ? '#e63946' : len >= 1800 ? '#f7971e' : '#999';
                barEl.style.width   = Math.min(len / 2000 * 100, 100) + '%';
                barEl.style.background = len < 5 ? '#e63946' : len >= 2000 ? '#e63946' : len >= 1800 ? '#f7971e' : '#4CAF50';
                var err = null;
                for (var i = 0; i < checks.length; i++) { err = checks[i](val); if (err) break; }
                if (err) {
                    ta.style.borderColor = '#e63946';
                    errEl.textContent    = err;
                    errEl.style.display  = 'block';
                    btn.disabled         = true;
                } else {
                    ta.style.borderColor = '#4CAF50';
                    errEl.style.display  = 'none';
                    btn.disabled         = false;
                }
                return !err;
            }

            ta.addEventListener('input', runValidation);
            ta.addEventListener('blur',  runValidation);
            if (ta.value) runValidation();

            /* soumission normale — pas de fetch, POST classique */
            form.addEventListener('submit', function(e) {
                if (!runValidation()) {
                    e.preventDefault();
                    return;
                }
                setTimeout(function() {
                    btn.disabled = true;
                    btn.textContent = '⏳ Publication...';
                }, 0);
                /* laisser le formulaire se soumettre normalement */
            });
        })();

        /* ── Reply likes AJAX ── */
        (function () {
            document.querySelectorAll('.js-reply-like').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var u = btn.getAttribute('data-like-url');
                    var rid = btn.getAttribute('data-reply-id');
                    btn.disabled = true;
                    fetch(u, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'toggle_reply_like=1&reply_id=' + encodeURIComponent(rid),
                        credentials: 'same-origin'
                    }).then(function (r) { return r.json(); }).then(function (d) {
                        if (!d || !d.ok) return;
                        var c = btn.querySelector('.js-reply-count');
                        var l = btn.querySelector('.js-reply-label');
                        if (c) c.textContent = d.likes;
                        if (l) l.textContent = d.liked ? 'Retirer' : 'J\'aime';
                        btn.setAttribute('data-liked', d.liked ? '1' : '0');
                    }).catch(function () {}).finally(function () {
                        btn.disabled = false;
                    });
                });
            });
        })();

        (function () {
            var favBtn = document.getElementById('postFavoriteBtn');
            if (!favBtn) return;
            var countEl = document.getElementById('postFavoriteCount');
            var labelEl = document.getElementById('postFavoriteLabel');
            var url = favBtn.getAttribute('data-fav-url');
            favBtn.addEventListener('click', function (e) {
                if (e) e.preventDefault();
                favBtn.disabled = true;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'toggle_favorite=1',
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d || !d.ok) return;
                    favBtn.classList.toggle('favorited', !!d.favorited);
                    if (labelEl) labelEl.textContent = d.favorited ? 'Retirer favori' : 'Ajouter favoris';
                    if (countEl) countEl.textContent = parseInt(d.favorites || 0, 10);
                }).catch(function () {}).finally(function () {
                    favBtn.disabled = false;
                });
            });
        })();

        (function () {
            var reportBtn = document.getElementById('reportPostBtn');
            if (!reportBtn) return;
            reportBtn.addEventListener('click', function () {
                var reason = window.prompt('Motif du signalement :', 'Contenu inapproprié');
                if (reason === null) return;
                reportBtn.disabled = true;
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'report_post=1&reason=' + encodeURIComponent(reason),
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d) return;
                    alert(d.message || (d.ok ? 'Signalement envoyé.' : 'Échec du signalement.'));
                }).catch(function () {
                    alert('Erreur réseau.');
                }).finally(function () {
                    reportBtn.disabled = false;
                });
            });
        })();

        (function () {
            var pollBox = document.getElementById('pollBox');
            var voteBtn = document.getElementById('pollVoteBtn');
            if (!pollBox || !voteBtn) return;
            var pollMeta = document.getElementById('pollMeta');
            function renderPoll(poll, selectedOptionId) {
                if (!poll) return;
                var optionsWrap = document.getElementById('pollOptions');
                if (!optionsWrap) return;
                var html = '';
                (poll.options || []).forEach(function (opt) {
                    var votes = parseInt(opt.vote_count || 0, 10);
                    var total = parseInt(poll.total_votes || 0, 10);
                    var pct = total > 0 ? Math.round((votes / total) * 100) : 0;
                    var checked = parseInt(selectedOptionId || 0, 10) === parseInt(opt.id, 10) ? 'checked' : '';
                    html += '<label class="poll-option-row">';
                    html += '<span><input type="radio" name="poll_option" value="' + opt.id + '" ' + checked + '> ';
                    html += String(opt.option_text).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    html += '<div class="poll-bar"><span style="width:' + pct + '%"></span></div></span>';
                    html += '<strong>' + votes + ' (' + pct + '%)</strong></label>';
                });
                optionsWrap.innerHTML = html;
                if (pollMeta) pollMeta.textContent = (parseInt(poll.total_votes || 0, 10)) + ' vote(s)';
            }
            voteBtn.addEventListener('click', function () {
                var checked = document.querySelector('input[name="poll_option"]:checked');
                if (!checked) return;
                voteBtn.disabled = true;
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'submit_poll_vote=1&poll_id=' + encodeURIComponent(pollBox.getAttribute('data-poll-id')) +
                        '&option_id=' + encodeURIComponent(checked.value),
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d || !d.ok) return;
                    renderPoll(d.poll, d.userVoteOptionId);
                }).catch(function () {}).finally(function () {
                    voteBtn.disabled = false;
                });
            });
        })();

        (function () {
            var btn = document.getElementById('notifToggleBtn');
            var panel = document.getElementById('notifPanel');
            var countEl = document.getElementById('notifCount');
            var list = document.getElementById('notifItems');
            var markReadBtn = document.getElementById('markNotifReadBtn');
            if (!btn || !panel) return;

            function render(items) {
                if (!list) return;
                if (!Array.isArray(items) || items.length === 0) {
                    list.innerHTML = '<div class="notif-item">Aucune notification.</div>';
                    return;
                }
                list.innerHTML = items.map(function (n) {
                    var unread = parseInt(n.is_read || 0, 10) === 0 ? ' unread' : '';
                    var msg = String(n.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return '<div class="notif-item' + unread + '">' + msg + '</div>';
                }).join('');
            }

            function refreshNotifications() {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'notifications_fetch=1',
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d || !d.ok) return;
                    if (countEl) countEl.textContent = parseInt(d.count || 0, 10);
                    render(d.items || []);
                }).catch(function () {});
            }

            btn.addEventListener('click', function () {
                panel.classList.toggle('open');
                if (panel.classList.contains('open')) refreshNotifications();
            });

            if (markReadBtn) {
                markReadBtn.addEventListener('click', function () {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'notifications_mark_read=1',
                        credentials: 'same-origin'
                    }).then(function () {
                        if (countEl) countEl.textContent = '0';
                        refreshNotifications();
                    }).catch(function () {});
                });
            }
            setInterval(refreshNotifications, 10000);
        })();
    </script>
</body>
</html>
