<?php
/** @var array $kpis */
/** @var array $chartTypes */
/** @var array $heatmap */
/** @var array $lateReturns */
$labels = array_column($chartTypes, 'category_name');
$counts = array_map('intval', array_column($chartTypes, 'request_count'));
$icons = array_column($chartTypes, 'icon');
$maxHits = 0;
foreach ($heatmap as $h) {
    $maxHits = max($maxHits, (int) $h['reservation_hits']);
}
?>
<header class="topbar topbar-bo bo-module-header">
  <div class="bo-topbar-main">
    <div class="bo-topbar-titles">
      <h1 class="bo-page-title">Dashboard</h1>
      <p class="bo-page-sub">Vue d’ensemble du parc, des réservations et des retards.</p>
    </div>
  </div>
</header>

<div class="stats-row">
  <div class="stat-card">
    <strong class="accent-teal"><?= (int) $kpis['total_equipment'] ?></strong>
    <span>Total équipements</span>
    <span class="trend trend-ok">Parc</span>
  </div>
  <div class="stat-card">
    <strong class="accent-blue"><?= (int) $kpis['reserved_today'] ?></strong>
    <span>Réservations actives aujourd’hui</span>
    <span class="trend trend-ok">Chevauchement date du jour</span>
  </div>
  <div class="stat-card">
    <strong class="accent-orange"><?= (int) $kpis['maintenance'] ?></strong>
    <span>En maintenance</span>
    <span class="trend trend-warn">Atelier</span>
  </div>
  <div class="stat-card">
    <strong class="accent-red"><?= (int) $kpis['pending'] ?></strong>
    <span>Réservations en attente</span>
    <span class="trend trend-warn">À valider</span>
  </div>
</div>

<div class="bo-dash-grid">
  <div class="card">
    <h3>Types les plus demandés</h3>
    <p class="muted-note bo-card-lead">Nombre total de réservations liées par type (toutes périodes).</p>
    <div class="bo-chart-wrap">
      <canvas id="chartTypes" height="220"></canvas>
    </div>
  </div>
  <div class="card">
    <h3>Activité par lieu (heatmap)</h3>
    <p class="muted-note bo-card-lead">Intensité = réservations sur 90 jours (échelle relative).</p>
    <div class="bo-heatmap">
      <?php foreach ($heatmap as $h): ?>
        <?php
        $hits = (int) $h['reservation_hits'];
        $pct = $maxHits > 0 ? round(100 * $hits / $maxHits) : 0;
        ?>
        <div class="bo-heatmap-row">
          <span class="bo-heatmap-label"><?= htmlspecialchars((string) $h['location'], ENT_QUOTES, 'UTF-8') ?></span>
          <div class="bo-heatmap-bar"><div class="bo-heatmap-fill" style="width:<?= $pct ?>%;opacity:<?= 0.35 + ($pct / 100) * 0.65 ?>"></div></div>
          <span class="bo-heatmap-val"><?= $hits ?> rés. · <?= (int) $h['equipment_count'] ?> eq.</span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($heatmap)): ?>
        <p class="muted-note">Aucune donnée de lieu.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <h3>Retours en retard</h3>
  <p class="muted-note bo-card-lead">Soit <code>end_date</code> dépassée alors que le statut est encore <code>approved</code>, soit <code>returned_at &gt; end_date</code>.</p>
  <div class="bo-table-wrap">
    <table class="transport-table bo-table">
      <thead>
        <tr><th>ID</th><th>Équipement</th><th>Utilisateur</th><th>Fin prévue</th><th>Statut</th></tr>
      </thead>
      <tbody>
      <?php foreach ($lateReturns as $lr): ?>
        <tr class="bo-row-warn">
          <td><?= (int) $lr['id'] ?></td>
          <td><?= htmlspecialchars((string) $lr['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($lr['user_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) $lr['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) $lr['status'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($lateReturns)): ?>
        <tr><td colspan="5" class="bo-empty-row">Aucun retard détecté.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('chartTypes');
  if (!ctx || typeof Chart === 'undefined') return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Réservations',
        data: <?= json_encode($counts) ?>,
        backgroundColor: 'rgba(26,115,232,0.65)',
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
  });
});
</script>
