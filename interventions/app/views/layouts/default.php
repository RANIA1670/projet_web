<?php
declare(strict_types=1);

/**
 * Aligné sur le module Événements : cityzen_render_head → site-shell → topbar citoyenne
 * → sous-navigation module → main.page → footer CityZen (pas une page séparée).
 */

if (!function_exists('cityzen_render_head')) {
    require_once dirname(__DIR__, 4) . '/core/layout.php';
}

$pageTitle = isset($pageTitle) ? (string) $pageTitle : 'Interventions et signalements';

$iPathRaw = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
$baseSegment = strtolower((string) (parse_url(rtrim(APP_URL, '/'), PHP_URL_PATH) ?: ''));
if ($baseSegment !== '' && str_starts_with($iPathRaw, $baseSegment)) {
    $iPathRaw = substr($iPathRaw, strlen($baseSegment)) ?: '/';
}
$iPathNorm = '/' . trim($iPathRaw, '/');

$h = static function (string $path): string {
    $path = '/' . ltrim($path, '/');
    if ($path === '//') {
        $path = '/';
    }

    return htmlspecialchars(rtrim(APP_URL, '/') . $path, ENT_QUOTES, 'UTF-8');
};

$sAccueil = ($iPathNorm === '/' || $iPathNorm === '/index.php' || str_ends_with($iPathNorm, '/index.php'));
$sCreer = str_starts_with($iPathNorm, '/signalement/creer');
$sListeSignalements = str_starts_with($iPathNorm, '/signalements')
    || (str_starts_with($iPathNorm, '/signalement/') && !$sCreer);
$sInterventions = ($iPathNorm === '/interventions');
$sSuivi = str_starts_with($iPathNorm, '/suivi');
$sContact = str_starts_with($iPathNorm, '/contact');
$sNotif = str_starts_with($iPathNorm, '/notifications');
$sCarte = str_starts_with($iPathNorm, '/carte');
$sBo = str_starts_with($iPathNorm, '/backoffice');

$flash = $flash ?? null;

$assetBase = rtrim(APP_URL, '/');
cityzen_render_head($pageTitle, [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;600&display=swap',
    $assetBase . '/public/cityzen_shell.css?v=cz-shell-3',
    $assetBase . '/public/assets/css/main.css?v=cz-mod-2',
    $assetBase . '/public/assets/css/copilot.css',
    $assetBase . '/public/cityzen_body_reset.css?v=1',
], 'interventions-module-public');

?>
<div class="site-shell site-shell-interventions">
<?php
$cityzen_nav_active = 'interventions_gestion';
include dirname(__DIR__, 4) . '/events/views/layouts/cityzen_topbar.php';
?>

<nav class="interventions-subnav cz-interventions-subnav" aria-label="Navigation du module interventions">
    <a href="<?= $h('/') ?>" class="<?= $sAccueil ? 'actif' : '' ?>">Accueil</a>
    <a href="<?= $h('/signalements') ?>" class="<?= $sListeSignalements ? 'actif' : '' ?>">Signalements</a>
    <a href="<?= $h('/interventions') ?>" class="<?= $sInterventions ? 'actif' : '' ?>">Interventions</a>
    <a href="<?= $h('/suivi') ?>" class="<?= $sSuivi ? 'actif' : '' ?>">Suivi</a>
    <a href="<?= $h('/carte') ?>" class="<?= $sCarte ? 'actif' : '' ?>">Carte</a>
    <a href="<?= $h('/contact') ?>" class="<?= $sContact ? 'actif' : '' ?>">Contact</a>
    <a href="<?= $h('/signalement/creer') ?>" class="<?= $sCreer ? 'actif' : '' ?>">Signaler</a>
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="<?= $h('/notifications') ?>" class="<?= $sNotif ? 'actif' : '' ?>">Notifications</a>
<?php endif; ?>
<?php if (function_exists('cityzen_is_agent') && cityzen_is_agent()): ?>
    <a href="<?= $h('/backoffice') ?>" class="<?= $sBo ? 'actif' : '' ?>">Administration</a>
<?php endif; ?>
</nav>

<main class="page public-page interventions-module-body cz-interventions-scope" id="mainContent">
<div class="interventions-module-wrap">

<?php if (is_array($flash) && ($flash['message'] ?? '') !== ''): ?>
<div class="interv-flash interv-flash--<?= htmlspecialchars((string) ($flash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?= $content ?>

<div class="interventions-module-footer-note" role="note">
    <p>© <?= (int) date('Y') ?> CityZen — module signalements &amp; interventions</p>
</div>

</div><!-- .interventions-module-wrap -->
</main>

</div><!-- .site-shell-interventions -->

<script src="<?= htmlspecialchars($assetBase . '/public/assets/js/main.js?v=cz-mod-2', ENT_QUOTES, 'UTF-8') ?>"></script>

<script>
    window.COPILOT_APP_URL = <?= json_encode((string) APP_URL, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.COPILOT_USER_ROLE = <?= json_encode($_SESSION['user_role'] ?? 'guest', JSON_UNESCAPED_UNICODE) ?>;
    window.COPILOT_USER_NAME = <?= json_encode($_SESSION['user_prenom'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= htmlspecialchars($assetBase . '/public/assets/js/copilot-kb.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($assetBase . '/public/assets/js/copilot.js', ENT_QUOTES, 'UTF-8') ?>"></script>

<a href="<?= $h('/backoffice') ?>" id="adminFloatBtn" title="Accès administration" <?= (function_exists('cityzen_is_agent') && !cityzen_is_agent()) ? 'hidden' : '' ?>>
    <i class="fas fa-cog"></i>
    <span>Administration</span>
</a>

<style>
#adminFloatBtn {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 18px;
  background: linear-gradient(135deg, #6c63ff, #4ecdc4);
  color: #fff;
  text-decoration: none;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 4px 20px rgba(108, 99, 255, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
#adminFloatBtn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(108, 99, 255, 0.5); }
@media (max-width: 600px) {
  #adminFloatBtn span { display: none; }
  #adminFloatBtn { padding: 13px; border-radius: 50%; }
}
</style>

<?php cityzen_render_footer(); ?>
