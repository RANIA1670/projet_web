<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/equipment_bo/app/models/Reservation.php';

cityzen_require_citizen_login();

$pdo = cityzen_db();
$reservations = new Reservation($pdo);
$uid = cityzen_current_user_id();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['cancel_reservation'])) {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $err = 'Jeton invalide.';
    } else {
        $rid = (int) ($_POST['reservation_id'] ?? 0);
        if ($reservations->cancelByUser($rid, $uid)) {
            header('Location: ' . cityzen_asset('equipment/my-reservations.php?msg=' . rawurlencode('Réservation annulée.') . '&type=ok'), true, 302);
            exit;
        }
        $err = 'Annulation impossible.';
    }
}

$list = $reservations->listForUser($uid);

$msg = (string) ($_GET['msg'] ?? '');
$type = (string) ($_GET['type'] ?? '');

$eq_title = 'Mes réservations';
$eq_nav_active = 'my-reservations';
require __DIR__ . '/includes/shell_start.php';

$purposeLabels = [
    'event' => 'Événement',
    'repair' => 'Réparation',
    'inspection' => 'Inspection',
];

$statusLabels = [
    'pending' => 'En attente',
    'approved' => 'Approuvée',
    'rejected' => 'Refusée',
    'returned' => 'Terminée',
    'no_show' => 'Non présentation',
    'cancelled' => 'Annulée',
];
?>
  <h1 class="eq-page-title">Mes réservations</h1>
  <p class="eq-page-lead">Suivi de vos demandes et actions possibles tant que la réservation est future ou en attente.</p>

  <?php if ($msg !== ''): ?>
    <p class="eq-alert eq-alert--<?= $type === 'ok' ? 'ok' : 'err' ?>" role="status"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>
  <?php if (!empty($err)): ?>
    <p class="eq-alert eq-alert--err"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <div class="eq-table-wrap">
    <table class="eq-table">
      <thead>
        <tr>
          <th>Équipement</th>
          <th>Début</th>
          <th>Fin</th>
          <th>Motif</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $r): ?>
          <?php
            $st = (string) $r['status'];
            $rid = (int) $r['id'];
            $eqId = (int) $r['equipment_id'];
            $startTs = strtotime((string) $r['start_date']);
            $endTs = strtotime((string) $r['end_date']);
            $now = time();
            $canCancel = $st === 'pending'
                || ($st === 'approved' && $startTs > $now);
            $canExtend = $st === 'approved' && $endTs > $now;
            $up = (string) ($r['usage_purpose'] ?? '');
            $motif = $purposeLabels[$up] ?? ($r['purpose'] ?? '—');
            if ((int) ($r['extension_of_id'] ?? 0) > 0) {
                $motif = 'Prolongation';
            }
            ?>
          <tr>
            <td><?= htmlspecialchars((string) $r['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['start_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $r['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $motif, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <div class="eq-actions">
                <?php if ($canCancel): ?>
                  <form method="post" action="" style="display:inline" onsubmit="return confirm('Annuler cette réservation ?');">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="cancel_reservation" value="1">
                    <input type="hidden" name="reservation_id" value="<?= $rid ?>">
                    <button type="submit" class="eq-btn eq-btn--ghost" style="padding:6px 12px;font-size:0.8rem">Annuler</button>
                  </form>
                <?php endif; ?>
                <?php if ($canExtend): ?>
                  <a class="eq-btn eq-btn--ghost" style="padding:6px 12px;font-size:0.8rem" href="<?= htmlspecialchars(cityzen_asset('equipment/reserve.php?equipment_id=' . $eqId . '&extend_from=' . $rid), ENT_QUOTES, 'UTF-8') ?>">Prolonger</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($list === []): ?>
    <p class="eq-page-lead">Aucune réservation pour l’instant. <a href="<?= htmlspecialchars(cityzen_asset('equipment/index.php'), ENT_QUOTES, 'UTF-8') ?>">Voir le catalogue</a>.</p>
  <?php endif; ?>

<?php require __DIR__ . '/includes/shell_end.php'; ?>
