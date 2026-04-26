<div class="admin-dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-cogs"></i> Tableau de bord Administration</h1>
        </div>

        <div class="admin-stats-grid mb-5">
            <div class="stat-card">
                <div class="stat-icon bg-primary-light">
                    <i class="fas fa-exclamation-triangle text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Signalements</h3>
                    <p class="stat-number"><?= $totalSignalements ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-warning-light">
                    <i class="fas fa-tools text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Interventions</h3>
                    <p class="stat-number"><?= $totalInterventions ?></p>
                </div>
            </div>
        </div>

        <div class="admin-quick-links mb-5">
            <div class="dashboard-cards">
                <a href="<?= APP_URL ?>/admin/signalements" class="dashboard-card">
                    <i class="fas fa-list-alt"></i>
                    <h4>Gérer les signalements</h4>
                    <p>Consulter, filtrer et gérer tous les signalements des citoyens.</p>
                </a>
                
                <a href="<?= APP_URL ?>/admin/interventions" class="dashboard-card">
                    <i class="fas fa-hammer"></i>
                    <h4>Gérer les interventions</h4>
                    <p>Suivre l'état d'avancement des travaux sur le terrain.</p>
                </a>
                
                <a href="<?= APP_URL ?>/admin/techniciens" class="dashboard-card">
                    <i class="fas fa-hard-hat"></i>
                    <h4>Gérer les techniciens</h4>
                    <p>Voir la disponibilité des techniciens et leurs affectations.</p>
                </a>
            </div>
        </div>

        <div class="recent-section">
            <h2 class="mb-3">Derniers signalements reçus</h2>
            <?php if (empty($recentSignalements)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Aucun signalement pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Catégorie</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSignalements as $sig): ?>
                            <tr>
                                <td>#<?= $sig['id'] ?></td>
                                <td>
                                    <span class="category-badge" style="background-color: <?= $sig['categorie_couleur'] ?>20; color: <?= $sig['categorie_couleur'] ?>">
                                        <i class="fas <?= $sig['categorie_icone'] ?>"></i> <?= htmlspecialchars($sig['categorie_nom']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($sig['titre']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($sig['created_at'])) ?></td>
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
                                    <a href="<?= APP_URL ?>/signalement/<?= $sig['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> Voir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Admin specific styles */
.admin-dashboard-container {
    background-color: #f8f9fa;
    min-height: calc(100vh - 200px);
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.stat-content h3 {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.stat-number {
    margin: 5px 0 0;
    font-size: 32px;
    font-weight: 700;
    color: #212529;
}
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.text-primary { color: #0d6efd; }
.text-warning { color: #ffc107; }

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}
.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.dashboard-card i {
    font-size: 40px;
    color: var(--primary-color, #0d6efd);
    margin-bottom: 20px;
}
.dashboard-card h4 {
    margin-bottom: 10px;
    color: #212529;
}
.dashboard-card p {
    color: #6c757d;
    margin: 0;
}

.admin-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border-collapse: collapse;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.admin-table th, .admin-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    text-align: left;
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
</style>
