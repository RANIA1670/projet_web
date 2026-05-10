<?php
declare(strict_types=1);

$page = isset($_GET['page']) ? (string) $_GET['page'] : 'back_dashboard';

$docTitle = (isset($titrePage) && is_string($titrePage) && $titrePage !== '')
    ? $titrePage
    : 'Admin événements';

$eb = htmlspecialchars(cityzen_asset('events/index.php'), ENT_QUOTES, 'UTF-8');

if (empty($GLOBALS['_events_bo_shell_open'])) {
    $GLOBALS['_events_bo_shell_open'] = true;

    $fontsMontserrat = 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap';
    $eventsCss = cityzen_asset('events/public/style.css') . '?v=cz-scope-2';
    cityzen_render_head($docTitle, [$fontsMontserrat, $eventsCss]);
?>
<div class="site-shell site-shell-events-bo">
<?php include __DIR__ . '/cityzen_topbar.php'; ?>

<main class="page public-page events-bo-stage cz-events-scope">
<div class="events-bo-toplinks">
  <a href="<?= htmlspecialchars(cityzen_asset('index.php'), ENT_QUOTES, 'UTF-8') ?>">← Portail CityZen</a>
  <a href="<?= $eb ?>">Accueil événements (public)</a>
  <a href="<?= htmlspecialchars(cityzen_asset('controller/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Tableau de bord agents</a>
</div>

<div class="admin-layout">

    <div class="sidebar">
        <div class="sidebar-title">Principal</div>
        <a href="<?= $eb ?>?page=back_dashboard"
           <?= $page === 'back_dashboard' ? 'class="actif"' : '' ?>>🏠 Dashboard</a>

        <div class="sidebar-title">Gestion</div>
        <a href="<?= $eb ?>?page=back_event_liste"
           <?= str_contains($page, 'back_event') ? 'class="actif"' : '' ?>>🎫 Événements</a>
        <a href="<?= $eb ?>?page=back_sponsor_liste"
           <?= str_contains($page, 'back_sponsor') ? 'class="actif"' : '' ?>>💼 Sponsors</a>
        <a href="<?= $eb ?>?page=back_participation_liste"
           <?= str_contains($page, 'back_participation') ? 'class="actif"' : '' ?>>👥 Participations</a>
        <a href="<?= $eb ?>?page=back_avis_liste"
           <?= str_contains($page, 'back_avis') ? 'class="actif"' : '' ?>>⭐ Avis & Notations</a>
    </div>

    <div class="main-admin">

<?php } ?>
