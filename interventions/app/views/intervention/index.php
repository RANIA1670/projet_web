<!-- ========== INTERVENTIONS - LIST ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Interventions</span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1><i class="fas fa-hard-hat" style="color:var(--secondary);"></i> Interventions</h1>
                <p><?= $total ?> intervention<?= $total > 1 ? 's' : '' ?> au total</p>
            </div>
            <a href="<?= APP_URL ?>/intervention/demande" class="btn btn-primary">
                <i class="fas fa-plus"></i> Demander une intervention
            </a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if(empty($interventions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-hard-hat"></i></div>
            <h3>Aucune intervention enregistrée</h3>
            <p>Les interventions apparaîtront ici après traitement de vos signalements.</p>
            <a href="<?= APP_URL ?>/intervention/demande" class="btn btn-primary" style="margin-top:20px;">
                <i class="fas fa-plus"></i> Soumettre une demande
            </a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <div class="table-header">
                <div class="table-title"><i class="fas fa-list"></i> Liste des interventions</div>
                <a href="<?= APP_URL ?>/intervention/demande" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nouvelle demande
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Réf.</th>
                            <th>Titre</th>
                            <th>Signalement</th>
                            <th>Technicien</th>
                            <th>Statut</th>
                            <th>Date planifiée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($interventions as $i): ?>
                        <tr>
                            <td>
                                <span style="font-family:var(--font-main);font-weight:700;color:var(--primary);">
                                    #<?= str_pad($i['id'],5,'0',STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600;color:var(--text-primary);max-width:240px;">
                                    <?= htmlspecialchars(mb_substr($i['titre'],0,50)) ?>
                                </div>
                            </td>
                            <td>
                                <?php if($i['signalement_titre']): ?>
                                <a href="<?= APP_URL ?>/signalement/<?= $i['signalement_id'] ?>" style="color:var(--secondary);font-size:.8rem;font-weight:600;">
                                    <?= htmlspecialchars(mb_substr($i['signalement_titre'],0,30)) ?>
                                </a>
                                <?php else: ?>
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($i['technicien_nom']): ?>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:28px;height:28px;background:linear-gradient(135deg,var(--secondary),var(--secondary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:.7rem;font-weight:700;">
                                        <?= strtoupper(substr($i['technicien_nom'],0,1)) ?>
                                    </div>
                                    <span style="font-size:.82rem;"><?= htmlspecialchars($i['technicien_nom']) ?></span>
                                </div>
                                <?php else: ?>
                                <span style="color:var(--text-muted);font-size:.8rem;">Non assigné</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= $i['statut'] ?>"><?= ucfirst(str_replace('_',' ',$i['statut'])) ?></span></td>
                            <td style="font-size:.82rem;color:var(--text-muted);">
                                <?= $i['date_planifiee'] ? date('d/m/Y', strtotime($i['date_planifiee'])) : '—' ?>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/suivi/<?= $i['id'] ?>" class="btn btn-outline btn-sm" style="font-size:.75rem;">
                                    <i class="fas fa-eye"></i> Suivre
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php if($page>1): ?><a href="?page=<?= $page-1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
                <a href="?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if($page<$totalPages): ?><a href="?page=<?= $page+1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
