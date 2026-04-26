<!-- ========== SIGNALEMENTS - LIST ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Signalements</span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;">
            <div>
                <h1><i class="fas fa-list" style="color:var(--secondary);"></i> Signalements</h1>
                <p><?= $total ?> signalement<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?></p>
            </div>
            <a href="<?= APP_URL ?>/signalement/creer" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau signalement
            </a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Filter Bar -->
        <div class="filter-bar" id="filterBarEl">
            <form action="<?= APP_URL ?>/signalements" method="GET" id="filterForm">
                <div class="filter-form">
                    <div class="filter-group filter-search">
                        <label>Recherche</label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-search" style="font-size:.85rem;"></i>
                            <input type="text" name="search" class="filter-control form-control" style="padding-left:36px;"
                                placeholder="Titre, adresse..." value="<?= htmlspecialchars($filters['search']) ?>">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label>Statut</label>
                        <select name="statut" class="filter-control">
                            <option value="">Tous les statuts</option>
                            <?php foreach(['nouveau','en_attente','en_cours','resolu','ferme'] as $s): ?>
                            <option value="<?= $s ?>" <?= $filters['statut']===$s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Priorité</label>
                        <select name="priorite" class="filter-control">
                            <option value="">Toutes</option>
                            <?php foreach(['faible','moyenne','haute','urgente'] as $p): ?>
                            <option value="<?= $p ?>" <?= $filters['priorite']===$p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Catégorie</label>
                        <select name="categorie_id" class="filter-control">
                            <option value="">Toutes</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filters['categorie_id']==$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group" style="justify-content:flex-end;">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrer</button>
                    </div>
                    <?php if(array_filter($filters)): ?>
                    <div class="filter-group" style="justify-content:flex-end;">
                        <label>&nbsp;</label>
                        <a href="<?= APP_URL ?>/signalements" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Réinitialiser</a>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Grid -->
        <?php if(empty($signalements)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-search"></i></div>
            <h3>Aucun signalement trouvé</h3>
            <p>Essayez de modifier vos filtres ou <a href="<?= APP_URL ?>/signalement/creer" style="color:var(--secondary);font-weight:600;">créez un nouveau signalement</a>.</p>
        </div>
        <?php else: ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;" id="signalementsGrid">
            <?php foreach($signalements as $s): ?>
            <div class="signalement-card reveal">
                <div class="signalement-card-image" style="height:160px;">
                    <?php if($s['image']): ?>
                        <img src="<?= UPLOAD_URL . htmlspecialchars($s['image']) ?>" alt="">
                    <?php else: ?>
                        <div class="signalement-card-image-placeholder" style="background:<?= htmlspecialchars($s['categorie_couleur']??'#eee') ?>18;">
                            <i class="fas <?= htmlspecialchars($s['categorie_icone']??'fa-exclamation') ?>" style="color:<?= htmlspecialchars($s['categorie_couleur']??'#ccc') ?>"></i>
                        </div>
                    <?php endif; ?>
                    <div style="position:absolute;top:10px;right:10px;">
                        <span class="badge badge-<?= $s['statut'] ?>"><?= ucfirst(str_replace('_',' ',$s['statut'])) ?></span>
                    </div>
                    <div style="position:absolute;top:10px;left:10px;">
                        <span class="badge badge-<?= $s['priorite'] ?>"><i class="fas fa-flag"></i> <?= ucfirst($s['priorite']) ?></span>
                    </div>
                    <div style="position:absolute;bottom:0;left:0;right:0;height:50%;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);"></div>
                    <div style="position:absolute;bottom:10px;left:12px;font-family:var(--font-main);font-size:.72rem;font-weight:700;color:rgba(255,255,255,.9);">
                        #<?= str_pad($s['id'],5,'0',STR_PAD_LEFT) ?>
                    </div>
                </div>
                <div class="signalement-card-body">
                    <div class="signalement-card-meta">
                        <?php if($s['categorie_nom']): ?>
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;color:<?= htmlspecialchars($s['categorie_couleur']??'var(--primary)') ?>;background:<?= htmlspecialchars($s['categorie_couleur']??'#2C3E50') ?>18;padding:3px 10px;border-radius:var(--radius-full);">
                            <i class="fas <?= htmlspecialchars($s['categorie_icone']??'fa-tag') ?>"></i> <?= htmlspecialchars($s['categorie_nom']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="signalement-card-title"><?= htmlspecialchars($s['titre']) ?></div>
                    <div class="signalement-card-desc"><?= htmlspecialchars($s['description']) ?></div>
                    <div class="signalement-card-footer">
                        <span style="display:flex;align-items:center;gap:4px;">
                            <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i>
                            <?= htmlspecialchars(mb_substr($s['adresse'],0,28)) ?>
                        </span>
                        <a href="<?= APP_URL ?>/signalement/<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                            Détails <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            <?php for($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
