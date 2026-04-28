<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/includes/shell_start.php';
?>
  <p class="eq-page-lead"><a href="<?= htmlspecialchars(cityzen_asset('equipment/index.php'), ENT_QUOTES, 'UTF-8') ?>">← Catalogue</a></p>
  <h1 class="eq-page-title"><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="eq-page-lead">
    <span class="eq-badge <?= $badgeClass ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span>
    &nbsp;<?= htmlspecialchars((string) $row['type_category_name'], ENT_QUOTES, 'UTF-8') ?>
  </p>

  <div class="eq-detail-grid">
    <div>
      <?php if ($hasMap): ?>
        <div id="eq-map" class="eq-map" role="img" aria-label="Carte du lieu"></div>
      <?php else: ?>
        <p class="eq-page-lead">Pas de coordonnées GPS pour cet équipement.</p>
      <?php endif; ?>
    </div>
    <div>
      <dl class="eq-detail-dl">
        <dt>Lieu</dt>
        <dd><?= htmlspecialchars((string) $row['location'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Prix / jour</dt>
        <dd><?= number_format((float) (($row['price_per_day'] ?? 0) > 0 ? $row['price_per_day'] : ($row['type_daily_cost'] ?? 0)), 2, ',', ' ') ?> TND</dd>
        <?php if ($hasMap): ?>
          <dt>GPS</dt>
          <dd><?= htmlspecialchars((string) $lat, ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) $lng, ENT_QUOTES, 'UTF-8') ?></dd>
        <?php endif; ?>
        <dt>Créneaux déjà réservés ou en attente</dt>
        <dd>
          <?php if ($busy === []): ?>
            Aucune réservation en cours sur le calendrier.
          <?php else: ?>
            <ul class="eq-busy-list">
              <?php foreach ($busy as $b): ?>
                <li><?= htmlspecialchars($b['start'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($b['end'], ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </dd>
        <dt>Prochaine disponibilité indicative</dt>
        <dd>
          <?php if ($nextEnd !== null): ?>
            Après le <strong><?= htmlspecialchars($nextEnd, ENT_QUOTES, 'UTF-8') ?></strong> (sous réserve de validation).
          <?php else: ?>
            Aucun blocage actif — vous pouvez proposer des dates.
          <?php endif; ?>
        </dd>
      </dl>

      <?php if ($canRequest): ?>
        <p style="margin-top:18px">
          <a class="eq-btn eq-btn--primary" href="<?= htmlspecialchars(cityzen_asset('equipment/reserve.php?equipment_id=' . $id), ENT_QUOTES, 'UTF-8') ?>">Demander une réservation</a>
        </p>
      <?php else: ?>
        <p class="eq-page-lead">Réservation en ligne indisponible pour le moment (statut équipement).</p>
      <?php endif; ?>

      <section style="margin-top:28px;padding-top:20px;border-top:1px solid var(--line)">
        <h2 class="eq-page-title" style="font-size:1.15rem">Signaler une panne</h2>
        <?php if (!cityzen_is_logged_in() || cityzen_current_user_id() <= 0): ?>
          <p class="eq-page-lead"><a href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset('equipment/detail.php?id=' . $id)), ENT_QUOTES, 'UTF-8') ?>">Connectez-vous</a> pour envoyer un signalement avec photo.</p>
        <?php else: ?>
          <?php if ($issueFlash !== ''): ?>
            <p class="eq-alert eq-alert--<?= $issueFlashType === 'ok' ? 'ok' : 'err' ?>" role="status"><?= htmlspecialchars($issueFlash, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
          <form class="eq-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="report_issue" value="1">
            <label>
              Type de problème
              <select name="issue_type" required>
                <option value="not_working">Ne fonctionne pas</option>
                <option value="damaged">Endommagé</option>
                <option value="lost">Perdu / introuvable</option>
              </select>
            </label>
            <label>
              Photo (optionnel, max 2 Mo)
              <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            </label>
            <label>
              Description
              <textarea name="description" required maxlength="4000" placeholder="Décrivez le problème…"></textarea>
            </label>
            <button type="submit" class="eq-btn eq-btn--primary">Envoyer le signalement</button>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </div>

  <?php if ($hasMap): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
      (function () {
        var lat = <?= json_encode((float) $lat) ?>;
        var lng = <?= json_encode((float) $lng) ?>;
        var map = L.map('eq-map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        L.marker([lat, lng]).addTo(map);
      })();
    </script>
  <?php endif; ?>

<?php require dirname(__DIR__, 3) . '/includes/shell_end.php'; ?>
