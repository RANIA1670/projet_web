<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - CityZen Forum</title>
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>CityZen Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="/admin.php" class="nav-link <?php echo (!isset($_GET['action']) || $_GET['action'] === 'dashboard') ? 'active' : ''; ?>">
                    <span>📊</span> Dashboard
                </a>
                <a href="/admin.php?action=posts" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'posts') ? 'active' : ''; ?>">
                    <span>📝</span> Posts
                </a>
                <a href="/admin.php?action=replies" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'replies') ? 'active' : ''; ?>">
                    <span>💬</span> Replies
                </a>
                <a href="/admin.php?action=logout" class="nav-link logout">
                    <span>🚪</span> Déconnexion
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1><?php echo $pageTitle ?? 'Administration'; ?></h1>
                <div class="user-info">
                    Connecté en tant que: <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                </div>
            </header>

            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php
                // Le contenu spécifique à chaque page sera inséré ici
                ?>
            </div>
        </main>
    </div>

    <script src="/public/js/admin.js"></script>
</body>
</html>