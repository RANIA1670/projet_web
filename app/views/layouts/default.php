<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CityZen - Plateforme intelligente de gestion des interventions et signalements urbains. Signalez, suivez et gérez les problèmes de votre ville.">
    <meta name="keywords" content="smart city, signalement, intervention urbaine, gestion ville, citoyens">
    <meta name="author" content="CityZen Team">
    <meta property="og:title" content="CityZen - <?= htmlspecialchars($pageTitle ?? 'Smart Intervention Management') ?>">
    <meta property="og:description" content="Plateforme intelligente de gestion des interventions urbaines">
    <title>CityZen | <?= htmlspecialchars($pageTitle ?? 'Accueil') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/main.css">

    <!-- Copilot CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/copilot.css">
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="navbar" id="mainNavbar">
    <div class="container navbar-inner">
        <!-- Brand -->
        <a href="<?= APP_URL ?>/" class="navbar-brand">
            <div class="brand-icon">
                <i class="fas fa-city"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">CityZen</span>
                <span class="brand-tagline">Smart City</span>
            </div>
        </a>

        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li>
                <a href="<?= APP_URL ?>/" class="nav-link <?= (!isset($_SERVER['REQUEST_URI']) || parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/fati/public/' || parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/fati/public/index.php') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/signalements" class="nav-link">
                    <i class="fas fa-exclamation-triangle"></i> Signalements
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/interventions" class="nav-link">
                    <i class="fas fa-tools"></i> Interventions
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/suivi" class="nav-link">
                    <i class="fas fa-search-location"></i> Suivi
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/contact" class="nav-link">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li>
                <a href="<?= APP_URL ?>/admin" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog"></i> Admin
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Nav Actions -->
        <div class="nav-actions">
            <!-- Widget Notifications -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div style="position:relative;">
                <button id="notifBtn" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-dark); position:relative;">
                    <i class="fas fa-bell"></i>
                    <span id="notifBadge" style="display:none; position:absolute; top:-8px; right:-8px; background:var(--secondary); color:white; border-radius:50%; width:20px; height:20px; font-size:0.75rem; display:flex; align-items:center; justify-content:center; font-weight:bold;"></span>
                </button>
                <div id="notifDropdown" style="display:none; position:absolute; top:100%; right:0; background:white; border:1px solid var(--border-color); border-radius:8px; width:350px; max-height:400px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:1000;">
                    <div style="padding:12px; border-bottom:1px solid var(--border-color); font-weight:600;">Notifications</div>
                    <div id="notifList"></div>
                    <div style="padding:12px; text-align:center; border-top:1px solid var(--border-color);">
                        <a href="<?= APP_URL ?>/notifications" style="color:var(--secondary); text-decoration:none; font-size:0.9rem;">Voir toutes les notifications</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <a href="<?= APP_URL ?>/signalement/creer" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Signaler
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown">
                    <button class="user-btn" id="userDropBtn">
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1)) ?></div>
                        <span><?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="userDropMenu">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></strong>
                            <span><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= APP_URL ?>/auth/deconnexion" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= APP_URL ?>/auth/connexion" class="btn btn-outline btn-sm">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Toggle -->
        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Flash Message -->
<?php if (isset($flash) && $flash): ?>
<div class="flash-container" id="flashContainer">
    <div class="flash flash-<?= $flash['type'] ?>">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle') ?>"></i>
        <span><?= htmlspecialchars($flash['message']) ?></span>
        <button class="flash-close" onclick="this.parentElement.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content" id="mainContent">
    <?= $content ?>
</main>

<!-- ========== FOOTER ========== -->
<footer class="footer">
    <div class="footer-top">
        <div class="container footer-grid">
            <!-- Brand -->
            <div class="footer-col footer-brand">
                <div class="footer-logo">
                    <div class="brand-icon"><i class="fas fa-city"></i></div>
                    <div class="brand-text">
                        <span class="brand-name">CityZen</span>
                        <span class="brand-tagline">Smart City</span>
                    </div>
                </div>
                <p>Plateforme intelligente de gestion des interventions et signalements urbains. Ensemble, construisons une ville meilleure.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Navigation -->
            <div class="footer-col">
                <h4>Navigation</h4>
                <ul class="footer-nav">
                    <li><a href="<?= APP_URL ?>/"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                    <li><a href="<?= APP_URL ?>/signalements"><i class="fas fa-chevron-right"></i> Signalements</a></li>
                    <li><a href="<?= APP_URL ?>/signalement/creer"><i class="fas fa-chevron-right"></i> Signaler un problème</a></li>
                    <li><a href="<?= APP_URL ?>/interventions"><i class="fas fa-chevron-right"></i> Interventions</a></li>
                    <li><a href="<?= APP_URL ?>/suivi"><i class="fas fa-chevron-right"></i> Suivi</a></li>
                    <li><a href="<?= APP_URL ?>/contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="footer-col">
                <h4>Services</h4>
                <ul class="footer-nav">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Voirie & Routes</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Éclairage Public</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Espaces Verts</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Eau & Assainissement</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Sécurité Urbaine</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> 15 Avenue Habib Bourguiba, Tunis</li>
                    <li><i class="fas fa-phone"></i> +216 71 000 001</li>
                    <li><i class="fas fa-envelope"></i> contact@cityzen.tn</li>
                    <li><i class="fas fa-clock"></i> Lun - Ven: 08h00 - 17h00</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?= date('Y') ?> CityZen - Smart Intervention Management. Tous droits réservés.</p>
            <div class="footer-badges">
                <span class="badge-tech"><i class="fab fa-php"></i> PHP MVC</span>
                <span class="badge-tech"><i class="fas fa-database"></i> MySQL</span>
                <span class="badge-tech"><i class="fab fa-js"></i> AJAX</span>
            </div>
        </div>
    </div>
