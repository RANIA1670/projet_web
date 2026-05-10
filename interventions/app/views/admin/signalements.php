<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= APP_URL ?>/backoffice" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
            <h1 class="mb-1"><i class="fas fa-exclamation-triangle"></i> Gestion des Signalements</h1>
            <p class="text-muted">Filtrer, consulter et gérer tous les signalements des citoyens.</p>
        </div>
    </div>

    <div class="card mb-4 bg-white rounded shadow-sm">
        <div class="card-body">
            <form action="<?= APP_URL ?>/backoffice/signalements" method="GET" class="d-flex gap-3 align-items-end">
                <div class="form-group mb-0 flex-grow-1">
                    <label for="statut">Filtrer par statut</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="nouveau" <?= $statut === 'nouveau' ? 'selected' : '' ?>>Nouveau</option>
                        <option value="en_cours" <?= $statut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="resolu" <?= $statut === 'resolu' ? 'selected' : '' ?>>Résolu</option>
                        <option value="rejete" <?= $statut === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
                <?php if ($statut): ?>
                    <a href="<?= APP_URL ?>/backoffice/signalements" class="btn btn-outline">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($signalements)): ?>
                    <tr><td colspan="8" class="text-center py-4">Aucun signalement trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($signalements as $sig): ?>
                    <tr>
                        <td>#<?= $sig['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($sig['created_at'])) ?></td>
                        <td>
                            <span class="category-badge" style="background-color: <?= $sig['categorie_couleur'] ?>20; color: <?= $sig['categorie_couleur'] ?>">
                                <i class="fas <?= $sig['categorie_icone'] ?>"></i> <?= htmlspecialchars($sig['categorie_nom']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($sig['titre']) ?></td>
                        <td><?= htmlspecialchars($sig['auteur_nom'] ?? 'Inconnu') ?></td>
                        <td>
                            <?php
                            $prioClass = match($sig['priorite']) {
                                'haute' => 'text-danger',
                                'moyenne' => 'text-warning',
                                'basse' => 'text-info',
                                default => ''
                            };
                            ?>
                            <span class="<?= $prioClass ?> fw-bold"><i class="fas fa-flag"></i> <?= ucfirst($sig['priorite']) ?></span>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($sig['statut']) {
                                'nouveau' => 'badge-primary',
                                'en_cours' => 'badge-warning',
                                'resolu' => 'badge-success',
                                'rejete' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $statutLabel = match($sig['statut']) {
                                'nouveau' => 'Nouveau',
                                'en_cours' => 'En cours',
                                'resolu' => 'Résolu',
                                'rejete' => 'Rejeté',
                                default => ucfirst($sig['statut'])
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/backoffice/signalement/<?= $sig['id'] ?>" class="btn btn-sm btn-outline-primary" title="Voir les détails"><i class="fas fa-eye"></i></a>
                            <?php if ($sig['statut'] === 'nouveau'): ?>
                                <a href="<?= APP_URL ?>/backoffice/intervention/creer?signalement_id=<?= $sig['id'] ?>" class="btn btn-sm btn-primary" title="Créer une intervention"><i class="fas fa-tools"></i></a>
                            <?php endif; ?>
                            
                            <form action="<?= APP_URL ?>/signalement/<?= $sig['id'] ?>/supprimer" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?');">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.admin-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
}
.admin-table th, .admin-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}
.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}
.admin-table tr:hover {
    background-color: #f8f9fa;
}
.category-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}
.card-body {
    padding: 20px;
}
</style>










