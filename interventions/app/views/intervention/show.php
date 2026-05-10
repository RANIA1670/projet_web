<!-- ========== INTERVENTION - SHOW ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/interventions">Interventions</a>
            <i class="fas fa-chevron-right"></i>
            <span>#<?= str_pad($intervention['id'],5,'0',STR_PAD_LEFT) ?></span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1><?= htmlspecialchars($intervention['titre']) ?></h1>
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="badge badge-<?= $intervention['statut'] ?>"><?= ucfirst(str_replace('_',' ',$intervention['statut'])) ?></span>
                    <span style="font-size:.78rem;color:rgba(255,255,255,.6);">Intervention #<?= str_pad($intervention['id'],5,'0',STR_PAD_LEFT) ?></span>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="<?= APP_URL ?>/suivi/<?= $intervention['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-search-location"></i> Suivre</a>
                <a href="<?= APP_URL ?>/interventions" class="btn btn-outline-white btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;">

            <div>
                <!-- Description -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;"><i class="fas fa-file-alt" style="color:var(--secondary);"></i> Description</h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size:.9rem;line-height:1.8;margin:0;"><?= nl2br(htmlspecialchars($intervention['description'] ?? 'Aucune description fournie.')) ?></p>
                    </div>
                </div>

                <!-- Notes -->
                <?php if($intervention['notes']): ?>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;"><i class="fas fa-sticky-note" style="color:var(--accent);"></i> Notes techniques</h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size:.875rem;line-height:1.8;background:rgba(230,126,34,.06);border-left:3px solid var(--accent);padding:14px;border-radius:0 var(--radius) var(--radius) 0;margin:0;"><?= nl2br(htmlspecialchars($intervention['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Suivi Timeline -->
                <div class="card">
                    <div class="card-header" style="justify-content:space-between;">
                        <h3 style="font-size:.9rem;margin:0;"><i class="fas fa-stream" style="color:var(--secondary);"></i> Historique</h3>
                        <a href="<?= APP_URL ?>/suivi/<?= $intervention['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Suivi détaillé</a>
                    </div>
                    <div class="card-body">
                        <?php if(empty($suivi)): ?>
                        <div class="empty-state" style="padding:24px 0;">
                            <i class="fas fa-clock" style="font-size:2rem;color:var(--gray-300);display:block;margin-bottom:10px;"></i>
                            <p style="font-size:.85rem;">Aucune mise à jour disponible.</p>
                        </div>
                        <?php else: ?>
                        <div class="timeline">
                            <?php foreach(array_slice($suivi, 0, 5) as $step): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date"><i class="fas fa-clock"></i> <?= date('d/m/Y à H:i', strtotime($step['created_at'])) ?></div>
                                    <?php if($step['statut']): ?><div class="timeline-status"><?= htmlspecialchars($step['statut']) ?></div><?php endif; ?>
                                    <div class="timeline-comment"><?= nl2br(htmlspecialchars($step['commentaire'])) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if(count($suivi) > 5): ?>
                        <div style="text-align:center;margin-top:16px;">
                            <a href="<?= APP_URL ?>/suivi/<?= $intervention['id'] ?>" class="btn btn-outline btn-sm">Voir tout l'historique (<?= count($suivi) ?>)</a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display:flex;flex-direction:column;gap:20px;">
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-info-circle" style="color:var(--secondary);"></i> Détails</h3></div>
                    <div class="card-body" style="padding:0;">
                        <?php foreach([
                            ['Référence','#'.str_pad($intervention['id'],5,'0',STR_PAD_LEFT)],
                            ['Statut','<span class="badge badge-'.$intervention['statut'].'">'.ucfirst(str_replace('_',' ',$intervention['statut'])).'</span>'],
                            ['Créée',date('d/m/Y',strtotime($intervention['created_at']))],
                            $intervention['date_planifiee']?['Planifiée',date('d/m/Y',strtotime($intervention['date_planifiee']))]:null,
                            $intervention['date_debut']?['Début',date('d/m/Y H:i',strtotime($intervention['date_debut']))]:null,
                            $intervention['date_fin']?['Fin',date('d/m/Y H:i',strtotime($intervention['date_fin']))]:null,
                        ] as $row): if(!$row) continue; [$label,$val]=$row; ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 18px;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;"><?= $label ?></span>
                            <span style="font-size:.82rem;font-weight:700;color:var(--text-primary);"><?= $val ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if($intervention['technicien_nom']): ?>
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-hard-hat" style="color:var(--secondary);"></i> Technicien</h3></div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:44px;height:44px;background:linear-gradient(135deg,var(--secondary),var(--secondary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:700;font-family:var(--font-main);">
                                <?= strtoupper(substr($intervention['technicien_nom'],0,1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:.875rem;"><?= htmlspecialchars($intervention['technicien_nom']) ?></div>
                                <?php if($intervention['technicien_tel']): ?><div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($intervention['technicien_tel']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($intervention['signalement_titre']): ?>
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-exclamation-triangle" style="color:var(--accent);"></i> Signalement associé</h3></div>
                    <div class="card-body">
                        <p style="font-size:.85rem;font-weight:600;margin-bottom:6px;"><?= htmlspecialchars($intervention['signalement_titre']) ?></p>
                        <?php if($intervention['adresse']): ?><p style="font-size:.75rem;color:var(--text-muted);margin-bottom:12px;"><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> <?= htmlspecialchars($intervention['adresse']) ?></p><?php endif; ?>
                        <a href="<?= APP_URL ?>/signalement/<?= $intervention['signalement_id'] ?>" class="btn btn-outline btn-sm btn-block"><i class="fas fa-eye"></i> Voir</a>
                    </div>
                </div>
                <?php endif; ?>

                <a href="<?= APP_URL ?>/suivi/<?= $intervention['id'] ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-search-location"></i> Suivi en temps réel
                </a>
            </div>
        </div>
    </div>
</section>
<style>@media(max-width:900px){.section .container>div[style*="grid-template-columns:1fr 340px"]{grid-template-columns:1fr !important;}}</style>
