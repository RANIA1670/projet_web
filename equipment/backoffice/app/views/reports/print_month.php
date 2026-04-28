<?php
/** @var int $year */
/** @var int $month */
/** @var array $rows */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Réservations <?= (int) $month ?>/<?= (int) $year ?></title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 24px; color: #243b53; }
    h1 { font-size: 1.25rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 11px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f0f0f0; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>
  <p class="no-print"><a href="javascript:window.print()">Imprimer / PDF</a></p>
  <h1>Réservations — <?= sprintf('%02d', (int) $month) ?>/<?= (int) $year ?></h1>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Début</th><th>Fin</th><th>Statut</th><th>Équipement</th><th>Lieu</th><th>Type</th><th>Utilisateur</th><th>Objet</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><?= htmlspecialchars((string) $r['start_date'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $r['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $r['status'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $r['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $r['location'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $r['type_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) ($r['user_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) ($r['purpose'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
