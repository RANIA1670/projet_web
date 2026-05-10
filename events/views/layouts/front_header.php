<?php
declare(strict_types=1);

/**
 * En-tête front module événements : barre CityZen + sous-nav du module (sans second document HTML complet).
 *
 * Variables attendues :
 * @var ?string $titrePage titre onglet et document
 */

$page ??= isset($_GET['page']) ? (string) $_GET['page'] : 'accueil';

$docTitle = isset($titrePage) ? (string) $titrePage : 'Événements';
$evtBase = htmlspecialchars(cityzen_asset('events/index.php'), ENT_QUOTES, 'UTF-8');

$fontsMontserrat = 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap';
$eventsCss = cityzen_asset('events/public/style.css') . '?v=cz-scope-2';

if (empty($GLOBALS['_events_cityzen_head_sent'])) {
    $GLOBALS['_events_cityzen_head_sent'] = true;
    cityzen_render_head($docTitle, [$fontsMontserrat, $eventsCss]);
?>
<div class="site-shell site-shell-events">
<?php include __DIR__ . '/cityzen_topbar.php'; ?>

<nav class="events-subnav cz-events-subnav" aria-label="Navigation gestion événements">
    <a href="<?= $evtBase ?>" <?= (($page ?? '') === 'accueil') ? 'class="actif"' : '' ?>>Accueil</a>
    <a href="<?= $evtBase ?>?page=front_event_liste" <?= str_contains($page ?? '', 'front_event_liste') ? 'class="actif"' : '' ?>>Événements</a>
    <a href="<?= $evtBase ?>?page=front_calendrier" <?= (($page ?? '') === 'front_calendrier') ? 'class="actif"' : '' ?>>Calendrier</a>
    <a href="<?= $evtBase ?>?page=front_sponsor_liste" <?= (($page ?? '') === 'front_sponsor_liste') ? 'class="actif"' : '' ?>>Sponsors</a>
    <?php if (cityzen_is_agent()): ?>
    <a href="<?= $evtBase ?>?page=back_dashboard" <?= str_starts_with($page ?? '', 'back_') ? 'class="actif"' : '' ?>>⚙ Administration</a>
    <?php endif; ?>
</nav>

<main class="page public-page events-module-body cz-events-scope">
<div class="events-module-wrap events-front">
<?php
}