</footer>

<!-- Main JS -->
<script src="<?= APP_URL ?>/public/assets/js/main.js"></script>

<!-- ========== WIDGET NOTIFICATIONS ========== -->
<script>
<?php if (isset($_SESSION['user_id'])): ?>
// Initialiser le widget de notifications
document.addEventListener('DOMContentLoaded', function() {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifList = document.getElementById('notifList');
    const notifBadge = document.getElementById('notifBadge');

    if (!notifBtn) return;

    // Charger les notifications
    async function loadNotifications() {
        try {
            const response = await fetch('<?= APP_URL ?>/api/notifications/widget');
            const data = await response.json();

            // Mettre à jour le badge
            if (data.count > 0) {
                notifBadge.textContent = data.count;
                notifBadge.style.display = 'flex';
            } else {
                notifBadge.style.display = 'none';
            }

            // Mettre à jour la liste
            if (data.notifications.length === 0) {
                notifList.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted);">Aucune nouvelle notification</div>';
            } else {
                notifList.innerHTML = data.notifications.map(n => `
                    <div style="padding:12px; border-bottom:1px solid var(--border-color); cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='white'">
                        <div style="display:flex; justify-content:space-between; align-items:start; gap:12px;">
                            <div style="flex:1;">
                                <strong style="font-size:0.95rem;">${n.titre}</strong>
                                <p style="margin:4px 0 0; font-size:0.85rem; color:var(--text-muted);">${n.message || ''}</p>
                                <p style="margin:4px 0 0; font-size:0.8rem; color:var(--text-muted);">${n.time_ago}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Erreur chargement notifications:', error);
        }
    }

    // Charger au démarrage
    loadNotifications();

    // Recharger toutes les 30 secondes
    setInterval(loadNotifications, 30000);

    // Afficher/masquer le dropdown
    notifBtn.addEventListener('click', () => {
        notifDropdown.style.display = notifDropdown.style.display === 'none' ? 'block' : 'none';
    });

    // Fermer en cliquant ailleurs
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#notifBtn') && !e.target.closest('#notifDropdown')) {
            notifDropdown.style.display = 'none';
        }
    });
});
<?php endif; ?>
</script>

<!-- ========== BOUTON ADMINISTRATION FLOTTANT ========== -->
<a href="<?= APP_URL ?>/admin" id="adminFloatBtn" title="Accès Administration">
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
        font-family: 'Inter', 'Montserrat', sans-serif;
        box-shadow: 0 4px 20px rgba(108, 99, 255, 0.4);
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
    }
    #adminFloatBtn:hover {
        transform: translateY(-3px) scale(1.04);
        box-shadow: 0 8px 28px rgba(108, 99, 255, 0.55);
        background: linear-gradient(135deg, #5a52d5, #3dbdb5);
    }
    #adminFloatBtn:active { transform: translateY(0) scale(0.98); }
    #adminFloatBtn i {
        font-size: 15px;
        animation: spin-slow 4s linear infinite;
    }
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    @media (max-width: 600px) {
        #adminFloatBtn span { display: none; }
        #adminFloatBtn { padding: 13px; border-radius: 50%; }
    }
</style>

<!-- ========== COPILOT ZENO ========== -->
<script>
    window.COPILOT_APP_URL = '<?= APP_URL ?>';
    window.COPILOT_USER_ROLE = '<?= $_SESSION['user_role'] ?? 'guest' ?>';
    window.COPILOT_USER_NAME = '<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>';
</script>
<script src="<?= APP_URL ?>/public/assets/js/copilot-kb.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/copilot.js"></script>

</body>
</html>
