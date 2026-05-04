<?php

declare(strict_types=1);

cityzen_render_head('Statistiques');
?>

<style>
.stats-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.stats-title {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.stats-filters {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 600;
}

.filter-select {
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    font-size: 0.9rem;
    cursor: pointer;
    transition: border-color 0.3s;
}

.filter-select:hover {
    border-color: #007bff;
}

.filter-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.stat-circle-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    text-align: center;
    position: relative;
}

.stat-circle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.circle-chart-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 15px;
}

.circle-chart {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: conic-gradient(
        var(--primary-color) 0deg var(--progress-deg),
        #f0f0f0 var(--progress-deg) 360deg
    );
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.circle-chart::before {
    content: '';
    position: absolute;
    width: 70%;
    height: 70%;
    background: white;
    border-radius: 50%;
    z-index: 1;
}

.circle-chart-value {
    position: absolute;
    z-index: 2;
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary-color);
    line-height: 1;
}

.circle-chart-label {
    font-size: 1rem;
    color: #666;
    margin-bottom: 5px;
    font-weight: 600;
}

.circle-chart-description {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 10px;
}

.circle-change {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 8px;
}

.circle-change.positive {
    background: #d4edda;
    color: #155724;
}

.circle-change.negative {
    background: #f8d7da;
    color: #721c24;
}

.circle-change.neutral {
    background: #f8f9fa;
    color: #6c757d;
}

