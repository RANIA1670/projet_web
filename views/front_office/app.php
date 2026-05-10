<?php
/**
 * Point d'entrée FRONT-OFFICE - Interface Utilisateur
 * http://localhost/web%20mardi/views/front_office/app.php
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/ForumRedirect.php';
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Reply.php';
require_once __DIR__ . '/../../models/Like.php';

$controller = new ForumController();

/** Erreurs / valeurs repost du formulaire « Créer un post » (traité avant tout HTML). */
$createPostErrors = [];
$createPostOld = ['title' => '', 'content' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'create') {
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $createPostOld = ['title' => $title, 'content' => $content];

    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        $_SESSION['user_id'] = 1;
        $userId = 1;
    }

    $v = (new FormValidator())
        ->required('title', $title, 'Le titre')
        ->minLength('title', $title, 5, 'Le titre')
        ->maxLength('title', $title, 150, 'Le titre')
        ->noScript('title', $title, 'Le titre')
        ->noExcessiveRepeat('title', $title, 6, 'Le titre')
        ->hasMeaningfulContent('title', $title, 'Le titre')
        ->required('content', $content, 'Le contenu')
        ->minLength('content', $content, 20, 'Le contenu')
        ->maxLength('content', $content, 5000, 'Le contenu')
        ->noScript('content', $content, 'Le contenu')
        ->noExcessiveRepeat('content', $content, 8, 'Le contenu')
        ->hasMeaningfulContent('content', $content, 'Le contenu');

    if ($v->fails()) {
        $createPostErrors = $v->getErrors();
        $page = 'home';
    } elseif ($controller->createPost($userId, $title, $content)) {
        header('Location: ' . forum_front_url('page=create&published=1'));
        exit;
    } else {
        $createPostErrors['global'] = 'Erreur lors de l\'enregistrement. Réessayez plus tard.';
        $page = 'home';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityZen Forum - Discussions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --header-bg:    #2F3C4F;
            --page-bg:      #F4F6F8;
            --surface:      #FFFFFF;
            --surface-alt:  #EEF1F4;
            --green:        #2ECC71;
            --navy:         #34495E;
            --orange:       #F39C12;
            --gray-btn:     #95A5A6;
            --title:        #2C3E50;
            --text-secondary: #7F8C8D;
            --link-muted:   #9BA4B5;
            --border:       #E8ECF0;
            --border-light: #EDF0F3;
            --radius:       10px;
            --text:         var(--title);
            --muted:        var(--text-secondary);
            --accent:       var(--green);
            --surface2:     #E8ECF0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--page-bg); color: var(--title); min-height: 100vh; line-height: 1.5; }
        
        /* ── Header ── */
        .header {
            background: var(--header-bg);
            border-bottom: 1px solid rgba(0,0,0,.12);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 18px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.5px;
        }
        
        .nav-links {
            display: flex;
            gap: 32px;
        }
        
        .nav-links a {
            color: var(--link-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #FFFFFF;
        }
        
        .btn-primary {
            background: var(--green);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .btn-primary:hover { filter: brightness(1.08); transform: translateY(-1px); }
        
        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            padding: 10px 18px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover { border-color: var(--green); color: var(--navy); }
        
        /* ── Main Content ── */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 40px;
        }
        
        /* ── Posts List ── */
        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .post-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .post-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: var(--accent);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .post-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .post-meta {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }
        
        .post-content {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light);
        }
        
        .post-stats {
            display: flex;
            gap: 20px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .post-actions {
            display: flex;
            gap: 8px;
        }
        
        .icon-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
            padding: 6px 8px;
        }
        
        .icon-btn:hover { color: var(--green); }
        
        /* ── Modal Form ── */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            color: var(--text);
        }

        .modal-global-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.35);
            color: #c0392b;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }

        .field-msg-error {
            color: #c0392b;
            font-size: 0.8rem;
            margin-top: 6px;
            margin-bottom: 0;
        }

        .form-input.input-error,
        .form-textarea.input-error {
            border-color: #e74c3c;
        }

        .req { color: #e74c3c; font-weight: 700; }

        .flash-success {
            background: rgba(46, 204, 113, 0.12);
            border: 1px solid rgba(46, 204, 113, 0.35);
            color: var(--title);
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .form-input, .form-textarea {
            width: 100%;
            background: var(--surface-alt);
            border: 1px solid var(--border-light);
            color: var(--text);
            padding: 10px 12px;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }
        
        /* ── Single Post View ── */
        .post-view {
            background: var(--surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 32px;
        }
        
        .replies-section {
            margin-top: 32px;
        }
        
        .replies-title {
            font-size: 16px;
            color: var(--text);
        }
        
        .reply-card {
            background: var(--surface2);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        
        .reply-meta {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        
        .badge {
            display: inline-block;
            background: rgba(46, 204, 113, 0.15);
            color: var(--navy);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--muted);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.5px; color: var(--title); }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">🏘️ CityZen Forum</div>
        <nav class="nav-links">
            <a href="?page=home">Accueil</a>
            <a href="?page=home">Tous les posts</a>
        </nav>
    </header>
    
    <!-- Main Content -->
    <div class="container">
        <?php
        switch($page) {
            case 'post':
                include __DIR__ . '/pages/single-post.php';
                break;
            case 'create':
                header('Location: app.php?page=home');
                exit;
            default:
                include __DIR__ . '/pages/home.php';
        }
        ?>
    </div>
    
    <!-- Create Post Modal -->
    <div class="modal <?= !empty($createPostErrors) ? 'active' : '' ?>" id="createModal">
        <div class="modal-content">
            <div class="modal-header">✍️ Créer un nouveau post</div>
            <?php if (!empty($createPostErrors['global'])): ?>
                <div class="modal-global-error" role="alert"><?= htmlspecialchars($createPostErrors['global']) ?></div>
            <?php endif; ?>
            <form method="POST" action="?page=create" id="createPostForm">
                <div class="form-group">
                    <label class="form-label">Titre <span class="req">*</span></label>
                    <input type="text" name="title" class="form-input <?= isset($createPostErrors['title']) ? 'input-error' : '' ?>"
                           placeholder="Titre de votre post (5–150 car.)" required minlength="5" maxlength="150"
                           value="<?= htmlspecialchars($createPostOld['title']) ?>">
                    <?php if (!empty($createPostErrors['title'])): ?>
                        <p class="field-msg-error"><?= htmlspecialchars($createPostErrors['title']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Contenu <span class="req">*</span></label>
                    <textarea name="content" class="form-textarea <?= isset($createPostErrors['content']) ? 'input-error' : '' ?>"
                              placeholder="Décrivez votre sujet (20–5000 car.)" required minlength="20" maxlength="5000"><?= htmlspecialchars($createPostOld['content']) ?></textarea>
                    <?php if (!empty($createPostErrors['content'])): ?>
                        <p class="field-msg-error"><?= htmlspecialchars($createPostErrors['content']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeCreateModal()">Annuler</button>
                    <button type="submit" class="btn-primary">Publier</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }
        
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    </script>
</body>
</html>
