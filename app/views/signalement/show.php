<!-- ========== SIGNALEMENT - SHOW ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/signalements">Signalements</a>
            <i class="fas fa-chevron-right"></i>
            <span>#<?= str_pad($signalement['id'],5,'0',STR_PAD_LEFT) ?></span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1><?= htmlspecialchars($signalement['titre']) ?></h1>
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px;flex-wrap:wrap;">
                    <span class="badge badge-<?= $signalement['statut'] ?>"><?= ucfirst(str_replace('_',' ',$signalement['statut'])) ?></span>
                    <span class="badge badge-<?= $signalement['priorite'] ?>"><i class="fas fa-flag"></i> <?= ucfirst($signalement['priorite']) ?></span>
                    <?php if($signalement['categorie_nom']): ?>
                    <span style="font-size:.78rem;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:4px;">
                        <i class="fas <?= htmlspecialchars($signalement['categorie_icone']) ?>"></i> <?= htmlspecialchars($signalement['categorie_nom']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="<?= APP_URL ?>/signalements" class="btn btn-outline-white btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;">

            <!-- Main Content -->
            <div>
                <?php if($signalement['image']): ?>
                <div class="card" style="margin-bottom:24px;overflow:hidden;">
                    <img src="<?= UPLOAD_URL . htmlspecialchars($signalement['image']) ?>"
                         alt="<?= htmlspecialchars($signalement['titre']) ?>"
                         style="width:100%;max-height:420px;object-fit:cover;">
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:1rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-file-alt" style="color:var(--secondary);"></i> Description
                        </h3>
                    </div>
                    <div class="card-body">
                        <p style="line-height:1.9;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($signalement['description'])) ?></p>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap;">
                    <a href="<?= APP_URL ?>/intervention/demande?signalement_id=<?= $signalement['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-hard-hat"></i> Demander une intervention
                    </a>
                    <a href="<?= APP_URL ?>/suivi?reference=<?= $signalement['id'] ?>" class="btn btn-outline">
                        <i class="fas fa-search-location"></i> Suivre le dossier
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display:flex;flex-direction:column;gap:20px;">
                <!-- Details -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-info-circle" style="color:var(--secondary);"></i> Détails
                        </h3>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table style="width:100%;">
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border-color);width:40%;">Référence</td>
                                <td style="padding:14px 20px;font-size:.875rem;font-weight:700;color:var(--primary);border-bottom:1px solid var(--border-color);">#<?= str_pad($signalement['id'],5,'0',STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border-color);">Statut</td>
                                <td style="padding:14px 20px;border-bottom:1px solid var(--border-color);">
                                    <span class="badge badge-<?= $signalement['statut'] ?>"><?= ucfirst(str_replace('_',' ',$signalement['statut'])) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border-color);">Priorité</td>
                                <td style="padding:14px 20px;border-bottom:1px solid var(--border-color);">
                                    <span class="badge badge-<?= $signalement['priorite'] ?>"><?= ucfirst($signalement['priorite']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border-color);">Catégorie</td>
                                <td style="padding:14px 20px;font-size:.875rem;color:var(--text-secondary);border-bottom:1px solid var(--border-color);">
                                    <?= $signalement['categorie_nom'] ? htmlspecialchars($signalement['categorie_nom']) : 'N/A' ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border-color);">Date</td>
                                <td style="padding:14px 20px;font-size:.875rem;color:var(--text-secondary);border-bottom:1px solid var(--border-color);">
                                    <?= date('d/m/Y', strtotime($signalement['date_incident'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Signalé le</td>
                                <td style="padding:14px 20px;font-size:.875rem;color:var(--text-secondary);">
                                    <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Location -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> Localisation
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <div style="width:36px;height:36px;background:rgba(230,126,34,.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
                                <i class="fas fa-location-arrow"></i>
                            </div>
                            <p style="margin:0;font-size:.875rem;line-height:1.6;"><?= htmlspecialchars($signalement['adresse']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Author -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-user" style="color:var(--info-light);"></i> Signalé par
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:42px;height:42px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-family:var(--font-main);font-weight:700;">
                                <?= strtoupper(substr($signalement['auteur_nom'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-family:var(--font-main);font-size:.875rem;font-weight:700;"><?= htmlspecialchars($signalement['auteur_nom'] ?? 'Anonyme') ?></div>
                                <?php if($signalement['auteur_email']): ?>
                                <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($signalement['auteur_email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media(max-width:768px){ .container > div[style*="grid-template-columns"] { grid-template-columns:1fr !important; } }
</style>
