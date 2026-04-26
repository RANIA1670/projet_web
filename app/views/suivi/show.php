<!-- ========== SUIVI - SHOW (Détail intervention) ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/suivi">Suivi</a>
            <i class="fas fa-chevron-right"></i>
            <span>#<?= str_pad($intervention['id'],5,'0',STR_PAD_LEFT) ?></span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1><i class="fas fa-search-location" style="color:var(--secondary);"></i> Suivi — Intervention #<?= str_pad($intervention['id'],5,'0',STR_PAD_LEFT) ?></h1>
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="badge badge-<?= $intervention['statut'] ?>"><?= ucfirst(str_replace('_',' ',$intervention['statut'])) ?></span>
                </div>
            </div>
            <a href="<?= APP_URL ?>/suivi" class="btn btn-outline-white btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;">

            <!-- Timeline -->
            <div>
                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">
                        <h3 style="font-size:1rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-hard-hat" style="color:var(--secondary);"></i>
                            <?= htmlspecialchars($intervention['titre']) ?>
                        </h3>
                    </div>
                    <?php if($intervention['description']): ?>
                    <div class="card-body">
                        <p style="font-size:.875rem;line-height:1.8;margin:0;"><?= nl2br(htmlspecialchars($intervention['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Suivi Timeline -->
                <div class="card">
                    <div class="card-header" style="justify-content:space-between;">
                        <h3 style="font-size:.9rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-stream" style="color:var(--secondary);"></i>
                            Historique complet du suivi
                        </h3>
                        <span style="font-size:.78rem;color:var(--text-muted);background:var(--gray-100);padding:4px 10px;border-radius:var(--radius-full);font-weight:600;">
                            <?= count($suivi) ?> étape<?= count($suivi)>1?'s':'' ?>
                        </span>
                    </div>
                    <div class="card-body" id="suiviTimelineWrap">
                        <?php if(empty($suivi)): ?>
                        <div class="empty-state" style="padding:28px 0;">
                            <i class="fas fa-clock" style="font-size:2.5rem;color:var(--gray-300);display:block;margin-bottom:12px;"></i>
                            <p>Aucune mise à jour pour le moment.</p>
                        </div>
                        <?php else: ?>
                        <div class="timeline" id="timelineContainer">
                            <?php foreach($suivi as $idx => $step): ?>
                            <div class="timeline-item" style="animation-delay:<?= $idx * 0.1 ?>s;">
                                <div class="timeline-dot" style="<?= $idx===0 ? 'background:var(--accent);box-shadow:0 0 0 3px rgba(230,126,34,.2);' : '' ?>"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">
                                        <i class="fas fa-clock"></i>
                                        <?= date('d/m/Y à H:i', strtotime($step['created_at'])) ?>
                                    </div>
                                    <?php if($step['statut']): ?>
                                    <div class="timeline-status">
                                        <i class="fas fa-tag" style="font-size:.75rem;"></i>
                                        <?= htmlspecialchars($step['statut']) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="timeline-comment"><?= nl2br(htmlspecialchars($step['commentaire'])) ?></div>
                                    <?php if($step['auteur_nom']): ?>
                                    <div class="timeline-author">
                                        <i class="fas fa-user-circle"></i> par <?= htmlspecialchars($step['auteur_nom']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Add comment (if logged in) -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="card-footer" style="background:var(--gray-50);">
                        <form action="<?= APP_URL ?>/suivi/<?= $intervention['id'] ?>/commentaire" method="POST" id="addCommentForm">
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <div style="flex:1;">
                                    <div class="form-row form-row-2" style="margin-bottom:10px;">
                                        <input type="text" name="statut" class="form-control" placeholder="Statut (ex: En cours, Terminé...)" style="font-size:.82rem;">
                                    </div>
                                    <textarea name="commentaire" class="form-control" rows="2" placeholder="Ajouter une mise à jour ou commentaire..." required style="font-size:.82rem;"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink:0;margin-top:36px;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display:flex;flex-direction:column;gap:20px;">
                <!-- Status -->
                <?php
                $statConf = [
                    'planifiee' => ['icon'=>'fa-calendar-alt','color'=>'#3498DB','label'=>'Planifiée','desc'=>'L\'intervention est planifiée'],
                    'en_cours'  => ['icon'=>'fa-cogs fa-spin','color'=>'#E67E22','label'=>'En cours','desc'=>'Les techniciens travaillent'],
                    'terminee'  => ['icon'=>'fa-check-double','color'=>'#27AE60','label'=>'Terminée','desc'=>'Intervention terminée avec succès'],
                    'annulee'   => ['icon'=>'fa-times','color'=>'#E74C3C','label'=>'Annulée','desc'=>'L\'intervention a été annulée'],
                ];
                $sc = $statConf[$intervention['statut']] ?? $statConf['planifiee'];
                ?>
                <div class="card" style="border:2px solid <?= $sc['color'] ?>20;overflow:hidden;">
                    <div style="background:<?= $sc['color'] ?>;padding:20px;text-align:center;">
                        <div style="font-size:2rem;color:rgba(255,255,255,.9);margin-bottom:8px;"><i class="fas <?= $sc['icon'] ?>"></i></div>
                        <div style="color:var(--white);font-family:var(--font-main);font-size:1rem;font-weight:700;"><?= $sc['label'] ?></div>
                        <div style="color:rgba(255,255,255,.75);font-size:.78rem;margin-top:4px;"><?= $sc['desc'] ?></div>
                    </div>
                    <div class="card-body" style="padding:16px;">
                        <!-- Progress Steps -->
                        <?php $steps=['planifiee'=>1,'en_cours'=>2,'terminee'=>3,'annulee'=>3];$cur=$steps[$intervention['statut']]??1; ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;position:relative;padding:0 8px;">
                            <div style="position:absolute;top:12px;left:20px;right:20px;height:2px;background:var(--border-color);z-index:0;"></div>
                            <div style="position:absolute;top:12px;left:20px;height:2px;background:<?= $sc['color'] ?>;z-index:1;width:<?= $intervention['statut']==='terminee'?'100':($intervention['statut']==='en_cours'?'50':'0') ?>%;transition:width 1s ease;"></div>
                            <?php foreach([['fa-calendar','Planifiée'],['fa-cogs','En cours'],['fa-check','Terminée']] as $i=>[$ico,$lbl]): ?>
                            <div style="text-align:center;position:relative;z-index:2;">
                                <div style="width:24px;height:24px;border-radius:50%;background:<?= $cur>$i?$sc['color']:'var(--border-color)' ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;"><i class="fas <?= $ico ?>" style="font-size:.6rem;color:<?= $cur>$i?'var(--white)':'var(--gray-400)' ?>;"></i></div>
                                <div style="font-size:.65rem;color:<?= $cur>$i?$sc['color']:'var(--text-muted)' ?>;font-weight:600;"><?= $lbl ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-info-circle" style="color:var(--secondary);"></i> Informations</h3></div>
                    <div class="card-body" style="padding:0;">
                        <?php foreach([
                            ['Référence','#'.str_pad($intervention['id'],5,'0',STR_PAD_LEFT),'fa-hashtag'],
                            ['Créée le',date('d/m/Y',strtotime($intervention['created_at'])),'fa-calendar'],
                            $intervention['date_planifiee']?['Planifiée',date('d/m/Y',strtotime($intervention['date_planifiee'])),'fa-calendar-check']:null,
                            $intervention['date_debut']?['Début',date('d/m/Y H:i',strtotime($intervention['date_debut'])),'fa-play']:null,
                            $intervention['date_fin']?['Fin',date('d/m/Y H:i',strtotime($intervention['date_fin'])),'fa-stop']:null,
                        ] as $row): if(!$row) continue; [$label,$val,$ico]=$row; ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:.75rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;"><i class="fas <?= $ico ?>"></i> <?= $label ?></span>
                            <span style="font-size:.82rem;font-weight:700;color:var(--text-primary);"><?= $val ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Technicien -->
                <?php if($intervention['technicien_nom']): ?>
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-hard-hat" style="color:var(--secondary);"></i> Technicien</h3></div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--secondary),var(--secondary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:1.1rem;font-weight:700;font-family:var(--font-main);">
                                <?= strtoupper(substr($intervention['technicien_nom'],0,1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:.9rem;"><?= htmlspecialchars($intervention['technicien_nom']) ?></div>
                                <?php if($intervention['technicien_email']): ?>
                                <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($intervention['technicien_email']) ?></div>
                                <?php endif; ?>
                                <?php if($intervention['technicien_tel']): ?>
                                <div style="font-size:.75rem;color:var(--text-muted);"><i class="fas fa-phone" style="font-size:.65rem;"></i> <?= htmlspecialchars($intervention['technicien_tel']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Signalement lié -->
                <?php if($intervention['signalement_titre']): ?>
                <div class="card">
                    <div class="card-header"><h3 style="font-size:.85rem;margin:0;"><i class="fas fa-exclamation-triangle" style="color:var(--accent);"></i> Signalement lié</h3></div>
                    <div class="card-body">
                        <p style="font-size:.85rem;font-weight:600;margin-bottom:8px;"><?= htmlspecialchars($intervention['signalement_titre']) ?></p>
                        <?php if($intervention['categorie_nom']): ?>
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:10px;"><i class="fas <?= htmlspecialchars($intervention['categorie_icone'] ?? 'fa-tag') ?>"></i> <?= htmlspecialchars($intervention['categorie_nom']) ?></div>
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/signalement/<?= $intervention['signalement_id'] ?>" class="btn btn-outline btn-sm btn-block">
                            <i class="fas fa-eye"></i> Voir le signalement
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>@media(max-width:900px){.section .container>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr !important;}}</style>

<script>
// AJAX comment form
const commentForm = document.getElementById('addCommentForm');
if (commentForm) {
    commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = commentForm.querySelector('[type="submit"]');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        try {
            const res = await fetch(commentForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(commentForm)
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            }
        } catch(e) {}
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        btn.disabled = false;
    });
}
</script>
