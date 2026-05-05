<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - CityZen</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .chart-container { position: relative; height: 300px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-item { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .export-btn { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .export-btn:hover { background: #218838; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">← Retour au site</a>
        <a href="/admin">Administration</a>
        <a href="/auth/deconnexion">Déconnexion</a>
    </div>

    <h1>Dashboard Administrateur</h1>

    <div class="stats-grid">
        <div class="stat-item">
            <h3>Signalements</h3>
            <div class="stat-number"><?= $stats['total_signalements'] ?></div>
        </div>
        <div class="stat-item">
            <h3>Interventions</h3>
            <div class="stat-number"><?= $stats['total_interventions'] ?></div>
        </div>
        <div class="stat-item">
            <h3>Citoyens</h3>
            <div class="stat-number"><?= $stats['citoyens'] ?></div>
        </div>
        <div class="stat-item">
            <h3>Techniciens</h3>
            <div class="stat-number"><?= $stats['techniciens'] ?></div>
        </div>
    </div>

    <button class="export-btn" onclick="window.open('/admin/export-pdf', '_blank')">
        <i class="fas fa-file-pdf"></i> Exporter en PDF
    </button>

    <div class="dashboard">
        <div class="card">
            <h3>Répartition des Signalements</h3>
            <div class="chart-container">
                <canvas id="signalementsChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Répartition des Interventions</h3>
            <div class="chart-container">
                <canvas id="interventionsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="more-stats">
        <div class="card">
            <h3>Signalements par catégorie</h3>
            <ul>
                <?php foreach ($stats['signalements_by_categorie'] as $row): ?>
                    <li><?= htmlspecialchars($row['categorie_nom']) ?> : <strong><?= $row['total'] ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card">
            <h3>Interventions par technicien</h3>
            <ul>
                <?php foreach ($stats['interventions_by_technician'] as $row): ?>
                    <li><?= htmlspecialchars($row['technicien_nom'] ?: 'Non assigné') ?> : <strong><?= $row['total'] ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        const signalementsData = {
            labels: [
                <?php foreach ($stats['signalements_by_status'] as $stat): ?>
                '<?= ucfirst(str_replace('_', ' ', $stat['statut'])) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($stats['signalements_by_status'] as $stat): ?>
                    <?= $stat['total'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            }]
        };

        const interventionsData = {
            labels: [
                <?php foreach ($stats['interventions_by_status'] as $stat): ?>
                '<?= ucfirst(str_replace('_', ' ', $stat['statut'])) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($stats['interventions_by_status'] as $stat): ?>
                    <?= $stat['total'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            }]
        };

        new Chart(document.getElementById('signalementsChart'), {
            type: 'doughnut',
            data: signalementsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        new Chart(document.getElementById('interventionsChart'), {
            type: 'doughnut',
            data: interventionsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>










