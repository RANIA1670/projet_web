<?php
/** @var array $pending */
/** @var array $history */
$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'pending';
$initialTab = $tab === 'history' ? 'tab-history' : 'tab-pending';
?>
<header class="topbar topbar-bo bo-module-header">
  <div class="bo-topbar-titles">
    <h1 class="bo-page-title">Réservations</h1>
    <p class="bo-page-sub">Validation, historique, retour matériel et no-show.</p>
  </div>
</header>

<div class="tabs tabs-bo" id="res-tabs" data-initial-tab="<?= htmlspecialchars($initialTab, ENT_QUOTES, 'UTF-8') ?>">
  <button type="button" class="tab-btn" data-tab="tab-pending">En attente (priorité)</button>
  <button type="button" class="tab-btn" data-tab="tab-history">Actives &amp; historique</button>
</div>

<div class="tab-pane" id="tab-pending">
  <div class="card">
    <h3>Approbations</h3>
    <div class="bo-table-wrap">
      <table class="transport-table bo-table">
        <thead>
          <tr>
            <th>ID</th><th>Utilisateur</th><th>Équipement</th><th>Début</th><th>Fin</th><th>Objet</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pending as $r): ?>
          <tr class="bo-row-pending">
            <td><?= (int) $r['id'] ?></td>
            <td><?= htmlspecialchars((string) ($r['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['start_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="bo-td-notes"><?= htmlspecialchars((string) ($r['purpose'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td class="bo-td-actions">
              <form method="post" action="<?= htmlspecialchars(bo_url('reservations'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form">
                <input type="hidden" name="route" value="reservations">
                <input type="hidden" name="_action" value="reservation_approve">
                <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                <label class="bo-inline-check"><input type="checkbox" name="send_notification" value="1"> E-mail</label>
                <button type="submit" class="btn-bo btn-bo--accept">Approuver</button>
              </form>
              <button type="button" class="btn-bo btn-bo--decline btn-open-reject" data-id="<?= (int) $r['id'] ?>">Refuser</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($pending)): ?>
          <tr><td colspan="7" class="bo-empty-row">Aucune demande en attente.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="tab-pane" id="tab-history">
  <div class="card">
    <h3>Actives &amp; passées</h3>
    <div class="bo-table-wrap">
      <table class="transport-table bo-table">
        <thead>
          <tr>
            <th>ID</th><th>Utilisateur</th><th>Équipement</th><th>Période</th><th>Statut</th><th>Motif refus</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $r): ?>
          <tr>
            <td><?= (int) $r['id'] ?></td>
            <td><?= htmlspecialchars((string) ($r['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['start_date'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars((string) $r['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="bo-td-notes"><?= htmlspecialchars((string) ($r['rejection_reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td class="bo-td-actions">
              <?php if (($r['status'] ?? '') === 'approved'): ?>
                <form method="post" action="<?= htmlspecialchars(bo_url('reservations'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form" onsubmit="return confirm('Confirmer le retour du matériel ?');">
                  <input type="hidden" name="route" value="reservations">
                  <input type="hidden" name="_action" value="reservation_return">
                  <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                  <button type="submit" class="btn-bo btn-bo--accept">Retour reçu</button>
                </form>
                <form method="post" action="<?= htmlspecialchars(bo_url('reservations'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form" onsubmit="return confirm('Marquer comme no-show ?');">
                  <input type="hidden" name="route" value="reservations">
                  <input type="hidden" name="_action" value="reservation_noshow">
                  <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                  <button type="submit" class="btn-bo btn-bo--decline">No-show</button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="bo-modal" id="modal-reject" aria-hidden="true">
  <div class="bo-modal-backdrop" data-close="1"></div>
  <div class="bo-modal-panel card">
    <h3>Refuser la demande</h3>
    <form method="post" action="<?= htmlspecialchars(bo_url('reservations'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="route" value="reservations">
      <input type="hidden" name="_action" value="reservation_reject">
      <input type="hidden" name="reservation_id" id="reject-id" value="">
      <div class="form-group full">
        <label>Motif obligatoire *</label>
        <textarea name="rejection_reason" required rows="4" maxlength="2000"></textarea>
      </div>
      <div class="bo-modal-actions">
        <button type="button" class="btn-bo btn-bo--decline" data-close="1">Annuler</button>
        <button type="submit" class="btn-bo" style="background:var(--red);color:#fff;">Refuser</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var tabs = document.getElementById('res-tabs');
  if (!tabs) return;
  var initial = tabs.dataset.initialTab || 'tab-pending';
  function act(id) {
    tabs.querySelectorAll('.tab-btn').forEach(function(b) {
      b.classList.toggle('active', b.dataset.tab === id);
    });
    document.querySelectorAll('#tab-pending,#tab-history').forEach(function(p) {
      p.classList.toggle('active', p.id === id);
    });
  }
  act(initial);
  tabs.querySelectorAll('.tab-btn').forEach(function(b) {
    b.addEventListener('click', function() { act(b.dataset.tab); });
  });
});
</script>
