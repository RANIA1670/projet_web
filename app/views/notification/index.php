<!-- ========== MES NOTIFICATIONS ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Mes notifications</span>
        </div>
        <h1><i class="fas fa-bell" style="color:var(--secondary);"></i> Mes Notifications</h1>
        <p>Suivez les mises à jour de vos signalements et interventions.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:800px;">
        
        <!-- Bouton marquer comme lues -->
        <?php if (!empty($notifications)): ?>
        <div style="margin-bottom:20px; display:flex; justify-content:flex-end;">
            <button onclick="markAllAsRead()" class="btn btn-outline">
                <i class="fas fa-check-double"></i> Marquer tout comme lu
            </button>
        </div>
        <?php endif; ?>

        <!-- Liste des notifications -->
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($notifications)): ?>
                <div style="background:#F8F9FA; border:1px solid var(--border-color); border-radius:8px; padding:40px; text-align:center;">
                    <i class="fas fa-inbox" style="font-size:2.5rem; color:var(--text-muted); margin-bottom:12px; display:block;"></i>
                    <h3 style="margin:0; color:var(--text-muted);">Aucune notification</h3>
                    <p style="margin:8px 0 0; color:var(--text-muted); font-size:0.9rem;">Vous êtes à jour !</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="notification-card" data-id="<?= $notif['id'] ?>" style="background:white; border:1px solid var(--border-color); border-radius:8px; padding:16px; display:flex; gap:16px; cursor:pointer; transition:background 0.2s; border-left:4px solid <?= $notif['lue'] ? 'var(--border-color)' : 'var(--secondary)' ?>;" 
                     onclick="markNotificationAsRead(<?= $notif['id'] ?>)">
                    
                    <!-- Icône par type -->
                    <div style="width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;
                                background: <?= match($notif['type']) {
                                    'new_signalement' => '#FFE8E8',
                                    'status_change' => '#E3F2FD',
                                    'new_intervention' => '#F3E5F5',
                                    default => '#F5F5F5'
                                } ?>;">
                        <?= match($notif['type']) {
                            'new_signalement' => '🔴',
                            'status_change' => '✏️',
                            'new_intervention' => '🔧',
                            default => '📬'
                        } ?>
                    </div>

                    <!-- Contenu -->
                    <div style="flex:1;">
                        <h4 style="margin:0 0 6px; font-size:0.95rem; font-weight:600; <?= !$notif['lue'] ? 'color:var(--text-dark)' : 'color:var(--text-muted)' ?>">
                            <?= htmlspecialchars($notif['titre']) ?>
                        </h4>
                        <p style="margin:0 0 8px; font-size:0.9rem; color:var(--text-muted);">
                            <?= htmlspecialchars($notif['message'] ?? '') ?>
                        </p>
                        <p style="margin:0; font-size:0.8rem; color:var(--text-muted);">
                            <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                        </p>
                    </div>

                    <!-- Statut de lecture -->
                    <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <?php if (!$notif['lue']): ?>
                            <div style="width:8px; height:8px; background:var(--secondary); border-radius:50%;"></div>
                        <?php endif; ?>
                        <button onclick="event.stopPropagation(); markNotificationAsRead(<?= $notif['id'] ?>)" class="btn btn-sm btn-outline" style="padding:4px 8px; font-size:0.75rem;">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($page > 1 || count($notifications) === 20): ?>
        <div style="margin-top:32px; text-align:center; display:flex; gap:12px; justify-content:center;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline">
                    <i class="fas fa-chevron-left"></i> Précédent
                </a>
            <?php endif; ?>
            <span style="align-self:center; color:var(--text-muted);">Page <?= $page ?></span>
            <?php if (count($notifications) === 20): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">
                    Suivant <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<script>
async function markNotificationAsRead(notificationId) {
    try {
        const response = await fetch('<?= APP_URL ?>/api/notifications/mark-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + notificationId
        });
        
        if (response.ok) {
            const card = document.querySelector(`[data-id="${notificationId}"]`);
            if (card) {
                card.style.borderLeft = '4px solid var(--border-color)';
                const badge = card.querySelector('[style*="width:8px"]');
                if (badge) badge.remove();
            }
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('<?= APP_URL ?>/api/notifications/mark-all', {
            method: 'POST'
        });
        
        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}
</script>
