<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/equipment_bo/app/models/Equipment.php';
require_once dirname(__DIR__) . '/equipment_bo/app/models/Reservation.php';

$pdo = cityzen_db();
$equipment = new Equipment($pdo);
$reservations = new Reservation($pdo);

$eqId = (int) ($_GET['equipment_id'] ?? $_POST['equipment_id'] ?? 0);
$extendFrom = (int) ($_GET['extend_from'] ?? $_POST['extend_from'] ?? 0);

$row = $eqId > 0 ? $equipment->findWithType($eqId) : null;
if ($row === null) {
    header('Location: ' . cityzen_asset('equipment/index.php'), true, 302);
    exit;
}

$parent = null;
if ($extendFrom > 0) {
    cityzen_require_citizen_login();
    $parent = $reservations->find($extendFrom);
    if ($parent === null
        || (int) ($parent['user_id'] ?? 0) !== cityzen_current_user_id()
        || (int) ($parent['equipment_id'] ?? 0) !== $eqId
        || ($parent['status'] ?? '') !== 'approved'
    ) {
        header('Location: ' . cityzen_asset('equipment/my-reservations.php'), true, 302);
        exit;
    }
    if (strtotime((string) $parent['end_date']) <= time()) {
        header('Location: ' . cityzen_asset('equipment/my-reservations.php'), true, 302);
        exit;
    }
}

$flash = '';
$flashType = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['reserve_submit'])) {
    cityzen_require_citizen_login();
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $flash = 'Session expirée. Rechargez la page.';
        $flashType = 'err';
    } else {
        $uid = cityzen_current_user_id();
        $ext = (int) ($_POST['extend_from'] ?? 0);
        if ($ext > 0) {
            $newEndRaw = (string) ($_POST['end_date'] ?? '');
            $newId = $reservations->createExtensionRequest($ext, $uid, $newEndRaw);
            if ($newId === null) {
                $flash = 'Prolongation impossible (dates ou chevauchement).';
                $flashType = 'err';
            } else {
                header('Location: ' . cityzen_asset('equipment/my-reservations.php?msg=' . rawurlencode('Demande de prolongation envoyée. En attente de validation.') . '&type=ok'), true, 302);
                exit;
            }
        } else {
            $startRaw = (string) ($_POST['start_date'] ?? '');
            $endRaw = (string) ($_POST['end_date'] ?? '');
            $purpose = (string) ($_POST['usage_purpose'] ?? '');
            $allowedP = ['event', 'repair', 'inspection'];
            $stEq = (string) $row['status'];
            if ($stEq !== 'available') {
                $flash = 'Cet équipement n\'est pas réservable en ligne.';
                $flashType = 'err';
            } elseif (!in_array($purpose, $allowedP, true)) {
                $flash = 'Choisissez un motif.';
                $flashType = 'err';
            } else {
                $tsS = strtotime($startRaw);
                $tsE = strtotime($endRaw);
                if ($tsS === false || $tsE === false || $tsE <= $tsS || $tsS < time() - 120) {
                    $flash = 'Dates invalides.';
                    $flashType = 'err';
                } elseif ($reservations->hasOverlap($eqId, date('Y-m-d H:i:s', $tsS), date('Y-m-d H:i:s', $tsE), null)) {
                    $flash = 'Ce créneau chevauche une réservation existante.';
                    $flashType = 'err';
                } else {
                    $reservations->create([
                        'equipment_id'    => $eqId,
                        'user_id'         => $uid,
                        'extension_of_id' => null,
                        'start_date'      => date('Y-m-d H:i:s', $tsS),
                        'end_date'        => date('Y-m-d H:i:s', $tsE),
                        'purpose'         => null,
                        'usage_purpose'   => $purpose,
                        'status'          => 'pending',
                    ]);
                    header('Location: ' . cityzen_asset('equipment/reserve.php?equipment_id=' . $eqId . '&done=1'), true, 302);
                    exit;
                }
            }
        }
    }
}

$done = isset($_GET['done']);
$eq_title = 'Demande de réservation';
$eq_nav_active = 'equipment';
require __DIR__ . '/includes/shell_start.php';

