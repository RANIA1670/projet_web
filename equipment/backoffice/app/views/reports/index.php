<?php
/** @var int $year */
/** @var int $month */
/** @var array $forecast */
/** @var array $violations */
?>
<header class="topbar topbar-bo bo-module-header">
  <div class="bo-topbar-titles">
    <h1 class="bo-page-title">Rapports &amp; analytique</h1>
    <p class="bo-page-sub">Export CSV, aperçu imprimable (PDF via navigateur), maintenance prévisionnelle, utilisateurs en retard.</p>
  </div>
</header>

<div class="card">
  <h3>Export réservations par mois</h3>
  <form method="get" action="<?= htmlspecialchars(bo_url('reports'), ENT_QUOTES, 'UTF-8') ?>" class="bo-filter-grid" style="align-items:flex-end;">
    <input type="hidden" name="route" value="reports">
    <label>Mois
      <select name="month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= $m ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Année
      <input type="number" name="year" value="<?= (int) $year ?>" min="2000" max="2100">
    </label>
    <button type="submit" class="btn-bo btn-bo--accept">Actualiser</button>
    <a class="btn-bo btn-bo--accept" href="<?= htmlspecialchars(bo_url('reports', ['export' => 'csv', 'year' => $year, 'month' => $month]), ENT_QUOTES, 'UTF-8') ?>">Télécharger CSV</a>
    <a class="btn-bo" style="background:var(--nav);color:#fff;text-decoration:none;padding:7px 14px;border-radius:8px;" target="_blank" href="<?= htmlspecialchars(bo_url('reports', ['print' => '1', 'year' => $year, 'month' => $month]), ENT_QUOTES, 'UTF-8') ?>">Aperçu PDF (impression)</a>
  </form>
  <p class="muted-note">Le PDF : ouvrir l’aperçu puis <strong>Fichier → Imprimer → Enregistrer au format PDF</strong>.</p>
</div>

<div class="card">
  <h3>Maintenance prévisionnelle (&gt; 6 mois sans intervention)</h3>
  <div class="bo-table-wrap">
    <table class="transport-table bo-table">
      <thead>
        <tr><th>Équipement</th><th>Type</th><th>Lieu</th><th>Dernière maintenance</th></tr>
      </thead>
      <tbody>
      <?php foreach ($forecast as $f): ?>
        <tr>
          <td><?= htmlspecialchars((string) $f['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($f['type_category_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) $f['location'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $f['last_maintenance'] ? htmlspecialchars((string) $f['last_maintenance'], ENT_QUOTES, 'UTF-8') : 'Jamais' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($forecast)): ?>
        <tr><td colspan="4" class="bo-empty-row">Aucun équipement en retard de maintenance.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h3>Utilisateurs — retours en retard (revue manuelle)</h3>
  <p class="muted-note">Basé sur <code>returned_at &gt; end_date</code> (historique).</p>
  <div class="bo-table-wrap">
    <table class="transport-table bo-table">
      <thead>
        <tr><th>Utilisateur</th><th>Retards enregistrés</th></tr>
      </thead>
      <tbody>
      <?php foreach ($violations as $v): ?>
        <tr class="bo-row-warn">
          <td><?= htmlspecialchars((string) $v['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int) $v['late_count'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($violations)): ?>
        <tr><td colspan="2" class="bo-empty-row">Aucun profil signalé.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
