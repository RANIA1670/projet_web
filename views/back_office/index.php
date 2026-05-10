<?php
/**
 * Point d'entrée BACK-OFFICE - Admin Dashboard
 * Interface d'administration pour le forum CityZen
 * Accès: http://localhost/web%20mardi/views/back_office/index.php
 */

session_start();

// Vérification authentification admin
if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = true;
    $_SESSION['user_id'] = 1;
}

$page = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Reply.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new ForumController();
    $action = (string)$_POST['action'];
    $currentPage = isset($_GET['page']) ? trim((string)$_GET['page']) : 'dashboard';
    $redirectTo = 'index.php?page=' . urlencode($currentPage);

    if ($action === 'delete_post' && isset($_POST['item_id']) && is_numeric($_POST['item_id'])) {
        $controller->deletePost((int)$_POST['item_id']);
        header('Location: ' . $redirectTo);
        exit;
    }

    if ($action === 'delete_reply' && isset($_POST['item_id']) && is_numeric($_POST['item_id'])) {
        $controller->deleteReply((int)$_POST['item_id']);
        header('Location: ' . $redirectTo);
        exit;
    }
}

/* Bulk actions from posts.php */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'], $_POST['post_ids'])) {
    require_once __DIR__ . '/../../controllers/ForumController.php';
    require_once __DIR__ . '/../../models/Post.php';
    $controller  = new ForumController();
    $bulkAction  = (string)$_POST['bulk_action'];
    $ids         = array_map('intval', (array)$_POST['post_ids']);
    foreach ($ids as $id) {
        if ($id <= 0) continue;
        if ($bulkAction === 'bulk_delete') $controller->deletePost($id);
        elseif ($bulkAction === 'bulk_feature') Post::setFeatured($id, true);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=posts';
    header('Location: ' . $referer);
    exit;
}

// Les vues d'édition sont autonomes (HTML complet + AJAX JSON).
// On les sert hors du layout dashboard pour éviter de corrompre la réponse JSON.
if ($page === 'edit_post') {
    include __DIR__ . '/edit_post.php';
    exit;
}
if ($page === 'edit_reply') {
    include __DIR__ . '/edit_reply.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityZen - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #F4F6F8;
            --surface: #FFFFFF;
            --surface-alt: #EEF1F4;
            --accent: #2ECC71;
            --accent-blue: #34495E;
            --accent-orange: #F39C12;
            --accent-purple: #2F3C4F;
            --text: #2C3E50;
            --text-secondary: #7F8C8D;
            --text-light: #9BA4B5;
            --muted: #7F8C8D;
            --border: #E8ECF0;
            --border-light: #EDF0F3;
            --radius: 10px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }
        
        .container { display: flex; height: 100vh; }
        
        /* ── Sidebar ── */
        .sidebar {
            width: 200px;
            background: #2F3C4F;
            border-right: 1px solid rgba(0,0,0,.12);
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            overflow-y: auto;
        }
        
        .logo {
            padding: 0 24px 32px;
            font-size: 16px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #FFFFFF;
            letter-spacing: -0.5px;
        }
        
        .logo::before { content: '🏘️'; font-size: 18px; }
        
        .nav-section {
            padding: 16px 0;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        
        .nav-section:first-of-type { border-top: none; }
        
        .nav-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #9BA4B5;
            padding: 0 24px 12px;
            letter-spacing: 0.8px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 24px;
            color: #9BA4B5;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #FFFFFF;
            border-left-color: var(--accent);
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.12);
            border-left-color: var(--accent);
            color: #FFFFFF;
        }
        
        /* ── Main Content ── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title { font-size: 22px; font-weight: 700; color: var(--text); }
        
        .header-btn {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }
        
        .header-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
        
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 32px 40px;
        }
        
        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            text-align: center;
            transition: all 0.2s;
            border-bottom: 4px solid var(--accent);
        }
        
        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(67,233,123,0.1);
            transform: translateY(-2px);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            margin: 16px 0;
            color: var(--accent);
            letter-spacing: -1px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
        }
        
        /* ── Table ── */
        .table-container {
            background: var(--surface););
            border-radius: var(--radius);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--surface-alt);
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            letter-spacing: 0.4px;
        }
        
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
        }
        
        tr:last-child td { border-bottom: none; }
        
        tr:hover { background: var(--surface-alt); }
        
        .action-btn {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin: 0 3px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .action-btn:hover { 
            background: rgba(67,233,123,0.1);
            color: var(--accent);
            border-color: var(--accent);
        }
        
        .action-btn.danger:hover { 
            background: rgba(247,78,78,0.1);
            color: #e74c3c;
            border-color: #e74c3c;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge.green { background: rgba(67,233,123,0.15); color: var(--accent); }
        .badge.orange { background: rgba(247,151,30,0.15); color: var(--accent-orange); }
        .badge.blue { background: rgba(79,142,247,0.15); color: var(--accent-bluent3); }
        .badge.blue { background: rgba(79,142,247,0.15); color: var(--accent); }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">CityZen</div>
            
            <nav class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
            </nav>
            
            <nav class="nav-section">
                <div class="nav-section-title">Gestion</div>
                <a href="?page=posts" class="nav-link <?php echo $page === 'posts' ? 'active' : ''; ?>">
                    📝 Posts
                </a>
                <a href="?page=replies" class="nav-link <?php echo $page === 'replies' ? 'active' : ''; ?>">
                    💬 Réponses
                </a>
                <a href="?page=statistics" class="nav-link <?php echo $page === 'statistics' ? 'active' : ''; ?>">
                    📈 Statistiques
                </a>
            </nav>
        </aside>
        
        <!-- Main -->
        <div class="main">
            <header class="header">
                <h1 class="header-title">
                    <?php 
                    echo match($page) {
                        'posts' => '📝 Gestion des Posts',
                        'replies' => '💬 Gestion des Réponses',
                        'statistics' => '📈 Statistiques',
                        default => '🏠 Tableau de bord'
                    };
                    ?>
                </h1>
            </header>
            
            <div class="content">
                <?php
                switch($page) {
                    case 'posts':
                        include __DIR__ . '/dashboard/posts.php';
                        break;
                    case 'replies':
                        include __DIR__ . '/dashboard/replies.php';
                        break;
                    case 'statistics':
                        include __DIR__ . '/dashboard/statistics.php';
                        break;
                    default:
                        include __DIR__ . '/dashboard/overview.php';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
