<!-- ========== HOME / ACCUEIL ========== -->

<!-- HERO -->
<section class="hero">
    <div class="hero-bg-shape hero-bg-shape-1"></div>
    <div class="hero-bg-shape hero-bg-shape-2"></div>
    <div class="container">
        <div class="hero-grid">
            <!-- Content -->
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-satellite-dish"></i> Plateforme Smart City
                </div>
                <h1 class="hero-title">
                    Gérez votre ville<br>
                    <span class="highlight">intelligemment</span>
                </h1>
                <p class="hero-desc">
                    CityZen est la plateforme intelligente qui connecte les citoyens et les services municipaux pour une gestion rapide, transparente et efficace des interventions urbaines.
                </p>
                <div class="hero-actions">
                    <a href="<?= APP_URL ?>/signalement/creer" class="btn btn-primary btn-xl">
                        <i class="fas fa-plus-circle"></i> Signaler un problème
                    </a>
                    <a href="<?= APP_URL ?>/suivi" class="btn btn-outline-white btn-xl">
                        <i class="fas fa-search"></i> Suivre mon dossier
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-value" data-count="<?= $stats['total_signalements'] ?>"><?= $stats['total_signalements'] ?></span>
                        <span class="hero-stat-label">Signalements</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-value" data-count="<?= $stats['en_cours'] ?>"><?= $stats['en_cours'] ?></span>
                        <span class="hero-stat-label">En cours</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-value" data-count="<?= $stats['resolus'] ?>"><?= $stats['resolus'] ?></span>
                        <span class="hero-stat-label">Résolus</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-value" data-count="<?= $stats['interventions'] ?>"><?= $stats['interventions'] ?></span>
                        <span class="hero-stat-label">Interventions</span>
                    </div>
                </div>
            </div>

            <!-- Visual / Live Panel -->
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="hero-card-title">
                        <i class="fas fa-circle-notch fa-spin" style="font-size:.7rem;"></i>
                        Signalements récents en direct
                    </div>
                    <?php foreach (array_slice($recentSignalements, 0, 4) as $r): ?>
                    <div class="hero-report-item">
                        <div class="hero-report-icon" style="background:<?= htmlspecialchars($r['categorie_couleur'] ?? '#2C3E50') ?>22; color:<?= htmlspecialchars($r['categorie_couleur'] ?? '#2C3E50') ?>">
                            <i class="fas <?= htmlspecialchars($r['categorie_icone'] ?? 'fa-exclamation') ?>"></i>
                        </div>
                        <div class="hero-report-text">
                            <div class="hero-report-title"><?= htmlspecialchars(mb_substr($r['titre'], 0, 40)) ?><?= strlen($r['titre']) > 40 ? '…' : '' ?></div>
                            <div class="hero-report-meta">
                                <i class="fas fa-map-marker-alt" style="font-size:.65rem;"></i>
                                <?= htmlspecialchars(mb_substr($r['adresse'], 0, 35)) ?>
                            </div>
                        </div>
                        <span class="badge badge-<?= htmlspecialchars($r['statut']) ?>"><?= ucfirst(str_replace('_',' ',$r['statut'])) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div style="margin-top:16px; text-align:right;">
                        <a href="<?= APP_URL ?>/signalements" style="font-family:var(--font-main);font-size:.8rem;font-weight:700;color:var(--secondary-light);">
                            Voir tous <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Floating badges -->
                <div class="hero-floating-badge hero-floating-badge-1">
                    <div style="width:32px;height:32px;background:rgba(39,174,96,.12);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--secondary);">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Résolu en 24h</span>
                </div>
                <div class="hero-floating-badge hero-floating-badge-2">
                    <div style="width:32px;height:32px;background:rgba(230,126,34,.12);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--accent);">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span>Réponse rapide</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="section" style="background:var(--white);padding:56px 0;">
    <div class="container">
        <div class="grid grid-4">
            <div class="stat-card reveal" style="--gradient: linear-gradient(90deg,#27AE60,#2ECC71)">
                <div class="stat-icon" style="background:rgba(39,174,96,.1);color:var(--secondary);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" data-count="<?= $stats['total_signalements'] ?>"><?= $stats['total_signalements'] ?></div>
                    <div class="stat-label">Total Signalements</div>
                    <div class="stat-trend up"><i class="fas fa-arrow-up"></i> Actif</div>
                </div>
            </div>
            <div class="stat-card reveal animate-fade-up-delay-1" style="--gradient: linear-gradient(90deg,#3498DB,#2980B9)">
                <div class="stat-icon" style="background:rgba(52,152,219,.1);color:#2980B9;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" data-count="<?= $stats['en_cours'] ?>"><?= $stats['en_cours'] ?></div>
                    <div class="stat-label">En Cours</div>
                    <div class="stat-trend up"><i class="fas fa-clock"></i> En traitement</div>
                </div>
            </div>
            <div class="stat-card reveal animate-fade-up-delay-2" style="--gradient: linear-gradient(90deg,#E67E22,#F39C12)">
                <div class="stat-icon" style="background:rgba(230,126,34,.1);color:var(--accent);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" data-count="<?= $stats['resolus'] ?>"><?= $stats['resolus'] ?></div>
                    <div class="stat-label">Résolus</div>
                    <div class="stat-trend up"><i class="fas fa-arrow-up"></i> +12% ce mois</div>
                </div>
            </div>
            <div class="stat-card reveal animate-fade-up-delay-3" style="--gradient: linear-gradient(90deg,#9B59B6,#8E44AD)">
                <div class="stat-icon" style="background:rgba(155,89,182,.1);color:#9B59B6;">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" data-count="<?= $stats['interventions'] ?>"><?= $stats['interventions'] ?></div>
                    <div class="stat-label">Interventions</div>
                    <div class="stat-trend up"><i class="fas fa-tools"></i> Sur le terrain</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <div class="section-badge"><i class="fas fa-lightbulb"></i> Comment ça marche</div>
            <h2 class="section-title">Simple, rapide et <span>efficace</span></h2>
            <p class="section-desc">En 3 étapes simples, signalez un problème et suivez sa résolution en temps réel.</p>
            <div class="title-line"></div>
        </div>
        <div class="grid grid-3">
            <div class="service-card reveal text-center">
                <div class="service-icon" style="background:rgba(39,174,96,.1);color:var(--secondary);margin:0 auto 20px;">
                    <i class="fas fa-camera"></i>
                </div>
                <div style="position:absolute;top:20px;right:20px;font-family:var(--font-main);font-size:3rem;font-weight:900;color:var(--border-color);line-height:1;">01</div>
                <h3>Signalez</h3>
                <p>Décrivez le problème avec photo, adresse et catégorie. Notre formulaire premium vous guide en quelques secondes.</p>
                <a href="<?= APP_URL ?>/signalement/creer" class="btn btn-primary btn-sm" style="margin-top:20px;">
                    <i class="fas fa-plus"></i> Créer un signalement
                </a>
            </div>
            <div class="service-card reveal animate-fade-up-delay-1 text-center" style="border-top:3px solid var(--accent);">
                <div class="service-icon" style="background:rgba(230,126,34,.1);color:var(--accent);margin:0 auto 20px;">
                    <i class="fas fa-cogs"></i>
                </div>
                <div style="position:absolute;top:20px;right:20px;font-family:var(--font-main);font-size:3rem;font-weight:900;color:var(--border-color);line-height:1;">02</div>
                <h3>Nous traitons</h3>
                <p>Notre équipe analyse le signalement, l'affecte à un technicien qualifié et planifie l'intervention.</p>
                <a href="<?= APP_URL ?>/interventions" class="btn btn-accent btn-sm" style="margin-top:20px;">
                    <i class="fas fa-tools"></i> Voir les interventions
                </a>
            </div>
            <div class="service-card reveal animate-fade-up-delay-2 text-center" style="border-top:3px solid var(--info-light);">
                <div class="service-icon" style="background:rgba(52,152,219,.1);color:var(--info-light);margin:0 auto 20px;">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div style="position:absolute;top:20px;right:20px;font-family:var(--font-main);font-size:3rem;font-weight:900;color:var(--border-color);line-height:1;">03</div>
                <h3>Suivez en temps réel</h3>
                <p>Recevez des mises à jour en temps réel et suivez l'avancement jusqu'à la résolution complète.</p>
                <a href="<?= APP_URL ?>/suivi" class="btn btn-dark btn-sm" style="margin-top:20px;">
                    <i class="fas fa-search-location"></i> Suivre mon dossier
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header reveal">
            <div class="section-badge"><i class="fas fa-th-large"></i> Catégories</div>
            <h2 class="section-title">Tous les types de <span>signalements</span></h2>
            <p class="section-desc">Signalez tout type de problème urbain. Nous couvrons l'ensemble des services municipaux.</p>
            <div class="title-line"></div>
        </div>
        <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/signalements?categorie_id=<?= $cat['id'] ?>" class="service-card reveal" style="padding:24px;text-align:center;text-decoration:none;">
                <div style="font-size:2rem;color:<?= htmlspecialchars($cat['couleur']) ?>;margin-bottom:12px;"><i class="fas <?= htmlspecialchars($cat['icone']) ?>"></i></div>
                <h4 style="font-size:0.9rem;margin-bottom:6px;"><?= htmlspecialchars($cat['nom']) ?></h4>
                <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">
                    <?= $cat['count_signalements'] ?> signalement<?= $cat['count_signalements'] != 1 ? 's' : '' ?>
                </p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RECENT SIGNALEMENTS -->
