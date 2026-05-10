<?php
/** @var array $rows */
/** @var array $typeList */
/** @var array<int, array<int, array<string, mixed>>> $reservationsByEquipment */
?>
<header class="topbar topbar-bo bo-module-header">
  <div class="bo-topbar-titles">
    <h1 class="bo-page-title">Gérer les équipements</h1>
    <p class="bo-page-sub">Filtres, édition, création avec position GPS sur carte.</p>
  </div>
</header>

<form method="get" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="bo-filter-bar card compact">
  <input type="hidden" name="route" value="equipment">
  <div class="bo-filter-grid">
    <label>Type
      <select name="filter_type">
        <option value="">Tous</option>
        <?php foreach ($typeList as $t): ?>
          <option value="<?= (int) $t['id'] ?>" <?= (isset($_GET['filter_type']) && (int)$_GET['filter_type'] === (int)$t['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string) $t['category_name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Statut
      <select name="filter_status">
        <option value="">Tous</option>
        <?php foreach (['available','reserved','maintenance','out_of_service'] as $st): ?>
          <option value="<?= $st ?>" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Lieu (contient)
      <input type="text" name="filter_location" value="<?= htmlspecialchars((string) ($_GET['filter_location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex. Mairie">
    </label>
    <button type="submit" class="btn-bo btn-bo--accept">Filtrer</button>
    <a href="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="btn-bo btn-bo--decline" style="text-decoration:none;display:inline-flex;align-items:center;">Réinitialiser</a>
  </div>
</form>

<form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" id="bulk-form">
  <input type="hidden" name="route" value="equipment">
  <input type="hidden" name="_action" value="equipment_bulk_maintenance">
  <div class="bo-toolbar">
    <button type="button" class="btn-bo btn-bo--accept" id="btn-open-create">+ Nouvel équipement</button>
    <button type="submit" class="btn-bo" style="background:var(--orange);color:#fff;" onclick="return document.querySelectorAll('input[name=\'equipment_ids[]\']:checked').length>0;">Maintenance groupée (sélection)</button>
  </div>

  <div class="card">
    <div class="bo-table-wrap">
      <table class="transport-table bo-table bo-equipment-table">
        <thead>
          <tr class="bo-eq-main-row">
            <th><input type="checkbox" id="check-all" title="Tout"></th>
            <th>Nom</th><th>Type</th><th>Prix / jour</th><th>Lieu</th><th>Statut</th><th>Dernière m.</th><th>GPS</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $eq): ?>
          <?php
          $eqReservations = $reservationsByEquipment[(int) $eq['id']] ?? [];
          ?>
          <tr>
            <td><input type="checkbox" name="equipment_ids[]" value="<?= (int) $eq['id'] ?>"></td>
            <td><?= htmlspecialchars((string) $eq['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($eq['type_icon'] ?? '') . ' ' . ($eq['type_category_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= number_format((float) ($eq['price_per_day'] ?? 0), 2, ',', ' ') ?> TND</td>
            <td class="bo-td-notes"><?= htmlspecialchars((string) $eq['location'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="bo-badge"><?= htmlspecialchars((string) $eq['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?= $eq['last_maintenance'] ? htmlspecialchars((string) $eq['last_maintenance'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td><?= ($eq['latitude'] !== null && $eq['longitude'] !== null) ? '✓' : '—' ?></td>
            <td><button type="button" class="btn-bo btn-bo--decline btn-edit" data-row="<?= htmlspecialchars(json_encode([
              'id' => (int) $eq['id'],
              'name' => $eq['name'],
              'status' => $eq['status'],
              'location' => $eq['location'],
              'type_id' => (int) $eq['type_id'],
              'price_per_day' => (float) ($eq['price_per_day'] ?? 0),
              'last_maintenance' => $eq['last_maintenance'] ?? '',
              'latitude' => $eq['latitude'],
              'longitude' => $eq['longitude'],
            ], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">Modifier</button></td>
          </tr>
          <tr class="bo-eq-reservation-row">
            <td colspan="9">
              <div class="bo-eq-reservation-box">
                <div class="bo-eq-reservation-head">
                  <strong>Réservations liées à cet équipement</strong>
                  <span class="bo-eq-reservation-count"><?= count($eqReservations) ?> total</span>
                </div>
                <?php if ($eqReservations === []): ?>
                  <p class="muted-note">Aucune réservation pour cet équipement.</p>
                <?php else: ?>
                  <div class="bo-table-wrap">
                    <table class="transport-table bo-table bo-table--nested">
                      <thead>
                        <tr>
                          <th>Utilisateur</th>
                          <th>Période</th>
                          <th>Objet</th>
                          <th>Montant</th>
                          <th>Statut</th>
                          <th class="bo-th-actions">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($eqReservations as $r): ?>
                        <?php
                        $status = (string) ($r['status'] ?? '');
                        $badgeClass = 'bo-badge bo-badge--muted';
                        if ($status === 'pending') {
                            $badgeClass = 'bo-badge bo-badge--pending';
                        } elseif ($status === 'approved' || $status === 'returned') {
                            $badgeClass = 'bo-badge bo-badge--ok';
                        } elseif ($status === 'rejected' || $status === 'no_show' || $status === 'cancelled') {
                            $badgeClass = 'bo-badge bo-badge--no';
                        }
                        ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($r['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                          <td>
                            <?= htmlspecialchars((string) ($r['start_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            →
                            <?= htmlspecialchars((string) ($r['end_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                          </td>
                          <td class="bo-td-notes"><?= htmlspecialchars((string) ($r['purpose'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                          <td><?= number_format((float) ($r['price_total'] ?? 0), 2, ',', ' ') ?> TND</td>
                          <td>
                            <span class="<?= $badgeClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($r['rejection_reason'])): ?>
                              <div class="bo-rejection-note"><?= htmlspecialchars((string) $r['rejection_reason'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="bo-td-actions">
                            <?php if ($status === 'pending'): ?>
                              <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form">
                                <input type="hidden" name="route" value="equipment">
                                <input type="hidden" name="_action" value="reservation_approve">
                                <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                <label class="bo-inline-check"><input type="checkbox" name="send_notification" value="1"> E-mail</label>
                                <button type="submit" class="btn-bo btn-bo--accept">Approuver</button>
                              </form>
                              <button type="button" class="btn-bo btn-bo--decline btn-open-reject" data-id="<?= (int) $r['id'] ?>">Refuser</button>
                            <?php elseif ($status === 'approved'): ?>
                              <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form" onsubmit="return confirm('Confirmer le retour du matériel ?');">
                                <input type="hidden" name="route" value="equipment">
                                <input type="hidden" name="_action" value="reservation_return">
                                <input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn-bo btn-bo--accept">Retour reçu</button>
                              </form>
                              <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="bo-inline-form" onsubmit="return confirm('Marquer comme no-show ?');">
                                <input type="hidden" name="route" value="equipment">
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
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</form>

<div class="bo-modal" id="modal-equipment" aria-hidden="true">
  <div class="bo-modal-backdrop" data-close="1"></div>
  <div class="bo-modal-panel card">
    <h3 id="modal-title">Équipement</h3>
    <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" id="form-equipment">
      <input type="hidden" name="route" value="equipment">
      <input type="hidden" name="_action" value="equipment_save">
      <input type="hidden" name="id" id="eq-id" value="">
      <div class="form-grid">
        <div class="form-group full">
          <label>Nom *</label>
          <input type="text" name="name" id="eq-name" required maxlength="150">
        </div>
        <div class="form-group">
          <label>Type *</label>
          <select name="type_id" id="eq-type" required>
            <?php foreach ($typeList as $t): ?>
              <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['category_name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Statut</label>
          <select name="status" id="eq-status">
            <?php foreach (['available','reserved','maintenance','out_of_service'] as $st): ?>
              <option value="<?= $st ?>"><?= $st ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Prix par jour (TND)</label>
          <input type="number" step="0.01" min="0" name="price_per_day" id="eq-price-day" value="0">
        </div>
        <div class="form-group full">
          <label>Lieu</label>
          <input type="text" name="location" id="eq-location" maxlength="255">
        </div>
        <div class="form-group">
          <label>Dernière maintenance</label>
          <input type="date" name="last_maintenance" id="eq-lm">
        </div>
        <div class="form-group">
          <label>Latitude</label>
          <input type="text" name="latitude" id="eq-lat" placeholder="clic carte">
        </div>
        <div class="form-group">
          <label>Longitude</label>
          <input type="text" name="longitude" id="eq-lng" placeholder="clic carte">
        </div>
      </div>
      <p class="muted-note">Cliquez sur la carte pour renseigner GPS (Tunis par défaut).</p>
      <div id="map-picker" class="bo-map-picker"></div>
      <div class="bo-modal-actions">
        <button type="button" class="btn-bo btn-bo--decline" data-close="1">Annuler</button>
        <button type="submit" class="btn-bo btn-bo--accept">Enregistrer</button>
      </div>
    </form>
    <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" id="form-equipment-delete" class="bo-delete-form" onsubmit="return confirm('Supprimer cet équipement ?');">
      <input type="hidden" name="route" value="equipment">
      <input type="hidden" name="_action" value="equipment_delete">
      <input type="hidden" name="id" id="eq-delete-id" value="">
      <button type="submit" class="btn-bo" style="background:var(--red);color:#fff;margin-top:10px;">Supprimer</button>
    </form>
  </div>
</div>

<div class="bo-modal" id="modal-reject" aria-hidden="true">
  <div class="bo-modal-backdrop" data-close="1"></div>
  <div class="bo-modal-panel card">
    <h3>Refuser la demande</h3>
    <form method="post" action="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="route" value="equipment">
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
