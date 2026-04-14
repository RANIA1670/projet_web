<?php
// Vue: Page 404 (front office)
$pageTitle = 'Page non trouvée';
ob_start();
?>

<div class="error-page">
    <div class="error-content">
        <h1>404</h1>
        <h2>Page non trouvée</h2>
        <p>Désolé, la page que vous recherchez n'existe pas.</p>
        <a href="/index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>