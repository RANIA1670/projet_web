<!-- ========== SUIVI INTERVENTION ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Suivi d'Intervention</span>
        </div>
        <h1><i class="fas fa-search-location" style="color:var(--secondary);"></i> Suivi d'Intervention</h1>
        <p>Entrez votre numéro de référence pour suivre l'état de votre dossier en temps réel.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Search Bar -->
        <div style="max-width:640px;margin:0 auto 48px;" class="reveal">
            <div class="card" style="border-radius:var(--radius-xl);">
                <div class="card-body" style="padding:32px;">
                    <div style="text-align:center;margin-bottom:24px;">
                        <div style="width:64px;height:64px;background:rgba(39,174,96,.1);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--secondary);font-size:1.8rem;">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <h3 style="font-size:1.2rem;margin-bottom:6px;">Suivre mon dossier</h3>
                        <p style="font-size:.875rem;">Entrez le numéro de référence de votre signalement ou intervention.</p>
                    </div>
                    <form action="<?= APP_URL ?>/suivi" method="GET" id="suiviSearchForm">
                        <div style="display:flex;gap:12px;">
                            <div class="input-icon-wrap" style="flex:1;">
                                <i class="input-icon fas fa-hashtag"></i>
                                <input type="text" id="refInput" name="reference" class="form-control"
                                    placeholder="Ex: 00001 ou #00001"
                                    value="<?= htmlspecialchars($reference ?? '') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if(!empty($reference) && !$intervention): ?>
        <!-- Not Found -->
        <div class="empty-state reveal" style="max-width:500px;margin:0 auto;">
            <div class="empty-state-icon" style="color:var(--danger);"><i class="fas fa-times-circle"></i></div>
            <h3 style="color:var(--danger);">Dossier introuvable</h3>
            <p>Aucune intervention trouvée pour la référence <strong>"<?= htmlspecialchars($reference) ?>"</strong>.<br>Vérifiez le numéro et réessayez.</p>
        </div>
        <?php elseif($intervention): ?>
        <!-- Result -->
        <div style="display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start;" class="reveal">

            <!-- Timeline -->
            <div>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <div>
                            <h3 style="font-size:1rem;margin:0;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-hard-hat" style="color:var(--secondary);"></i>
                                <?= htmlspecialchars($intervention['titre']) ?>
                            </h3>
                            <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                                <span class="badge badge-<?= $intervention['statut'] ?>"><?= ucfirst(str_replace('_',' ',$intervention['statut'])) ?></span>
                                <span style="font-size:.78rem;color:var(--text-muted);">Réf. #<?= str_pad($intervention['id'],5,'0',STR_PAD_LEFT) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if($intervention['description']): ?>
                    <div class="card-body">
                        <p style="font-size:.875rem;line-height:1.8;"><?= nl2br(htmlspecialchars($intervention['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Suivi Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.9rem;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-stream" style="color:var(--secondary);"></i> Historique du suivi
                        </h3>
                        <span style="font-size:.78rem;color:var(--text-muted);"><?= count($suivi) ?> étape<?= count($suivi)>1?'s':'' ?></span>
                    </div>
                    <div class="card-body" id="suiviTimeline">
                        <?php if(empty($suivi)): ?>
                        <div class="empty-state" style="padding:32px 0;">
                            <i class="fas fa-clock" style="font-size:2.5rem;color:var(--gray-300);margin-bottom:12px;display:block;"></i>
                            <p>Aucune mise à jour disponible pour le moment.</p>
                        </div>
                        <?php else: ?>
                        <div class="timeline" id="timelineContainer">
                            <?php foreach($suivi as $step): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">
                                        <i class="fas fa-clock"></i>
                                        <?= date('d/m/Y à H:i', strtotime($step['created_at'])) ?>
                                    </div>
                                    <?php if($step['statut']): ?>
                                    <div class="timeline-status"><?= htmlspecialchars($step['statut']) ?></div>
                                    <?php endif; ?>
                                    <div class="timeline-comment"><?= nl2br(htmlspecialchars($step['commentaire'])) ?></div>
                                    <?php if($step['auteur_nom']): ?>
                                    <div class="timeline-author">
                                        <i class="fas fa-user"></i> par <?= htmlspecialchars($step['auteur_nom']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display:flex;flex-direction:column;gap:20px;">
                <!-- Status Card -->
                <?php
                $statusConfig = [
                    'planifiee' => ['icon'=>'fa-calendar-check','color'=>'#3498DB','label'=>'Planifiée','bg'=>'rgba(52,152,219,.1)'],
                    'en_cours'  => ['icon'=>'fa-cogs','color'=>'#E67E22','label'=>'En cours','bg'=>'rgba(230,126,34,.1)'],
                    'terminee'  => ['icon'=>'fa-check-circle','color'=>'#27AE60','label'=>'Terminée','bg'=>'rgba(39,174,96,.1)'],
                    'annulee'   => ['icon'=>'fa-times-circle','color'=>'#E74C3C','label'=>'Annulée','bg'=>'rgba(231,76,60,.1)'],
                ];
                $s = $statusConfig[$intervention['statut']] ?? $statusConfig['planifiee'];
                ?>
                <div class="card" style="border-top:4px solid <?= $s['color'] ?>;">
                    <div class="card-body" style="text-align:center;padding:28px;">
                        <div style="width:72px;height:72px;background:<?= $s['bg'] ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:<?= $s['color'] ?>;font-size:1.8rem;">
                            <i class="fas <?= $s['icon'] ?>"></i>
                        </div>
                        <h3 style="color:<?= $s['color'] ?>;font-size:1.2rem;margin-bottom:4px;"><?= $s['label'] ?></h3>
                        <p style="font-size:.82rem;color:var(--text-muted);">Statut de l'intervention</p>
                    </div>
                </div>

                <!-- Signalement lié -->
                <?php if($intervention['signalement_titre']): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.85rem;margin:0;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-exclamation-triangle" style="color:var(--accent);"></i> Signalement associé
                        </h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size:.875rem;font-weight:600;color:var(--text-primary);margin-bottom:8px;">
                            <?= htmlspecialchars($intervention['signalement_titre']) ?>
                        </p>
                        <div style="display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--text-muted);margin-bottom:12px;">
                            <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i>
                            <?= htmlspecialchars($intervention['adresse'] ?? '') ?>
                        </div>
                        <?php if($intervention['priorite']): ?>
                        <span class="badge badge-<?= $intervention['priorite'] ?>"><?= ucfirst($intervention['priorite']) ?></span>
                        <?php endif; ?>
                        <div style="margin-top:12px;">
                            <a href="<?= APP_URL ?>/signalement/<?= $intervention['signalement_id'] ?>" class="btn btn-outline btn-sm btn-block">
                                <i class="fas fa-eye"></i> Voir le signalement
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Technicien -->
                <?php if($intervention['technicien_nom']): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.85rem;margin:0;"><i class="fas fa-hard-hat" style="color:var(--secondary);"></i> Technicien assigné</h3>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:46px;height:46px;background:linear-gradient(135deg,var(--secondary),var(--secondary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-family:var(--font-main);font-weight:700;font-size:1rem;">
                                <?= strtoupper(substr($intervention['technicien_nom'],0,1)) ?>
                            </div>
                            <div>
                                <div style="font-size:.9rem;font-weight:700;"><?= htmlspecialchars($intervention['technicien_nom']) ?></div>
                                <?php if($intervention['technicien_tel']): ?>
                                <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($intervention['technicien_tel']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Dates -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="font-size:.85rem;margin:0;"><i class="fas fa-calendar" style="color:var(--info-light);"></i> Calendrier</h3>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <?php foreach([
                            ['Date planifiée', $intervention['date_planifiee'] ? date('d/m/Y', strtotime($intervention['date_planifiee'])) : null, 'fa-calendar-check'],
                            ['Début', $intervention['date_debut'] ? date('d/m/Y H:i', strtotime($intervention['date_debut'])) : null, 'fa-play'],
                            ['Fin', $intervention['date_fin'] ? date('d/m/Y H:i', strtotime($intervention['date_fin'])) : null, 'fa-stop'],
                        ] as [$label, $val, $icon]): if(!$val) continue; ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:.78rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;"><i class="fas <?= $icon ?>"></i> <?= $label ?></span>
                            <span style="font-size:.82rem;font-weight:600;color:var(--text-primary);"><?= $val ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Recent Interventions -->
        <div class="reveal">
            <div class="section-header" style="margin-bottom:28px;text-align:left;">
                <h3 style="font-size:1.1rem;"><i class="fas fa-history" style="color:var(--secondary);"></i> Interventions récentes</h3>
            </div>
            <?php if(!empty($recentInterventions)): ?>
            <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
                <?php foreach($recentInterventions as $ri): ?>
                <a href="<?= APP_URL ?>/suivi/<?= $ri['id'] ?>" style="text-decoration:none;">
                    <div class="card" style="cursor:pointer;">
                        <div class="card-body" style="padding:20px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <span style="font-family:var(--font-main);font-size:.75rem;font-weight:700;color:var(--primary);">#<?= str_pad($ri['id'],5,'0',STR_PAD_LEFT) ?></span>
                                <span class="badge badge-<?= $ri['statut'] ?>"><?= ucfirst(str_replace('_',' ',$ri['statut'])) ?></span>
                            </div>
                            <h4 style="font-size:.9rem;margin-bottom:8px;color:var(--text-primary);"><?= htmlspecialchars(mb_substr($ri['titre'],0,50)) ?></h4>
                            <?php if($ri['adresse']): ?>
                            <p style="font-size:.78rem;color:var(--text-muted);margin:0;"><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> <?= htmlspecialchars(mb_substr($ri['adresse'],0,40)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<style>@media(max-width:900px){.section .container>div[style*="grid-template-columns:1fr 380px"]{grid-template-columns:1fr !important;}}</style>
