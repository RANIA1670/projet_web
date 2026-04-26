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
                <a href="<?= APP_URL ?>/admin" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') !== false ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Administration
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Nav Actions -->
        <div class="nav-actions">
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
</body>
</html>
