<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/data.php';

cityzen_require_agent();

$q = trim((string) ($_GET['q'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'id');
$dir = strtoupper((string) ($_GET['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

$rows = cityzen_users_export_rows($q, $sort, $dir, 500);
$app = (string) ($cityzen['app_name'] ?? 'CityZen');
$city = (string) ($cityzen['city_name'] ?? '');
$exportedAt = date('d/m/Y H:i');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>Export utilisateurs | <?= htmlspecialchars($app) ?></title>
  <style>
    :root { --ink: #243b53; --line: #d9dfe7; --muted: #7b8794; }
    body { font-family: system-ui, Segoe UI, sans-serif; color: var(--ink); margin: 24px; }
    h1 { font-size: 1.35rem; margin: 0 0 8px; }
    .meta { color: var(--muted); font-size: 0.9rem; margin-bottom: 20px; }
    .toolbar { margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    button { font: inherit; padding: 10px 16px; border-radius: 10px; border: 0; background: #2db35d; color: #fff; font-weight: 700; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; font-size: 0.92rem; }
    th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); }
    th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); }
    .hint { font-size: 0.85rem; color: var(--muted); max-width: 42rem; line-height: 1.45; }
    @media print {
      body { margin: 12mm; }
      .toolbar, .hint { display: none !important; }
      table { font-size: 10pt; }
    }
  </style>
</head>
<body>
  <h1>Liste des utilisateurs</h1>
  <p class="meta"><?= htmlspecialchars($app) ?><?= $city !== '' ? ' — ' . htmlspecialchars($city) : '' ?> — genere le <?= htmlspecialchars($exportedAt) ?> — <?= count($rows) ?> ligne(s) (max 500)</p>
  <div class="toolbar">
    <button type="button" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
  </div>
  <p class="hint">Utilisez la fonction d&apos;impression du navigateur (Chrome : Imprimer &gt; Destination &quot;Enregistrer au format PDF&quot;) pour obtenir un fichier PDF sans extension supplementaire.</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Utilisateur</th>
        <th>Role</th>
        <th>Etat</th>
        <th>Inscription</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int) $r['id'] ?></td>
          <td><?= htmlspecialchars((string) $r['username']) ?></td>
          <td><?= $r['role'] === 'admin' ? 'Administrateur' : 'Citoyen' ?></td>
          <td><?= (int) ($r['blocked'] ?? 0) === 1 ? 'Bloque' : 'Actif' ?></td>
          <td><?= htmlspecialchars((string) $r['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