$busyJson = json_encode($reservations->busyRangesForEquipment($eqId), JSON_UNESCAPED_UNICODE);
?>
  <p class="eq-page-lead"><a href="<?= htmlspecialchars(cityzen_asset('equipment/detail.php?id=' . $eqId), ENT_QUOTES, 'UTF-8') ?>">← Fiche équipement</a></p>
  <h1 class="eq-page-title"><?= $extendFrom > 0 ? 'Prolonger la réservation' : 'Demander une réservation' ?></h1>
  <p class="eq-page-lead"><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $row['type_category_name'], ENT_QUOTES, 'UTF-8') ?></p>

  <div class="eq-steps">
    <span class="eq-step is-done">1. Équipement</span>
    <span class="eq-step is-done">2. Dates</span>
    <span class="eq-step is-done">3. Motif</span>
    <span class="eq-step">4. Envoi</span>
  </div>

  <?php if ($done): ?>
    <p class="eq-alert eq-alert--ok" role="status">Demande enregistrée. <strong>En attente de validation par un agent.</strong></p>
    <p><a class="eq-btn eq-btn--ghost" href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php'), ENT_QUOTES, 'UTF-8') ?>">Voir mes réservations</a></p>
  <?php else: ?>
    <?php if ($flash !== ''): ?>
      <p class="eq-alert eq-alert--<?= $flashType === 'ok' ? 'ok' : 'err' ?>"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!cityzen_is_logged_in() || cityzen_current_user_id() <= 0): ?>
      <p class="eq-page-lead">
        <a class="eq-btn eq-btn--primary" href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset($_SERVER['REQUEST_URI'] ?? 'equipment/reserve.php?equipment_id=' . $eqId)), ENT_QUOTES, 'UTF-8') ?>">Connexion pour envoyer la demande</a>
        ou <a href="<?= htmlspecialchars(cityzen_asset('register.php'), ENT_QUOTES, 'UTF-8') ?>">créer un compte</a>.
      </p>
    <?php endif; ?>

    <p class="eq-page-lead">Les plages déjà prises ou en attente apparaissent en rouge ci-dessous.</p>
    <ul class="eq-busy-list" id="eq-busy-display" aria-live="polite"></ul>

    <form class="eq-form" method="post" action="" style="margin-top:18px" id="eq-reserve-form">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="reserve_submit" value="1">
      <input type="hidden" name="equipment_id" value="<?= $eqId ?>">
      <?php if ($extendFrom > 0): ?>
        <input type="hidden" name="extend_from" value="<?= $extendFrom ?>">
        <label>
          Fin de votre réservation actuelle
          <input type="text" value="<?= htmlspecialchars((string) $parent['end_date'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </label>
        <?php
          $minExt = date('Y-m-d\TH:i', strtotime((string) $parent['end_date']) + 60);
        ?>
        <label>
          Nouvelle date de fin
          <input type="datetime-local" name="end_date" required min="<?= htmlspecialchars($minExt, ENT_QUOTES, 'UTF-8') ?>">
        </label>
      <?php else: ?>
        <label>
          Début
          <input type="datetime-local" name="start_date" id="eq-start" required>
        </label>
        <label>
          Fin
          <input type="datetime-local" name="end_date" id="eq-end" required>
        </label>
        <label>
          Motif
          <select name="usage_purpose" required>
            <option value="">— Choisir —</option>
            <option value="event">Événement</option>
            <option value="repair">Réparation</option>
            <option value="inspection">Inspection</option>
          </select>
        </label>
      <?php endif; ?>
      <?php if (cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
        <button type="submit" class="eq-btn eq-btn--primary"><?= $extendFrom > 0 ? 'Envoyer la demande de prolongation' : 'Envoyer la demande' ?></button>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <script>
  (function () {
    var busy = <?= $busyJson ?>;
    var list = document.getElementById('eq-busy-display');
    if (list && busy.length) {
      busy.forEach(function (b) {
        var li = document.createElement('li');
        li.textContent = b.start.replace('T', ' ').substring(0, 16) + ' → ' + b.end.replace('T', ' ').substring(0, 16);
        list.appendChild(li);
      });
    } else if (list) {
      var li = document.createElement('li');
      li.textContent = 'Aucune plage bloquée pour le moment.';
      li.style.borderLeftColor = 'var(--line)';
      li.style.background = 'var(--surface-soft)';
      li.style.color = 'var(--muted)';
      list.appendChild(li);
    }
  })();
  </script>

<?php require __DIR__ . '/includes/shell_end.php'; ?>
