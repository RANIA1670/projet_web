<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityZen Forum</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1><a href="/index.php">CityZen Forum</a></h1>
                <nav>
                    <a href="/index.php">Accueil</a>
                    <a href="/index.php?action=create">Nouveau Post</a>
                    <a href="/admin.php">Administration</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
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

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 CityZen Forum. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="/public/js/main.js"></script>
</body>
</html>