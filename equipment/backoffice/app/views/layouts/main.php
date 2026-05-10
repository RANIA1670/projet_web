<?php
/** @var string $content */
/** @var string $activeRoute */

global $cityzen;
if (!isset($cityzen) || !is_array($cityzen)) {
    $cityzen = ['admin_menu' => []];
}

if (!isset($message)) {
    $message = (string) ($_GET['msg'] ?? '');
}
if (!isset($messageType)) {
    $messageType = (string) ($_GET['type'] ?? '');
}

$eqCss = PUBLIC_WEB_PATH . '/css/equipment.css?v=7';
$leafletCss = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';

cityzen_render_head('Back-office équipement', [$eqCss, $leafletCss], 'bo-body');
?>
<div class="admin-layout admin-layout-equipment">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php
        $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url'];
        $isActive = ($item['key'] ?? '') === 'equipment';
        ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $isActive ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-page admin-page--equipment">
    <div class="equipment-bo-root">
      <div class="app-shell">
        <aside class="left-sidebar bo-sidebar">
          <div class="sidebar-brand">City<strong>Zen</strong></div>
          <p class="bo-sidebar-tag">Équipement</p>
          <nav class="sidebar-nav bo-mod-nav">
            <span class="bo-nav-label">Modules</span>
            <a href="<?= htmlspecialchars(bo_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="<?= ($activeRoute ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= htmlspecialchars(bo_url('equipment'), ENT_QUOTES, 'UTF-8') ?>" class="<?= ($activeRoute ?? '') === 'equipment' ? 'active' : '' ?>">Équipement (CRUD)</a>
            <a href="<?= htmlspecialchars(bo_url('types'), ENT_QUOTES, 'UTF-8') ?>" class="<?= ($activeRoute ?? '') === 'types' ? 'active' : '' ?>">Types</a>
            <a href="<?= htmlspecialchars(bo_url('reports'), ENT_QUOTES, 'UTF-8') ?>" class="<?= ($activeRoute ?? '') === 'reports' ? 'active' : '' ?>">Rapports</a>
          </nav>
        </aside>
        <div class="content-area bo-content">
          <div class="page bo-page">
            <?= $content ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<div class="toast" id="toast" data-message="<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>" data-type="<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= htmlspecialchars(PUBLIC_WEB_PATH, ENT_QUOTES, 'UTF-8') ?>/js/equipment.js?v=4"></script>
<script src="<?= htmlspecialchars(PUBLIC_WEB_PATH, ENT_QUOTES, 'UTF-8') ?>/js/backoffice.js?v=1"></script>
<?php cityzen_render_footer(); ?>
