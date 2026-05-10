<?php
/**
 * Test d'accès - Si vous voyez ce message, le serveur fonctionne!
 */
echo "<h1>✓ Le serveur PHP fonctionne!</h1>";
echo "<p>Répertoire courant: " . dirname(__FILE__) . "</p>";
echo "<p>Fichier: " . __FILE__ . "</p>";

// Tester l'accès aux fichiers
$files = [
    'config/Database.php' => file_exists(__DIR__ . '/config/Database.php'),
    'index.php' => file_exists(__DIR__ . '/index.php'),
    'controllers/ForumController.php' => file_exists(__DIR__ . '/controllers/ForumController.php'),
];

echo "<h2>Fichiers détectés:</h2>";
echo "<ul>";
foreach ($files as $file => $exists) {
    $status = $exists ? '✓' : '✗';
    echo "<li>$status $file</li>";
}
echo "</ul>";

// Test de connexion BD
echo "<h2>Connexion Base de données:</h2>";
try {
    require_once __DIR__ . '/config/Database.php';
    $db = Database::getInstance();
    echo "<p style='color:green;'>✓ Connexion réussie!</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<hr>
<h2>Comment accéder à l'application:</h2>
<ol>
    <li><strong>Vérifiez où le dossier "web mardi" est situé:</strong>
        <ul>
            <li>XAMPP: devrait être dans <code>c:\xampp\htdocs\</code></li>
            <li>WAMP: devrait être dans <code>c:\wamp64\www\</code></li>
        </ul>
    </li>
    <li><strong>Déplacez-le si nécessaire</strong></li>
    <li><strong>Accédez à:</strong> <code>http://localhost/web%20mardi/</code></li>
</ol>
