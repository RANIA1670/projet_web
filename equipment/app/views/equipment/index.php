<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/includes/shell_start.php';
?>
  <h1 class="eq-page-title">Équipement disponible</h1>
  <p class="eq-page-lead">Consultez le matériel municipal, les lieux et demandez une réservation en ligne. Les demandes sont validées par un agent.</p>

  <form class="eq-toolbar" method="get" action="">
    <label>
      Type
      <select name="type" onchange="this.form.submit()">
        <option value="">Tous les types</option>
        <?php foreach ($typeList as $t): ?>
          <option value="<?= (int) $t['id'] ?>" <?= $typeId === (int) $t['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string) $t['icon'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) $t['category_name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>

  <div class="eq-cards">
    <?php foreach ($rows as $row): ?>
      <?php
        $st = (string) $row['status'];
        $badgeClass = match ($st) {
            'available' => 'eq-badge--available',
            'reserved' => 'eq-badge--reserved',
            'maintenance' => 'eq-badge--maintenance',
            default => 'eq-badge--out',
        };
        $stLabel = match ($st) {
            'available' => 'Disponible',
            'reserved' => 'En utilisation',
            'maintenance' => 'Maintenance',
            'out_of_service' => 'Hors service',
            default => $st,
        };
        $nextBlock = $reservations->latestBlockingEnd((int) $row['id']);
        ?>
      <article class="eq-card">
        <div class="eq-card-icon"><?= htmlspecialchars((string) ($row['type_icon'] ?? '📦'), ENT_QUOTES, 'UTF-8') ?></div>
        <h2><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="eq-card-meta"><?= htmlspecialchars((string) $row['type_category_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="eq-card-meta"><?= htmlspecialchars((string) $row['location'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="eq-card-meta"><strong><?= number_format((float) (($row['price_per_day'] ?? 0) > 0 ? $row['price_per_day'] : ($row['type_daily_cost'] ?? 0)), 2, ',', ' ') ?> TND / jour</strong></p>
        <span class="eq-badge <?= $badgeClass ?>"><?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?></span>
        <?php if ($nextBlock !== null && $st === 'available'): ?>
          <p class="eq-card-meta"><strong>Prochain créneau libre :</strong> après le <?= htmlspecialchars($nextBlock, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif ($nextBlock === null && $st === 'available'): ?>
          <p class="eq-card-meta"><strong>Prochain créneau libre :</strong> dès maintenant (selon calendrier)</p>
        <?php endif; ?>
        <a class="eq-btn eq-btn--ghost" href="<?= htmlspecialchars(cityzen_asset('equipment/detail.php?id=' . (int) $row['id']), ENT_QUOTES, 'UTF-8') ?>">Détails</a>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($rows === []): ?>
    <p class="eq-page-lead">Aucun équipement ne correspond à ce filtre.</p>
  <?php endif; ?>

<?php require dirname(__DIR__, 3) . '/includes/shell_end.php'; ?>
