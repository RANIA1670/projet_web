<?php
/**
 * Vue Front-Office : Affichage détaillé d'un post avec ses réponses
 */

// Simulation d'une session utilisateur
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Vérifier si un ID de post a été fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list_posts.php');
    exit;
}

$postId = (int)$_GET['id'];

// Récupérer le post et ses réponses
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
$controller = new ForumController();

$post = $controller->showPost($postId);
if (!$post) {
    header('Location: list_posts.php');
    exit;
}

$replies = $controller->listRepliesByPost($postId);
$postLikeCount = $controller->countPostLikes($postId);
$userHasLikedPost = $currentUserId > 0 ? $controller->hasUserLikedPost($postId, $currentUserId) : false;

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
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
            color: #4CAF50;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .post-detail {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .post-title {
            font-size: 2em;
            color: #2d5016;
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
            color: #555;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }

        .post-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95em;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #666;
        }

        .btn-secondary:hover {
            background-color: #555;
        }

        .btn-like {
            background-color: #ff6b6b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-like:hover {
            background-color: #ee5a52;
        }

        .btn-like.liked {
            background-color: #e63946;
        }

        .stats {
            display: flex;
            gap: 30px;
            color: #666;
            font-size: 0.95em;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
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
            color: #2d5016;
            margin-bottom: 20px;
        }

        .reply-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .replies-list {
            display: grid;
            gap: 20px;
        }

        .reply-card {
            background-color: white;
            border-left: 4px solid #4CAF50;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            color: #2d5016;
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
            color: #4CAF50;
            cursor: pointer;
            text-decoration: none;
        }

        .reply-action-btn:hover {
            color: #2d5016;
            text-decoration: underline;
        }

        .empty-replies {
            text-align: center;
            padding: 30px;
            color: #999;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="list_posts.php">← Retour au forum</a>
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

            <div class="post-actions">
                <?php if ($currentUserId > 0 && $currentUserId === $post->getUserId()): ?>
                    <a href="edit_post.php?id=<?php echo $post->getId(); ?>" class="btn btn-secondary">✏️ Modifier</a>
                    <a href="delete_post.php?id=<?php echo $post->getId(); ?>" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr ?');">🗑️ Supprimer</a>
                <?php endif; ?>
            </div>

            <div class="stats">
                <div class="stat">
                    <span>👁️</span>
                    <span><?php echo $post->getViewCount(); ?> vue<?php echo $post->getViewCount() > 1 ? 's' : ''; ?></span>
                </div>
                <div class="stat">
                    <span>👍</span>
                    <span><?php echo $postLikeCount; ?> like<?php echo $postLikeCount > 1 ? 's' : ''; ?></span>
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
                <h3 style="margin-bottom: 15px; color: #2d5016;">Ajouter une réponse</h3>
                <?php if ($currentUserId === 0): ?>
                    <p style="color: #666; margin-bottom: 15px;">
                        <a href="login.php">Connectez-vous</a> pour participer à la discussion.
                    </p>
                <?php else: ?>
                    <form method="POST" id="replyPostForm" novalidate>
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
                        <button type="submit" class="btn" id="replySubmit" disabled>📤 Publier votre réponse</button>
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
                                <button class="reply-action-btn" onclick="toggleReplyLike(<?php echo $reply->getId(); ?>, <?php echo $currentUserId; ?>)">
                                    👍 <?php echo $replyLikeCount; ?> like<?php echo $replyLikeCount > 1 ? 's' : ''; ?>
                                </button>
                                <?php if ($currentUserId > 0 && $currentUserId === $reply->getUserId()): ?>
                                    <a href="edit_reply.php?id=<?php echo $reply->getId(); ?>&post_id=<?php echo $postId; ?>" class="reply-action-btn">✏️ Modifier</a>
                                    <a href="delete_reply.php?id=<?php echo $reply->getId(); ?>&post_id=<?php echo $postId; ?>" class="reply-action-btn" onclick="return confirm('Êtes-vous sûr ?');">🗑️ Supprimer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleReplyLike(replyId, userId) {
            if (userId === 0) {
                alert('Vous devez être connecté pour liker une réponse.');
                return;
            }
            console.log('Toggle like for reply:', replyId);
        }

        /* ── Validation réponse en temps réel ── */
        (function () {
            var ta      = document.getElementById('reply_content');
            var btn     = document.getElementById('replySubmit');
            var countEl = document.getElementById('rCount');
            var barEl   = document.getElementById('rBar');
            var errEl   = document.getElementById('rError');
            if (!ta) return;

            var checks = [
                function(v){ return v.trim().length === 0      ? 'La réponse est obligatoire.'                          : null; },
                function(v){ return v.trim().length < 5        ? 'Minimum 5 caractères.'                                : null; },
                function(v){ return v.trim().length > 2000     ? 'Maximum 2 000 caractères.'                           : null; },
                function(v){ return /<script/i.test(v)         ? 'Pas de balises script.'                               : null; },
                function(v){ return /(.)\1{6,}/.test(v)      ? 'Répétition excessive de caractères détectée.'           : null; },
            ];

            function run() {
                var val = ta.value, len = val.length;
                countEl.textContent = len + ' / 2000';
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
            }

            ta.addEventListener('input', run);
            ta.addEventListener('blur',  run);
            if (ta.value) run();
        })();
    </script>
</body>
</html>
