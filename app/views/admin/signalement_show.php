<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
        <div>
            <a href="<?= APP_URL ?>/admin/signalements" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Retour aux signalements</a>
            <h1 class="mb-1"><i class="fas fa-exclamation-triangle"></i> Signalement #<?= str_pad($signalement['id'], 5, '0', STR_PAD_LEFT) ?></h1>
            <p class="text-muted">Détail complet du signalement en administration.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= APP_URL ?>/admin/intervention/creer?signalement_id=<?= $signalement['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-tools"></i> Créer une intervention</a>
            <a href="<?= APP_URL ?>/admin/signalements" class="btn btn-sm btn-outline">Fermer</a>
        </div>
    </div>

    <div class="row gx-4 gy-4">
        <div class="col-lg-8">
            <?php if (!empty($signalement['image'])): ?>
                <div class="card mb-4 overflow-hidden">
                    <img src="<?= UPLOAD_URL . htmlspecialchars($signalement['image']) ?>" alt="<?= htmlspecialchars($signalement['titre']) ?>" style="width:100%;height:auto;object-fit:cover;" />
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Description</h2>
                    <p class="mb-0" style="white-space:pre-wrap;line-height:1.8;"><?= nl2br(htmlspecialchars($signalement['description'])) ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Actions & suivi</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-<?= htmlspecialchars($signalement['statut']) ?>"><?= ucfirst(str_replace('_', ' ', $signalement['statut'])) ?></span>
                        <span class="badge badge-<?= htmlspecialchars($signalement['priorite']) ?>"><i class="fas fa-flag"></i> <?= ucfirst(htmlspecialchars($signalement['priorite'])) ?></span>
                        <?php if (!empty($signalement['categorie_nom'])): ?>
                            <span class="badge badge-secondary"><i class="fas <?= htmlspecialchars($signalement['categorie_icone']) ?>"></i> <?= htmlspecialchars($signalement['categorie_nom']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h6 mb-3">Détails</h3>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Référence</dt>
                        <dd class="col-7">#<?= str_pad($signalement['id'], 5, '0', STR_PAD_LEFT) ?></dd>

                        <dt class="col-5 text-muted">Signalé le</dt>
                        <dd class="col-7"><?= date('d/m/Y H:i', strtotime($signalement['created_at'])) ?></dd>

                        <dt class="col-5 text-muted">Date incident</dt>
                        <dd class="col-7"><?= !empty($signalement['date_incident']) ? date('d/m/Y', strtotime($signalement['date_incident'])) : '-' ?></dd>

                        <dt class="col-5 text-muted">Statut</dt>
                        <dd class="col-7"><?= ucfirst(str_replace('_', ' ', $signalement['statut'])) ?></dd>

                        <dt class="col-5 text-muted">Priorité</dt>
                        <dd class="col-7"><?= ucfirst(htmlspecialchars($signalement['priorite'])) ?></dd>

                        <dt class="col-5 text-muted">Catégorie</dt>
                        <dd class="col-7"><?= htmlspecialchars($signalement['categorie_nom'] ?? 'N/A') ?></dd>

                        <dt class="col-5 text-muted">Adresse</dt>
                        <dd class="col-7"><?= htmlspecialchars($signalement['adresse'] ?? '-') ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="h6 mb-3">Signalé par</h3>
                    <p class="mb-1"><strong><?= htmlspecialchars($signalement['auteur_nom'] ?? 'Anonyme') ?></strong></p>
                    <?php if (!empty($signalement['auteur_email'])): ?>
                        <p class="text-muted mb-0"><?= htmlspecialchars($signalement['auteur_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-container { padding-bottom: 40px; }
.card { border-radius: 20px; box-shadow: 0 20px 45px rgba(15,23,42,0.06); border: 1px solid #eef2f7; }
.card-body { padding: 24px; }
.badge { padding: 0.7em 1em; border-radius: 999px; font-size: 0.82rem; text-transform: uppercase; letter-spacing: .05em; }
.badge-primary { background: #2563eb; color: white; }
.badge-secondary { background: #6b7280; color: white; }
.badge-success { background: #16a34a; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-info { background: #0ea5e9; color: white; }
</style>
