<?php
/** @var array $rows */
/** @var ?array $deleteRow */
/** @var int $deleteCount */
$deleteRow = $deleteRow ?? null;
$deleteCount = $deleteCount ?? 0;
?>
<header class="topbar topbar-bo bo-module-header">
  <div class="bo-topbar-titles">
    <h1 class="bo-page-title">Types d’équipement</h1>
    <p class="bo-page-sub">Coût journalier, icône, fréquence de maintenance par défaut. Suppression avec réassignation si besoin.</p>
  </div>
</header>

<?php if ($deleteRow && $deleteCount === 0): ?>
<div class="card bo-alert-warn">
  <p>Supprimer le type « <?= htmlspecialchars((string) $deleteRow['category_name'], ENT_QUOTES, 'UTF-8') ?> » ?</p>
  <form method="post" action="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>" style="display:inline;">
    <input type="hidden" name="route" value="types">
    <input type="hidden" name="_action" value="type_delete">
    <input type="hidden" name="id" value="<?= (int) $deleteRow['id'] ?>">
    <button type="submit" class="btn-bo" style="background:var(--red);color:#fff;">Confirmer suppression</button>
  </form>
  <a href="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
</div>
<?php endif; ?>

<?php if ($deleteRow && $deleteCount > 0): ?>
<div class="card bo-alert-warn">
  <strong><?= (int) $deleteCount ?> équipement(s)</strong> utilisent le type « <?= htmlspecialchars((string) $deleteRow['category_name'], ENT_QUOTES, 'UTF-8') ?> ».
  Choisissez un type de remplacement puis supprimez :
  <form method="post" action="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <input type="hidden" name="route" value="types">
    <input type="hidden" name="_action" value="type_delete">
    <input type="hidden" name="id" value="<?= (int) $deleteRow['id'] ?>">
    <label>Réassigner vers
      <select name="reassign_to" required>
        <option value="">—</option>
        <?php foreach ($rows as $t): ?>
          <?php if ((int) $t['id'] === (int) $deleteRow['id']) continue; ?>
          <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['category_name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit" class="btn-bo" style="background:var(--red);color:#fff;">Supprimer et réassigner</button>
  </form>
  <a href="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>" class="muted-note">Annuler</a>
</div>
<?php endif; ?>

<div class="bo-toolbar">
  <button type="button" class="btn-bo btn-bo--accept" id="btn-type-new">+ Nouveau type</button>
</div>

<div class="card">
  <div class="bo-table-wrap">
    <table class="transport-table bo-table">
      <thead>
        <tr><th>Icône</th><th>Catégorie</th><th>€ / jour</th><th>Garantie (mois)</th><th>Maintenance défaut (mois)</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $t): ?>
        <tr>
          <td class="bo-td-icon"><?= htmlspecialchars((string) $t['icon'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) $t['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= number_format((float) $t['daily_cost'], 2, ',', ' ') ?></td>
          <td><?= (int) $t['warranty_months'] ?></td>
          <td><?= (int) $t['default_maintenance_frequency_months'] ?></td>
          <td>
            <button type="button" class="btn-bo btn-bo--decline btn-type-edit" data-row="<?= htmlspecialchars(json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">Modifier</button>
            <a href="<?= htmlspecialchars(bo_url('types', ['delete_type' => (string) $t['id']]), ENT_QUOTES, 'UTF-8') ?>" class="btn-bo" style="background:var(--red);color:#fff;text-decoration:none;padding:7px 14px;border-radius:8px;font-size:.78rem;">Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="bo-modal" id="modal-type" aria-hidden="true">
  <div class="bo-modal-backdrop" data-close="1"></div>
  <div class="bo-modal-panel card">
    <h3 id="type-modal-title">Type</h3>
    <form method="post" action="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="route" value="types">
      <input type="hidden" name="_action" value="type_save">
      <input type="hidden" name="id" id="type-id" value="">
      <div class="form-grid">
        <div class="form-group full">
          <label>Catégorie *</label>
          <input type="text" name="category_name" id="type-name" required maxlength="150">
        </div>
        <div class="form-group">
          <label>Icône (emoji ou texte)</label>
          <input type="text" name="icon" id="type-icon" maxlength="64">
        </div>
        <div class="form-group">
          <label>Coût journalier</label>
          <input type="number" step="0.01" name="daily_cost" id="type-cost" value="0">
        </div>
        <div class="form-group">
          <label>Garantie (mois)</label>
          <input type="number" name="warranty_months" id="type-war" value="12" min="1">
        </div>
        <div class="form-group full">
          <label>Fréquence maintenance par défaut (mois)</label>
          <input type="number" name="default_maintenance_frequency_months" id="type-maint" value="6" min="1">
        </div>
      </div>
      <div class="bo-modal-actions">
        <button type="button" class="btn-bo btn-bo--decline" data-close="1">Annuler</button>
        <button type="submit" class="btn-bo btn-bo--accept">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
