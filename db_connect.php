<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = getPDO();
    $connected = true;
} catch (Exception $e) {
    $connected = false;
    $error = $e->getMessage();
}

$signalementCount = 0;
$interventionCount = 0;
if ($connected) {
    try {
        $signalementCount = $pdo->query('SELECT COUNT(*) FROM signalement')->fetchColumn();
        $interventionCount = $pdo->query('SELECT COUNT(*) FROM intervention')->fetchColumn();
    } catch (PDOException $e) {
        $error = $e->getMessage();
        $connected = false;
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion BDD CityZen</title>
  <style>
    body { font-family: Arial, sans-serif; background: #F4F7FB; margin: 0; padding: 40px; }
    .card { max-width: 760px; margin: 0 auto; background: #fff; border-radius: 18px; border: 1px solid #E2E8F0; padding: 32px; box-shadow: 0 24px 60px rgba(15,23,42,.08); }
    h1 { margin: 0 0 16px; color: #0F172A; }
    .status { margin-top: 24px; padding: 20px; border-radius: 16px; }
    .success { background: #ECFDF5; color: #14532D; border: 1px solid rgba(34,197,94,.18); }
    .error { background: #FEE2E2; color: #991B1B; border: 1px solid rgba(239,68,68,.18); }
    .stats { display: grid; gap: 16px; margin-top: 20px; }
    .stat { padding: 18px; border: 1px solid #E2E8F0; border-radius: 16px; background: #F8FAFC; }
    .stat strong { display: block; font-size: 1.4rem; margin-bottom: 8px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Test de connexion PDO</h1>
    <p>Page de vérification de la connexion à la base de données `cityzen_db`.</p>

    <?php if ($connected): ?>
      <div class="status success">
        <strong>Connexion réussie</strong>
        <p>La connexion à la base de données fonctionne avec PDO.</p>
      </div>
      <div class="stats">
        <div class="stat">
          <strong><?= htmlspecialchars($signalementCount, ENT_QUOTES) ?></strong>
          Signalements dans la table <code>signalement</code>
        </div>
        <div class="stat">
          <strong><?= htmlspecialchars($interventionCount, ENT_QUOTES) ?></strong>
          Interventions dans la table <code>intervention</code>
        </div>
      </div>
    <?php else: ?>
      <div class="status error">
        <strong>Erreur de connexion</strong>
        <p><?= htmlspecialchars($error ?? 'Erreur inconnue', ENT_QUOTES) ?></p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
