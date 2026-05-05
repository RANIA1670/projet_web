<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
            <h1 class="mb-1"><i class="fas fa-users-cog"></i> Gestion des Techniciens</h1>
            <p class="text-muted">Liste des techniciens, statut de disponibilité et interventions en cours.</p>
        </div>
        <a href="<?= APP_URL ?>/admin/technicien/creer" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter un technicien</a>
    </div>

    <div class="card mb-4 bg-white rounded shadow-sm">
        <div class="card-body p-4">
            <h2 class="mb-3">Techniciens</h2>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Intervention</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($techniciens)): ?>
                            <tr><td colspan="7" class="text-center py-4">Aucun technicien trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($techniciens as $tech): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($tech['id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(($tech['prenom'] ?? '') . ' ' . ($tech['nom'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($tech['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($tech['telephone'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($tech['is_busy'])): ?>
                                            <span class="badge badge-warning">Occupé</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Libre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($tech['is_busy']) && !empty($tech['current_intervention'])): ?>
                                            <a href="<?= APP_URL ?>/admin/intervention/<?= htmlspecialchars($tech['current_intervention']['id']) ?>" class="btn btn-sm btn-outline-secondary">#<?= htmlspecialchars($tech['current_intervention']['id']) ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">Aucune</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/admin/technicien/<?= htmlspecialchars($tech['id'] ?? '') ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pencil-alt"></i></a>
                                        <form action="<?= APP_URL ?>/admin/technicien/<?= htmlspecialchars($tech['id'] ?? '') ?>/supprimer" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce technicien ?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.admin-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
}
.admin-table th, .admin-table td {
    padding: 15px 18px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}
.admin-table th {
    background-color: #f8f9fa;
    font-weight: 700;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
}
.admin-table tr:hover {
    background-color: #f8f9fa;
}
.card {
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}
.card-body { padding: 24px; }
.btn-outline-secondary { border-color: #ced4da; color: #495057; }
.btn-outline-danger { border-color: #e74c3c; color: #e74c3c; }
</style>










