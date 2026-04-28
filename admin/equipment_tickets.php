<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/layout.php';

cityzen_require_agent();

require_once __DIR__ . '/../model/db.php';
require_once __DIR__ . '/../equipment/backoffice/app/models/EquipmentIssue.php';

$pdo = cityzen_db();
$issues = new EquipmentIssue($pdo);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ticket_status'])) {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Jeton invalide.'];
    } else {
        $tid = (int) ($_POST['ticket_id'] ?? 0);
        $newSt = (string) ($_POST['new_status'] ?? '');
        if ($tid > 0 && in_array($newSt, ['open', 'acknowledged', 'resolved'], true) && $issues->setStatus($tid, $newSt)) {
            $flash = ['type' => 'success', 'msg' => 'Statut mis à jour.'];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Mise à jour impossible.'];
        }
    }
}

$tab = (string) ($_GET['tab'] ?? 'open');
if (!in_array($tab, ['open', 'acknowledged', 'resolved', 'all'], true)) {
    $tab = 'open';
}

$filter = $tab === 'all' ? 'all' : $tab;
$rows = $issues->allForAdmin($filter);

$issueTypeLabels = [
    'not_working' => 'Ne fonctionne pas',
    'damaged' => 'Endommagé',
    'lost' => 'Perdu',
];

cityzen_render_head('Tickets équipement');
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= ($item['key'] ?? '') === 'eq-tickets' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Signalements pannes — équipement</h1>
        <p class="admin-header-lead">Tickets créés depuis le site public (citoyens connectés).</p>
      </div>
    </header>

    <?php if (!empty($flash)): ?>
      <p class="admin-flash <?= ($flash['type'] ?? '') === 'success' ? 'admin-flash-success' : 'admin-flash-error' ?>"><?= htmlspecialchars((string) $flash['msg'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <p style="margin-bottom:16px">
      <a href="?tab=open" class="<?= $tab === 'open' ? 'pill progress' : 'pill done' ?>" style="margin-right:8px">Ouverts</a>
      <a href="?tab=acknowledged" class="<?= $tab === 'acknowledged' ? 'pill progress' : 'pill done' ?>" style="margin-right:8px">Pris en compte</a>
      <a href="?tab=resolved" class="<?= $tab === 'resolved' ? 'pill progress' : 'pill done' ?>" style="margin-right:8px">Résolus</a>
      <a href="?tab=all" class="<?= $tab === 'all' ? 'pill progress' : 'pill done' ?>">Tous</a>
    </p>

    <div class="reports-table">
      <div class="table-head" style="grid-template-columns: 2fr 1fr 1fr 1fr 2fr 1.2fr">
        <span>Équipement</span>
        <span>Type</span>
        <span>Auteur</span>
        <span>Date</span>
        <span>Description</span>
        <span>Action</span>
      </div>
      <?php foreach ($rows as $t): ?>
        <div class="table-row" style="grid-template-columns: 2fr 1fr 1fr 1fr 2fr 1.2fr; align-items:start">
          <span><strong><?= htmlspecialchars((string) $t['equipment_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span><?= htmlspecialchars($issueTypeLabels[(string) $t['issue_type']] ?? (string) $t['issue_type'], ENT_QUOTES, 'UTF-8') ?></span>
          <span><?= htmlspecialchars((string) ($t['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></span>
          <span><?= htmlspecialchars((string) $t['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
          <span style="font-size:0.85rem"><?= nl2br(htmlspecialchars((string) $t['description'], ENT_QUOTES, 'UTF-8')) ?>
            <?php if (!empty($t['photo_path'])): ?>
              <br><a href="<?= htmlspecialchars(cityzen_asset('admin/issue_photo.php?id=' . (int) $t['id']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Voir la photo</a>
            <?php endif; ?>
          </span>
          <span>
            <form method="post" action="" style="display:flex;flex-direction:column;gap:6px">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="ticket_status" value="1">
              <input type="hidden" name="ticket_id" value="<?= (int) $t['id'] ?>">
              <select name="new_status">
                <option value="open" <?= ($t['status'] ?? '') === 'open' ? 'selected' : '' ?>>Ouvert</option>
                <option value="acknowledged" <?= ($t['status'] ?? '') === 'acknowledged' ? 'selected' : '' ?>>Pris en compte</option>
                <option value="resolved" <?= ($t['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Résolu</option>
              </select>
              <button type="submit" class="users-role-submit">Appliquer</button>
            </form>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($rows === []): ?>
      <p class="panel-lead">Aucun ticket dans cette vue.</p>
    <?php endif; ?>
  </main>
</div>
<?php cityzen_render_footer(); ?>
