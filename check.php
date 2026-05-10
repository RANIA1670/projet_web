<?php
/**
 * Vérification de la configuration du Forum
 * Accédez à: http://localhost/web%20mardi/check.php
 */

session_start();

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de configuration - Forum CityZen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #333;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .check-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .check-item.ok {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        .check-item.error {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }
        .check-item.warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .check-icon {
            font-size: 1.5rem;
            min-width: 30px;
            text-align: center;
        }
        .check-ok {
            color: #4caf50;
        }
        .check-error {
            color: #f44336;
        }
        .check-warning {
            color: #ff9800;
        }
        .check-details {
            color: #666;
            font-size: 0.9rem;
        }
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Vérification de configuration</h1>
            <p>Forum CityZen - Diagnostic système</p>
        </div>
        <div class="content">
            <?php
            
            $checks = [
                'PHP Version' => [
                    'required' => '7.4',
                    'actual' => phpversion(),
                    'check' => version_compare(phpversion(), '7.4', '>=')
                ],
                'Extension: PDO' => [
                    'required' => 'Activée',
                    'actual' => extension_loaded('pdo') ? '✓ Activée' : '✗ Manquante',
                    'check' => extension_loaded('pdo')
                ],
                'Extension: PDO MySQL' => [
                    'required' => 'Activée',
                    'actual' => extension_loaded('pdo_mysql') ? '✓ Activée' : '✗ Manquante',
                    'check' => extension_loaded('pdo_mysql')
                ],
                'Fichier Database.php' => [
                    'required' => 'Présent',
                    'actual' => file_exists(__DIR__ . '/config/Database.php') ? '✓ Présent' : '✗ Manquant',
                    'check' => file_exists(__DIR__ . '/config/Database.php')
                ],
                'Répertoire views' => [
                    'required' => 'Présent',
                    'actual' => is_dir(__DIR__ . '/views') ? '✓ Présent' : '✗ Manquant',
                    'check' => is_dir(__DIR__ . '/views')
                ],
                'Répertoire models' => [
                    'required' => 'Présent',
                    'actual' => is_dir(__DIR__ . '/models') ? '✓ Présent' : '✗ Manquant',
                    'check' => is_dir(__DIR__ . '/models')
                ],
                'Répertoire controllers' => [
                    'required' => 'Présent',
                    'actual' => is_dir(__DIR__ . '/controllers') ? '✓ Présent' : '✗ Manquant',
                    'check' => is_dir(__DIR__ . '/controllers')
                ],
            ];
            
            // Test de connexion à la base de données
            $dbCheck = [
                'status' => false,
                'message' => '',
                'type' => 'error'
            ];
            
            try {
                require_once __DIR__ . '/config/Database.php';
                $db = Database::getInstance();
                $conn = $db->getConnection();
                $dbCheck['status'] = true;
                $dbCheck['message'] = 'Connectée';
                $dbCheck['type'] = 'ok';
            } catch (Exception $e) {
                $dbCheck['status'] = false;
                $dbCheck['message'] = $e->getMessage();
                $dbCheck['type'] = 'error';
            }
            
            $checks['Connexion Base de données'] = [
                'required' => 'Connectée',
                'actual' => $dbCheck['message'],
                'check' => $dbCheck['status'],
                'type' => $dbCheck['type']
            ];

            $requiredTables = ['users', 'posts', 'replies', 'likes'];
            $missingTables = [];
            if ($dbCheck['status']) {
                $have = [];
                try {
                    $conn = Database::getInstance()->getConnection();
                    $have = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                    $have = is_array($have) ? array_map('strtolower', $have) : [];
                    foreach ($requiredTables as $t) {
                        if (!in_array(strtolower($t), $have, true)) {
                            $missingTables[] = $t;
                        }
                    }
                } catch (Throwable $e) {
                    $missingTables = $requiredTables;
                }
            }
            $tablesOk = $dbCheck['status'] && empty($missingTables);
            $checks['Tables MySQL (furum)'] = [
                'required' => implode(', ', $requiredTables),
                'actual' => $tablesOk
                    ? '✓ ' . implode(', ', $requiredTables)
                    : '✗ Manquantes : ' . (empty($missingTables) ? '?' : implode(', ', $missingTables))
                    . ' — importez init_database.sql ou config/align_furum_schema.sql',
                'check' => $tablesOk,
                'type' => $tablesOk ? 'ok' : 'error',
            ];

            $requiredPostCols = ['title', 'content', 'user_id', 'created_at', 'updated_at', 'view_count'];
            $missingPostCols = [];
            if ($dbCheck['status'] && isset($conn) && is_array($have) && in_array('posts', $have, true)) {
                try {
                    $pcols = $conn->query('SHOW COLUMNS FROM posts')->fetchAll(PDO::FETCH_COLUMN);
                    $pcolsL = array_map('strtolower', $pcols ?: []);
                    foreach ($requiredPostCols as $c) {
                        if (!in_array(strtolower($c), $pcolsL, true)) {
                            $missingPostCols[] = $c;
                        }
                    }
                } catch (Throwable $e) {
                    $missingPostCols = $requiredPostCols;
                }
            }
            $postsColsOk = empty($missingPostCols);
            $checks['Colonnes table posts'] = [
                'required' => implode(', ', $requiredPostCols),
                'actual' => $postsColsOk
                    ? '✓ Schéma conforme à models/Post.php'
                    : '✗ Manquantes : ' . implode(', ', $missingPostCols)
                    . ' — exécutez config/align_furum_schema.sql dans phpMyAdmin (base furum)',
                'check' => $postsColsOk,
                'type' => $postsColsOk ? 'ok' : 'error',
            ];
            
            // Affichage des vérifications
            foreach ($checks as $name => $check) {
                $type = isset($check['type']) ? $check['type'] : ($check['check'] ? 'ok' : 'error');
                $icon = $type === 'ok' ? '✓' : '✗';
                $iconClass = $type === 'ok' ? 'check-ok' : 'check-error';
                $itemClass = "check-item {$type}";
                
                echo "<div class=\"{$itemClass}\">";
                echo "<div class=\"check-icon {$iconClass}\">$icon</div>";
                echo "<div>";
                echo "<div><strong>$name</strong></div>";
                echo "<div class=\"check-details\">" . htmlspecialchars($check['actual']) . "</div>";
                echo "</div>";
                echo "</div>";
            }
            
            // Résumé
            $allOk = array_reduce($checks, function($carry, $check) {
                return $carry && (isset($check['type']) ? $check['type'] === 'ok' : $check['check']);
            }, true);
            
            if ($allOk) {
                echo "<div class=\"check-item ok\" style=\"margin-top: 30px; background: #c8e6c9; border-left-color: #4caf50;\">";
                echo "<div class=\"check-icon check-ok\">✓</div>";
                echo "<div>";
                echo "<div><strong>✓ Tous les contrôles sont OK</strong></div>";
                echo "<div class=\"check-details\">Vous pouvez commencer à utiliser l'application.</div>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class=\"check-item error\" style=\"margin-top: 30px; background: #ffcdd2; border-left-color: #f44336;\">";
                echo "<div class=\"check-icon check-error\">✗</div>";
                echo "<div>";
                echo "<div><strong>✗ Certains contrôles ont échoué</strong></div>";
                echo "<div class=\"check-details\">Veuillez corriger les erreurs avant de continuer.</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>

            <div class="actions">
                <a href="index.php" class="btn btn-primary">➜ Aller à l'application</a>
                <a href="check.php" class="btn btn-secondary">🔄 Rafraîchir</a>
            </div>
        </div>
    </div>
</body>
</html>