<?php if (!empty($recentSignalements)): ?>
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <div class="section-badge"><i class="fas fa-clock"></i> Récents</div>
            <h2 class="section-title">Derniers <span>signalements</span></h2>
            <div class="title-line"></div>
        </div>
        <div class="grid grid-3">
            <?php foreach ($recentSignalements as $s): ?>
            <div class="signalement-card reveal">
                <div class="signalement-card-image">
                    <?php if ($s['image']): ?>
                        <img src="<?= UPLOAD_URL . htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>">
                    <?php else: ?>
                        <div class="signalement-card-image-placeholder">
                            <i class="fas <?= htmlspecialchars($s['categorie_icone'] ?? 'fa-exclamation') ?>" style="color:<?= htmlspecialchars($s['categorie_couleur'] ?? '#ccc') ?>"></i>
                        </div>
                    <?php endif; ?>
                    <div style="position:absolute;top:12px;left:12px;">
                        <span class="badge badge-<?= $s['priorite'] ?>"><i class="fas fa-flag"></i> <?= ucfirst($s['priorite']) ?></span>
                    </div>
                </div>
                <div class="signalement-card-body">
                    <div class="signalement-card-meta">
                        <span class="badge badge-<?= $s['statut'] ?>"><?= ucfirst(str_replace('_',' ',$s['statut'])) ?></span>
                        <?php if ($s['categorie_nom']): ?>
                        <span style="font-size:.75rem;color:var(--text-muted);"><i class="fas <?= htmlspecialchars($s['categorie_icone']) ?>"></i> <?= htmlspecialchars($s['categorie_nom']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="signalement-card-title"><?= htmlspecialchars($s['titre']) ?></div>
                    <div class="signalement-card-desc"><?= htmlspecialchars($s['description']) ?></div>
                    <div class="signalement-card-footer">
                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(mb_substr($s['adresse'], 0, 30)) ?></span>
                        <a href="<?= APP_URL ?>/signalement/<?= $s['id'] ?>">Voir <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-32">
            <a href="<?= APP_URL ?>/signalements" class="btn btn-outline">
                <i class="fas fa-list"></i> Voir tous les signalements
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content reveal">
            <div class="section-badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2);">
                <i class="fas fa-rocket"></i> Agissez maintenant
            </div>
            <h2 style="color:var(--white);font-size:clamp(1.8rem,3.5vw,2.8rem);margin-top:16px;">
                Vous avez repéré un problème<br>dans votre ville ?
            </h2>
            <p>Ne restez pas silencieux. Signalez-le en quelques clics et contribuez à rendre votre ville plus belle et plus sûre.</p>
            <div class="cta-actions">
                <a href="<?= APP_URL ?>/signalement/creer" class="btn btn-primary btn-xl">
                    <i class="fas fa-plus-circle"></i> Signaler maintenant
                </a>
                <a href="<?= APP_URL ?>/intervention/demande" class="btn btn-outline-white btn-xl">
                    <i class="fas fa-hard-hat"></i> Demander une intervention
                </a>
                <a href="<?= APP_URL ?>/contact" class="btn btn-outline-white btn-xl">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>