/* Color themes for circles */
.circle-primary { --primary-color: #007bff; }
.circle-success { --primary-color: #28a745; }
.circle-warning { --primary-color: #ffc107; }
.circle-danger { --primary-color: #dc3545; }
.circle-info { --primary-color: #17a2b8; }
.circle-secondary { --primary-color: #6c757d; }

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-title {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
}

.chart-canvas {
    width: 100%;
    height: 300px;
    position: relative;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    height: 250px;
    gap: 10px;
    padding: 10px 0;
}

.bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 30px;
}

.bar {
    width: 100%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 4px 4px 0 0;
    transition: all 0.3s;
    position: relative;
}

.bar:hover {
    opacity: 0.8;
}

.bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: bold;
    color: #333;
}

.bar-label {
    margin-top: 10px;
    font-size: 0.8rem;
    color: #666;
    text-align: center;
}

.pie-chart {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    position: relative;
}

.pie-slice {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.distribution-list {
    margin-top: 20px;
}

.distribution-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.distribution-item:last-child {
    border-bottom: none;
}

.distribution-label {
    font-weight: 500;
    color: #333;
}

.distribution-value {
    display: flex;
    align-items: center;
    gap: 10px;
}

.distribution-count {
    font-weight: bold;
    color: #666;
}

.distribution-percentage {
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    color: #666;
}

.recent-registrations {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-responsive {
    overflow-x: auto;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.stats-table th,
.stats-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.stats-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.stats-table tr:hover {
    background: #f8f9fa;
}

.user-role {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.user-role.admin {
    background: #d4edda;
    color: #155724;
}

.user-role.user {
    background: #d1ecf1;
    color: #0c5460;
}

.loading-spinner {
    display: none;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .stats-container {
        padding: 10px;
    }
    
    .stats-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .bar-chart {
        height: 200px;
    }
    
    .stats-table {
        font-size: 0.9rem;
    }
    
    .stats-table th,
    .stats-table td {
        padding: 8px;
    }
}
</style>

<div class="stats-container">
    <div class="stats-header">
        <div>
            <h1 class="stats-title">Statistiques des utilisateurs</h1>
            <p style="color: #666; margin: 5px 0 0 0;">
                Vue d'ensemble complète des utilisateurs et de leur activité
                <?php if ($dateFilter): ?>
                    <span style="background: #007bff; color: white; padding: 2px 8px; border-radius: 4px; margin-left: 10px;">
                        Filtre: <?= htmlspecialchars($dateFilter) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="stats-filters">
            <div class="filter-group">
                <label class="filter-label">Filtre par date</label>
                <select class="filter-select" id="dateFilter" onchange="applyFilters()">
                    <option value="">Toutes les dates</option>
                    <option value="today" <?= $dateFilter === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                    <option value="week" <?= $dateFilter === 'week' ? 'selected' : '' ?>>Cette semaine</option>
                    <option value="month" <?= $dateFilter === 'month' ? 'selected' : '' ?>>Ce mois</option>
                    <option value="year" <?= $dateFilter === 'year' ? 'selected' : '' ?>>Cette année</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques principales sous forme de cercles -->
    <div class="stats-grid">
        <div class="stat-circle-card circle-primary">
            <div class="circle-chart-container">
                <?php 
                $totalUsers = $stats['basic_stats']['total_users'];
                $maxUsers = max($totalUsers, 1);
                $percentage = ($totalUsers / $maxUsers) * 100;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($totalUsers) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Total des utilisateurs</div>
            <div class="circle-chart-description">Tous les comptes enregistrés</div>
            <?php if ($stats['growth_stats']['growth_rate'] !== 0): ?>
                <div class="circle-change <?= $stats['growth_stats']['growth_rate'] > 0 ? 'positive' : ($stats['growth_stats']['growth_rate'] < 0 ? 'negative' : 'neutral') ?>">
                    <?= $stats['growth_stats']['growth_rate'] > 0 ? '+' : '' ?><?= $stats['growth_stats']['growth_rate'] ?>% vs période précédente
                </div>
            <?php endif; ?>
        </div>

        <div class="stat-circle-card circle-success">
            <div class="circle-chart-container">
                <?php 
                $regularUsers = $stats['basic_stats']['total_regular_users'];
                $percentage = ($totalUsers > 0) ? ($regularUsers / $totalUsers) * 100 : 0;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($regularUsers) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Utilisateurs actifs</div>
            <div class="circle-chart-description">Comptes utilisateurs standards</div>
        </div>

        <div class="stat-circle-card circle-warning">
            <div class="circle-chart-container">
                <?php 
                $admins = $stats['basic_stats']['total_admins'];
                $percentage = ($totalUsers > 0) ? ($admins / $totalUsers) * 100 : 0;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($admins) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Administrateurs</div>
            <div class="circle-chart-description">Comptes avec privilèges admin</div>
        </div>

        <div class="stat-circle-card circle-danger">
            <div class="circle-chart-container">
                <?php 
                $blocked = $stats['basic_stats']['total_blocked'];
                $percentage = ($totalUsers > 0) ? ($blocked / $totalUsers) * 100 : 0;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($blocked) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Comptes bloqués</div>
            <div class="circle-chart-description">Accès désactivé</div>
        </div>

        <div class="stat-circle-card circle-info">
            <div class="circle-chart-container">
                <?php 
                $withPhotos = $stats['basic_stats']['with_photos'];
                $percentage = ($totalUsers > 0) ? ($withPhotos / $totalUsers) * 100 : 0;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($withPhotos) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Avec photo de profil</div>
            <div class="circle-chart-description">Utilisateurs avec avatar</div>
        </div>

        <div class="stat-circle-card circle-secondary">
            <div class="circle-chart-container">
                <?php 
                $withQr = $stats['basic_stats']['with_qr_codes'];
                $percentage = ($totalUsers > 0) ? ($withQr / $totalUsers) * 100 : 0;
                $degrees = ($percentage / 100) * 360;
                ?>
                <div class="circle-chart" style="--progress-deg: <?= $degrees ?>deg;">
                    <div class="circle-chart-value"><?= number_format($withQr) ?></div>
                </div>
            </div>
            <div class="circle-chart-label">Avec QR code</div>
            <div class="circle-chart-description">QR codes générés</div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="charts-section">
        <div class="chart-container">
            <h3 class="chart-title">Inscriptions quotidiennes (30 derniers jours)</h3>
            <div class="chart-canvas">
                <div class="bar-chart" id="dailyChart">
                    <?php if (!empty($stats['daily_registrations'])): ?>
                        <?php 
                        $maxCount = max(array_column($stats['daily_registrations'], 'count'));
                        foreach (array_reverse($stats['daily_registrations']) as $registration): 
                            $height = $maxCount > 0 ? ($registration['count'] / $maxCount) * 100 : 0;
                        ?>
                            <div class="bar-item">
                                <div class="bar" style="height: <?= $height ?>%">
                                    <span class="bar-value"><?= $registration['count'] ?></span>
                                </div>
                                <div class="bar-label"><?= date('d/m', strtotime($registration['date'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666;">Aucune donnée disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <h3 class="chart-title">Distribution par rôle</h3>
            <div class="chart-canvas">
                <div class="distribution-list">
                    <?php if (!empty($stats['role_distribution'])): ?>
                        <?php foreach ($stats['role_distribution'] as $role): ?>
                            <div class="distribution-item">
                                <div class="distribution-label">
                                    <?= ucfirst(htmlspecialchars($role['role'])) ?>
                                </div>
                                <div class="distribution-value">
                                    <span class="distribution-count"><?= number_format($role['count']) ?></span>
                                    <span class="distribution-percentage"><?= $role['percentage'] ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666;">Aucune donnée disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution par ville -->
    <?php if (!empty($stats['city_distribution'])): ?>
        <div class="chart-container" style="margin-bottom: 40px;">
            <h3 class="chart-title">Top 10 des villes</h3>
            <div class="chart-canvas">
                <div class="distribution-list">
                    <?php foreach ($stats['city_distribution'] as $city): ?>
                        <div class="distribution-item">
                            <div class="distribution-label">
                                <?= htmlspecialchars($city['city']) ?>
                            </div>
                            <div class="distribution-value">
                                <span class="distribution-count"><?= number_format($city['count']) ?></span>
                                <span class="distribution-percentage"><?= $city['percentage'] ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Inscriptions récentes -->
    <?php if (!empty($stats['recent_registrations'])): ?>
        <div class="recent-registrations">
            <h3 class="chart-title">Inscriptions récentes (7 derniers jours)</h3>
            <div class="table-responsive">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Ville</th>
                            <th>Rôle</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_registrations'] as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($user['city'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="user-role <?= htmlspecialchars($user['role']) ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="loading-spinner" id="loadingSpinner"></div>
</div>

<script>
function applyFilters() {
    const dateFilter = document.getElementById('dateFilter').value;
    const spinner = document.getElementById('loadingSpinner');
    
    // Show loading spinner
    spinner.style.display = 'block';
    
    // Build URL with filters
    const url = new URL(window.location);
    if (dateFilter) {
        url.searchParams.set('date_filter', dateFilter);
    } else {
        url.searchParams.delete('date_filter');
    }
    
    // Redirect to filtered page
    window.location.href = url.toString();
}

// Auto-refresh every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing statistics...');
    // Optional: implement auto-refresh without page reload using AJAX
}, 300000);

// Animate circles and numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    // Animate circle charts
    const circleCharts = document.querySelectorAll('.circle-chart');
    circleCharts.forEach((chart, index) => {
        const targetDegrees = parseFloat(chart.style.getPropertyValue('--progress-deg'));
        chart.style.setProperty('--progress-deg', '0deg');
        
        setTimeout(() => {
            chart.style.transition = 'all 1.5s ease-out';
            chart.style.setProperty('--progress-deg', targetDegrees + 'deg');
        }, index * 200);
    });
    
    // Animate numbers in circles
    const circleValues = document.querySelectorAll('.circle-chart-value');
    circleValues.forEach((element, index) => {
        const finalValue = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        if (!isNaN(finalValue)) {
            element.textContent = '0';
            setTimeout(() => {
                animateValue(element, 0, finalValue, 1500);
            }, index * 200);
        }
    });
    
    // Add hover effects for circles
    const statCards = document.querySelectorAll('.stat-circle-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const chart = this.querySelector('.circle-chart');
            if (chart) {
                chart.style.transform = 'scale(1.05)';
                chart.style.filter = 'brightness(1.1)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const chart = this.querySelector('.circle-chart');
            if (chart) {
                chart.style.transform = 'scale(1)';
                chart.style.filter = 'brightness(1)';
            }
        });
    });
});

function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const updateValue = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.floor(start + (end - start) * progress);
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    };
    requestAnimationFrame(updateValue);
}
</script>

<?php cityzen_render_footer(); ?>
