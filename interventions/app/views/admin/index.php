<div class="admin-dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h1><i class="fas fa-cogs"></i> Tableau de bord Administration</h1>
            <a href="<?= APP_URL ?>/backoffice/export-pdf" target="_blank" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
        </div>

        <div class="admin-summary-grid mb-5">
            <div class="summary-card summary-blue">
                <div>
                    <p>Total signalements</p>
                    <h3><?= $totalSignalements ?></h3>
                </div>
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="summary-card summary-green">
                <div>
                    <p>Total interventions</p>
                    <h3><?= $totalInterventions ?></h3>
                </div>
                <i class="fas fa-tools"></i>
            </div>
            <div class="summary-card summary-purple">
                <div>
                    <p>Citoyens inscrits</p>
                    <h3><?= $stats['citoyens'] ?></h3>
                </div>
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="summary-card summary-orange">
                <div>
                    <p>Techniciens actifs</p>
                    <h3><?= $stats['techniciens'] ?></h3>
                </div>
                <i class="fas fa-hard-hat"></i>
            </div>
        </div>

        <div class="admin-dashboard-grid mb-5">
            <div class="panel-card panel-large">
                <div class="panel-header">Signalements par catégorie</div>
                <div class="panel-chart"><canvas id="categoryBarChart"></canvas></div>
            </div>
            <div class="panel-card panel-large">
                <div class="panel-header">Interventions par statut</div>
                <div class="panel-chart"><canvas id="statusBarChart"></canvas></div>
            </div>
        </div>

        <div class="admin-small-grid mb-5">
            <div class="panel-card">
                <div class="panel-header">Répartition signalements</div>
                <div class="panel-chart small-chart"><canvas id="signalementsPieChart"></canvas></div>
            </div>
            <div class="panel-card">
                <div class="panel-header">Techniciens / interventions</div>
                <div class="panel-chart small-chart"><canvas id="techniciansDoughnutChart"></canvas></div>
            </div>
            <div class="panel-card mini-progress-card">
                <div class="panel-header">Taux de statut</div>
                <?php
                    $signalStatusMap = array_column($stats['signalements_by_status'], 'total', 'statut');
                    $totalSignal = max(1, $stats['total_signalements']);
                    $statuses = [
                        'nouveau' => 'Nouveaux',
                        'en_cours' => 'En cours',
                        'resolu' => 'Résolus',
                        'rejete' => 'Rejetés',
                    ];
                ?>
                <div class="progress-grid">
                    <?php foreach ($statuses as $key => $label):
                        $value = $signalStatusMap[$key] ?? 0;
                        $percent = round(($value / $totalSignal) * 100);
                    ?>
                        <div class="progress-item">
                            <span><?= $label ?></span>
                            <strong><?= $percent ?>%</strong>
                            <div class="progress-bar"><div class="progress-fill" style="width: <?= $percent ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="admin-bottom-grid mb-5">
            <div class="panel-card panel-large">
                <div class="panel-header">Évolution des signalements</div>
                <div class="panel-chart"><canvas id="trendLineChart"></canvas></div>
            </div>
            <div class="panel-card panel-large">
                <div class="panel-header">Interventions des 6 derniers mois</div>
                <div class="panel-chart"><canvas id="monthlyBarChart"></canvas></div>
            </div>
        </div>

        <div class="admin-quick-links mb-5">
            <div class="dashboard-cards">
                <a href="<?= APP_URL ?>/backoffice/signalements" class="dashboard-card">
                    <i class="fas fa-list-alt"></i>
                    <h4>Gérer les signalements</h4>
                    <p>Consulter, filtrer et gérer tous les signalements des citoyens.</p>
                </a>
                
                <a href="<?= APP_URL ?>/backoffice/interventions" class="dashboard-card">
                    <i class="fas fa-hammer"></i>
                    <h4>Gérer les interventions</h4>
                    <p>Suivre l'état d'avancement des travaux sur le terrain.</p>
                </a>
                
                <a href="<?= APP_URL ?>/backoffice/techniciens" class="dashboard-card">
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
                                <td>#<?= htmlspecialchars($sig['id'] ?? '') ?></td>
                                <td>
                                    <span class="category-badge" style="background-color: <?= htmlspecialchars($sig['categorie_couleur'] ?? '#6c757d') ?>20; color: <?= htmlspecialchars($sig['categorie_couleur'] ?? '#6c757d') ?>">
                                        <i class="fas <?= htmlspecialchars($sig['categorie_icone'] ?? 'fa-tag') ?>"></i> <?= htmlspecialchars($sig['categorie_nom'] ?? 'Sans catégorie') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($sig['titre'] ?? '') ?></td>
                                <td><?= !empty($sig['created_at']) ? date('d/m/Y H:i', strtotime($sig['created_at'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $statut = $sig['statut'] ?? '';
                                    $badgeClass = match($statut) {
                                        'nouveau' => 'badge-primary',
                                        'en_cours' => 'badge-warning',
                                        'resolu' => 'badge-success',
                                        'rejete' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                    $statutLabel = match($statut) {
                                        'nouveau' => 'Nouveau',
                                        'en_cours' => 'En cours',
                                        'resolu' => 'Résolu',
                                        'rejete' => 'Rejeté',
                                        default => ucfirst(str_replace('_', ' ', $statut ?: 'Inconnu'))
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/backoffice/signalement/<?= htmlspecialchars($sig['id'] ?? '') ?>" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> Voir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="recent-section mt-5">
            <h2 class="mb-3">Dernières interventions</h2>
            <?php if (empty($recentInterventions)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Aucune intervention pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Objet</th>
                                <th>Technicien</th>
                                <th>Date prévue</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInterventions as $inv): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($inv['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($inv['objet'] ?? $inv['titre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($inv['technicien_nom'] ?? 'Non assigné') ?></td>
                                <td><?= !empty($inv['date_planifiee']) ? date('d/m/Y', strtotime($inv['date_planifiee'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $statut = $inv['statut'] ?? '';
                                    $badgeClass = match($statut) {
                                        'planifiee' => 'badge-secondary',
                                        'en_cours' => 'badge-warning',
                                        'termine' => 'badge-success',
                                        'annule' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                    $statutLabel = ucfirst(str_replace('_', ' ', $statut ?: 'Inconnu'));
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/backoffice/intervention/<?= htmlspecialchars($inv['id'] ?? '') ?>" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> Voir</a>
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
.admin-chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}
.chart-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
    padding: 24px;
    display: flex;
    flex-direction: column;
    min-height: 340px;
}
.chart-card-header {
    font-size: 1rem;
    font-weight: 700;
    color: #2C3E50;
    margin-bottom: 18px;
}
.chart-wrapper {
    position: relative;
    flex: 1;
    min-height: 250px;
}
.chart-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
    border-top: 1px solid #f0f2f5;
    padding-top: 14px;
}
.chart-label {
    color: #95A5A6;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.chart-wrapper canvas {
    width: 100% !important;
    height: 100% !important;
}

    .admin-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }
    .summary-card {
        background: white;
        border-radius: 22px;
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
    }
    .summary-card p {
        margin: 0 0 8px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.82rem;
    }
    .summary-card h3 {
        margin: 0;
        font-size: 2.1rem;
        color: #111827;
    }
    .summary-card i {
        font-size: 28px;
        color: white;
        padding: 16px;
        border-radius: 18px;
    }
    .summary-blue i { background: #2563eb; }
    .summary-green i { background: #10b981; }
    .summary-purple i { background: #8b5cf6; }
    .summary-orange i { background: #f97316; }

    .admin-dashboard-grid,
    .admin-bottom-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }
    .admin-small-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
    }
    .panel-card {
        background: #fff;
        border-radius: 24px;
        padding: 26px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        min-height: 320px;
    }
    .panel-large {
        min-height: 360px;
    }
    .panel-header {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 18px;
        color: #111827;
    }
    .panel-chart {
        flex: 1;
        min-height: 240px;
        position: relative;
    }
    .small-chart { min-height: 220px; }
    .mini-progress-card {
        gap: 16px;
        min-height: auto;
    }
    .progress-grid {
        display: grid;
        gap: 16px;
    }
    .progress-item span {
        display: block;
        color: #475569;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    .progress-item strong {
        display: block;
        margin-bottom: 8px;
        font-size: 1.1rem;
        color: #111827;
    }
    .progress-bar {
        background: #e2e8f0;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-fill {
        height: 10px;
        background: linear-gradient(90deg, #2563eb 0%, #22c55e 100%);
        border-radius: 999px;
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const signalementsByStatus = <?= json_encode($stats['signalements_by_status'] ?? []) ?>;
const interventionsByStatus = <?= json_encode($stats['interventions_by_status'] ?? []) ?>;
const signalementsByCategorie = <?= json_encode($stats['signalements_by_categorie'] ?? []) ?>;
const signalementsTrend = <?= json_encode($stats['signalements_trend'] ?? []) ?>;
const interventionsMonthly = <?= json_encode($stats['interventions_monthly'] ?? []) ?>;
const interventionsByTechnician = <?= json_encode($stats['interventions_by_technician'] ?? []) ?>;

const statusLabels = signalementsByStatus.map(row => row.statut ? row.statut.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase()) : 'Inconnu');
const statusValues = signalementsByStatus.map(row => Number(row.total) || 0);
const interventionStatusLabels = interventionsByStatus.map(row => row.statut ? row.statut.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase()) : 'Inconnu');
const interventionStatusValues = interventionsByStatus.map(row => Number(row.total) || 0);
const technicianLabels = interventionsByTechnician.map(row => row.technicien_nom || 'Non assigné');
const technicianTotals = interventionsByTechnician.map(row => Number(row.total) || 0);
const categoryLabels = signalementsByCategorie.map(row => row.nom || 'Autre');
const categoryTotals = signalementsByCategorie.map(row => Number(row.total) || 0);
const trendLabels = signalementsTrend.map(row => row.label);
const trendValues = signalementsTrend.map(row => Number(row.total) || 0);
const monthlyLabels = interventionsMonthly.map(row => row.month_label);
const monthlyTotals = interventionsMonthly.map(row => Number(row.total) || 0);

const baseColors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#22C55E', '#0EA5E9'];

new Chart(document.getElementById('categoryBarChart'), {
    type: 'bar',
    data: {
        labels: categoryLabels,
        datasets: [{
            label: 'Signalements',
            data: categoryTotals,
            backgroundColor: baseColors.slice(0, categoryLabels.length),
            borderRadius: 12,
            maxBarThickness: 40,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(15, 23, 42, 0.08)' } }
        }
    }
});

new Chart(document.getElementById('statusBarChart'), {
    type: 'bar',
    data: {
        labels: interventionStatusLabels,
        datasets: [{
            label: 'Interventions',
            data: interventionStatusValues,
            backgroundColor: ['#22C55E', '#0EA5E9', '#F59E0B', '#EF4444'].slice(0, interventionStatusLabels.length),
            borderRadius: 12,
            maxBarThickness: 40,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(15, 23, 42, 0.08)' } }
        }
    }
});

new Chart(document.getElementById('signalementsPieChart'), {
    type: 'doughnut',
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryTotals,
            backgroundColor: baseColors.slice(0, categoryLabels.length),
            borderColor: '#fff',
            borderWidth: 2,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } }
    }
});

new Chart(document.getElementById('techniciansDoughnutChart'), {
    type: 'doughnut',
    data: {
        labels: technicianLabels,
        datasets: [{
            data: technicianTotals,
            backgroundColor: baseColors.slice(0, technicianTotals.length),
            borderColor: '#fff',
            borderWidth: 2,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } }
    }
});

new Chart(document.getElementById('trendLineChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Signalements',
            data: trendValues,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.18)',
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#3B82F6',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(15, 23, 42, 0.08)' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('monthlyBarChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Interventions',
            data: monthlyTotals,
            backgroundColor: 'rgba(59, 130, 246, 0.9)',
            borderRadius: 12,
            maxBarThickness: 40,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(15, 23, 42, 0.08)' } }
        }
    }
});
</script>





