<?php
/**
 * Dashboard Overview - Tableau de bord principal
 */
require_once __DIR__ . '/../../../config/ForumRedirect.php';
require_once __DIR__ . '/../../../models/Report.php';
$controller = new ForumController();
$allPosts = Post::findAll();
$totalPosts = count($allPosts);
$totalReplies = 0;
$totalViews = 0;

foreach ($allPosts as $post) {
    $totalReplies += Reply::countByPostId($post->getId());
    $totalViews += (int)$post->getViewCount();
}

$latestPosts = array_slice($allPosts, 0, 5);
$activePosts = count(array_filter($allPosts, fn($p) => Reply::countByPostId($p->getId()) > 0));
$activeRate = $totalPosts > 0 ? round(($activePosts / $totalPosts) * 100) : 0;
$avgRepliesPerPost = $totalPosts > 0 ? round($totalReplies / $totalPosts, 1) : 0;
$engagementScore = $totalViews > 0 ? min(100, round(($totalReplies / $totalViews) * 100)) : 0;
$openReportsEnriched = Report::getOpenEnriched(50);
$topReasons = Report::getTopReasons(5);
$totalOpenReports = Report::countOpen();

// Grouper les signalements par post
$reportsByPost = [];
foreach ($openReportsEnriched as $report) {
    $postId = (int)$report['post_id'];
    if (!isset($reportsByPost[$postId])) {
        $reportsByPost[$postId] = [
            'post_id' => $postId,
            'title' => $report['title'],
            'content' => $report['content'],
            'author_id' => $report['author_id'],
            'post_status' => $report['post_status'],
            'view_count' => $report['view_count'],
            'report_count' => (int)$report['total_report_count'],
            'reports' => []
        ];
    }
    $reportsByPost[$postId]['reports'][] = [
        'id' => (int)$report['id'],
        'reason' => $report['reason'],
        'reporter_user_id' => (int)$report['reporter_user_id'],
        'created_at' => $report['created_at']
    ];
}
// Trier par nombre de signalements
uasort($reportsByPost, function($a, $b) {
    return $b['report_count'] <=> $a['report_count'];
});

/* Handle resolve_report from this page */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $action = $_POST['action'];
    
    if ($action === 'resolve_report') {
        Report::resolve($itemId);
    } elseif ($action === 'hide_post') {
        Report::hidePost($itemId);
    } elseif ($action === 'resolve_post_reports') {
        Report::resolveAllReportsForPost($itemId);
    } elseif ($action === 'delete_post') {
        $controller->deletePost($itemId);
    }
    header('Location: ' . forum_admin_nav_base() . '?page=dashboard', true, 302);
    exit;
}
?>

<!-- Stats Overview -->
<style>
    .stats-round-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }
    .stat-round-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 14px 14px;
        text-align: left;
        position: relative;
        overflow: hidden;
        box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .stat-round-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    }
    .stat-round-card::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(circle at top right, rgba(46, 204, 113, 0.13), transparent 55%);
    }
    .stat-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    .stat-pill {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--accent);
        background: rgba(52, 73, 94, .10);
        border: 1px solid rgba(52, 73, 94, .15);
        border-radius: 999px;
        padding: 4px 10px;
    }
    .stat-mini {
        font-size: .75rem;
        color: var(--muted);
        font-weight: 600;
        text-align: right;
    }
    .stat-round-ring {
        --pct: 0;
        width: 96px;
        height: 96px;
        border-radius: 50%;
        margin: 0;
        background: conic-gradient(var(--accent) calc(var(--pct) * 1%), #e8edf2 0);
        position: relative;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .stat-round-ring::before {
        content: '';
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid var(--border);
        position: absolute;
    }
    .stat-round-value {
        position: relative;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text);
    }
    .stat-content {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }
    .stat-round-label {
        font-size: .76rem;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
    }
    .stat-main-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
    }
    .stat-sub {
        font-size: .76rem;
        color: var(--muted);
    }
    .stat-meter {
        margin-top: 10px;
        position: relative;
        z-index: 1;
    }
    .stat-meter-track {
        height: 6px;
        border-radius: 999px;
        background: #e9edf2;
        overflow: hidden;
    }
    .stat-meter-fill {
        height: 100%;
        width: 0;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--accent2), #7DDEAD);
    }
    .stat-round-card.posts .stat-round-ring { background: conic-gradient(#34495E calc(var(--pct) * 1%), #e8edf2 0); }
    .stat-round-card.replies .stat-round-ring { background: conic-gradient(#2ECC71 calc(var(--pct) * 1%), #e8edf2 0); }
    .stat-round-card.active .stat-round-ring { background: conic-gradient(#F39C12 calc(var(--pct) * 1%), #e8edf2 0); }
    .stat-round-card.engagement .stat-round-ring { background: conic-gradient(#8E44AD calc(var(--pct) * 1%), #e8edf2 0); }
    .stat-round-card.replies .stat-meter-fill { background: linear-gradient(90deg, #2ECC71, #7DDEAD); }
    .stat-round-card.active .stat-meter-fill { background: linear-gradient(90deg, #F39C12, #F8C471); }
    .stat-round-card.engagement .stat-meter-fill { background: linear-gradient(90deg, #8E44AD, #BB8FCE); }
    @media (max-width: 680px) {
        .stat-content { gap: 10px; }
        .stat-main-value { font-size: 1.25rem; }
    }
</style>

<div class="stats-round-grid">
    <div class="stat-round-card posts">
        <div class="stat-head">
            <span class="stat-pill">Volume</span>
            <span class="stat-mini">Posts publiés</span>
        </div>
        <div class="stat-content">
            <div class="stat-round-ring" style="--pct: <?= min(100, $totalPosts) ?>">
                <div class="stat-round-value"><?php echo $totalPosts; ?></div>
            </div>
            <div>
                <div class="stat-round-label">📝 Total Posts</div>
                <div class="stat-main-value"><?= number_format($totalPosts) ?></div>
                <div class="stat-sub">Base des discussions</div>
            </div>
        </div>
        <div class="stat-meter">
            <div class="stat-meter-track">
                <div class="stat-meter-fill" style="width: <?= min(100, $totalPosts) ?>%"></div>
            </div>
        </div>
    </div>
    <div class="stat-round-card replies">
        <div class="stat-head">
            <span class="stat-pill">Interaction</span>
            <span class="stat-mini">Réponses générées</span>
        </div>
        <div class="stat-content">
            <div class="stat-round-ring" style="--pct: <?= min(100, $totalReplies) ?>">
                <div class="stat-round-value"><?php echo $totalReplies; ?></div>
            </div>
            <div>
                <div class="stat-round-label">💬 Total Réponses</div>
                <div class="stat-main-value"><?= number_format($totalReplies) ?></div>
                <div class="stat-sub">Moyenne: <?= $avgRepliesPerPost ?> / post</div>
            </div>
        </div>
        <div class="stat-meter">
            <div class="stat-meter-track">
                <div class="stat-meter-fill" style="width: <?= min(100, $totalReplies) ?>%"></div>
            </div>
        </div>
    </div>
    <div class="stat-round-card active">
        <div class="stat-head">
            <span class="stat-pill">Couverture</span>
            <span class="stat-mini"><?= $activePosts ?>/<?= $totalPosts ?> actifs</span>
        </div>
        <div class="stat-content">
            <div class="stat-round-ring" style="--pct: <?= min(100, $activeRate) ?>">
                <div class="stat-round-value"><?php echo $activeRate; ?>%</div>
            </div>
            <div>
                <div class="stat-round-label">👥 Taux Posts Actifs</div>
                <div class="stat-main-value"><?= $activeRate ?>%</div>
                <div class="stat-sub">Posts avec au moins 1 réponse</div>
            </div>
        </div>
        <div class="stat-meter">
            <div class="stat-meter-track">
                <div class="stat-meter-fill" style="width: <?= min(100, $activeRate) ?>%"></div>
            </div>
        </div>
    </div>
    <div class="stat-round-card engagement">
        <div class="stat-head">
            <span class="stat-pill">Qualité</span>
            <span class="stat-mini">Basé sur vues/réponses</span>
        </div>
        <div class="stat-content">
            <div class="stat-round-ring" style="--pct: <?= min(100, $engagementScore) ?>">
                <div class="stat-round-value"><?php echo $engagementScore; ?>%</div>
            </div>
            <div>
                <div class="stat-round-label">🔥 Score Engagement</div>
                <div class="stat-main-value"><?= $engagementScore ?>%</div>
                <div class="stat-sub"><?= number_format($totalViews) ?> vues cumulées</div>
            </div>
        </div>
        <div class="stat-meter">
            <div class="stat-meter-track">
                <div class="stat-meter-fill" style="width: <?= min(100, $engagementScore) ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Actions Rapides -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; margin-bottom: 32px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">⚡ Actions rapides</h3>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="?page=posts" class="header-btn">➕ Gérer les Posts</a>
        <a href="?page=replies" class="header-btn">➕ Gérer les Réponses</a>
        <a href="?page=statistics" class="header-btn">➕ Voir Statistiques</a>
    </div>
</div>

<!-- ── Signalements AVANCÉS ── -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:28px;">
    <div style="padding:16px 20px;font-weight:700;font-size:.95rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
        <div>🛡️ Modération avancée — Signalements</div>
        <div style="font-size:.75rem;color:var(--muted);font-weight:500;">Total: <strong style="color:#c0392b;"><?= $totalOpenReports ?></strong></div>
    </div>

    <?php if ($totalOpenReports === 0): ?>
        <div style="padding:40px 20px;text-align:center;color:var(--text-secondary);">
            <div style="font-size:3rem;margin-bottom:16px;">✨</div>
            <p style="font-size:1rem;color:var(--accent);font-weight:600;">Excellent ! Aucun signalement en attente.</p>
            <p style="font-size:.85rem;color:var(--text-secondary);margin-top:6px;">Votre forum est bien modéré.</p>
        </div>
    <?php else: ?>
        <!-- Stats des signalements -->
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
            <div style="background:#FEF3E2;border-radius:8px;padding:12px;border-left:4px solid var(--accent-orange);">
                <div style="font-size:.7rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Total signalements</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--accent-orange);"><?= $totalOpenReports ?></div>
            </div>
            <div style="background:#E3F2FD;border-radius:8px;padding:12px;border-left:4px solid var(--accent-blue);">
                <div style="font-size:.7rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Posts signalés</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--accent-blue);"><?= count($reportsByPost) ?></div>
            </div>
            <div style="background:#F0F4F8;border-radius:8px;padding:12px;border-left:4px solid var(--muted);">
                <div style="font-size:.7rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Raison majeure</div>
                <div style="font-size:.85rem;font-weight:600;color:var(--text);">
                    <?= !empty($topReasons) ? htmlspecialchars((string)($topReasons[0]['reason'] ?? '')) : '—' ?>
                </div>
            </div>
        </div>

        <!-- Posts signalés - Vue détaillée -->
        <div style="padding:16px 20px;">
            <?php 
            foreach ($reportsByPost as $postId => $post):
            ?>
                <div style="border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:12px;background:#fafbfc;">
                    <!-- En-tête du post -->
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px;">
                        <div style="flex:1;">
                            <div style="font-weight:700;color:var(--text);font-size:.92rem;margin-bottom:4px;">
                                <a href="<?= htmlspecialchars(forum_list_url('page=post&id=' . $postId)) ?>" style="color:var(--accent);text-decoration:none;">
                                    #<?= $postId ?> — <?= htmlspecialchars(substr((string)$post['title'], 0, 60)) ?>
                                </a>
                            </div>
                            <div style="font-size:.75rem;color:var(--text-secondary);">
                                👤 Auteur #<?= $post['author_id'] ?> • 
                                👁️ <?= $post['view_count'] ?> vues • 
                                <span style="padding:2px 6px;background:<?= $post['post_status'] === 'Masqué' ? 'rgba(231,76,60,.15)' : 'rgba(67,233,123,.15)' ?>;border-radius:4px;color:<?= $post['post_status'] === 'Masqué' ? '#c0392b' : 'var(--accent)' ?>;font-size:.68rem;font-weight:600;">
                                    <?= htmlspecialchars((string)$post['post_status']) ?>
                                </span>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:1.6rem;font-weight:800;color:#c0392b;"><?= $post['report_count'] ?></div>
                            <div style="font-size:.68rem;color:var(--text-secondary);text-transform:uppercase;font-weight:600;">Signalements</div>
                        </div>
                    </div>

                    <!-- Extrait du contenu -->
                    <div style="padding:10px 12px;background:#fff;border-radius:6px;border-left:3px solid var(--accent-orange);margin-bottom:10px;font-size:.78rem;color:var(--text);line-height:1.4;">
                        <?= htmlspecialchars(substr((string)$post['content'], 0, 140)) ?><?= strlen((string)$post['content']) > 140 ? '...' : '' ?>
                    </div>

                    <!-- Motifs de signalement -->
                    <div style="margin-bottom:10px;">
                        <div style="font-size:.7rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:5px;">Raisons rapportées :</div>
                        <div style="display:flex;flex-wrap:wrap;gap:5px;">
                            <?php 
                            $reasons = [];
                            foreach ($post['reports'] as $report) {
                                $reason = htmlspecialchars((string)$report['reason']);
                                if (!isset($reasons[$reason])) {
                                    $reasons[$reason] = 0;
                                }
                                $reasons[$reason]++;
                            }
                            foreach ($reasons as $reason => $count): 
                            ?>
                                <span style="display:inline-flex;align-items:center;gap:3px;padding:4px 8px;background:#fff;border:1px solid var(--border);border-radius:16px;font-size:.72rem;font-weight:600;color:var(--text);">
                                    🏷️ <?= $reason ?> <span style="background:#c0392b;color:#fff;padding:0 5px;border-radius:8px;font-size:.65rem;font-weight:700;margin-left:1px;"><?= $count ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Bouton afficher détails -->
                    <div style="margin-bottom:10px;">
                        <button onclick="toggleReportDetails(<?= $postId ?>)" style="background:none;border:none;color:var(--accent);font-size:.73rem;font-weight:600;cursor:pointer;text-decoration:underline;font-family:inherit;">
                            📋 Afficher les <?= count($post['reports']) ?> signalement<?= count($post['reports']) > 1 ? 's' : '' ?> →
                        </button>
                        <div id="reports-<?= $postId ?>" style="display:none;margin-top:8px;padding:10px;background:#fff;border-radius:6px;border:1px solid var(--border);font-size:.77rem;">
                            <?php foreach ($post['reports'] as $idx => $report): ?>
                                <div style="padding:6px 0;border-bottom:<?= $idx < count($post['reports']) - 1 ? '1px solid var(--border)' : 'none' ?>;">
                                    <div style="color:var(--text-secondary);font-size:.72rem;">👤 #<?= $report['reporter_user_id'] ?> • 📅 <?= date('d/m/Y H:i', strtotime((string)$report['created_at'])) ?></div>
                                    <div style="color:var(--text);margin-top:2px;">📌 <?= htmlspecialchars((string)$report['reason']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:10px;">
                        <a href="<?= htmlspecialchars(forum_list_url('page=post&id=' . $postId)) ?>" class="action-btn" style="background:rgba(79,142,247,.1);color:var(--accent-blue);border:1px solid rgba(79,142,247,.2);padding:6px 12px;font-size:.75rem;text-decoration:none;">👁️ Voir</a>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="resolve_post_reports">
                            <input type="hidden" name="item_id" value="<?= $postId ?>">
                            <button type="submit" class="action-btn" style="background:rgba(67,233,123,.1);color:var(--accent);border:1px solid rgba(67,233,123,.2);padding:6px 12px;font-size:.75rem;">✅ Rejeter</button>
                        </form>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="hide_post">
                            <input type="hidden" name="item_id" value="<?= $postId ?>">
                            <button type="submit" class="action-btn" style="background:rgba(247,151,30,.1);color:var(--accent-orange);border:1px solid rgba(247,151,30,.2);padding:6px 12px;font-size:.75rem;">🙈 Masquer</button>
                        </form>

                        <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ? Cette action est définitive.');">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="item_id" value="<?= $postId ?>">
                            <button type="submit" class="action-btn danger" style="background:rgba(231,76,60,.1);color:#c0392b;border:1px solid rgba(231,76,60,.2);padding:6px 12px;font-size:.75rem;">🗑️ Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            function toggleReportDetails(postId) {
                const el = document.getElementById('reports-' + postId);
                if (el) {
                    el.style.display = el.style.display === 'none' ? 'block' : 'none';
                }
            }
        </script>
    <?php endif; ?>
</div>

<!-- Derniers Posts -->
<h3 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">📊 Derniers Posts</h3>
<form method="POST" action="<?= htmlspecialchars(forum_admin_nav_base(), ENT_QUOTES, 'UTF-8') ?>?page=dashboard" id="dashboardBulkForm">
<div id="dashboardBulkBar" style="display:none; align-items:center; gap:10px; margin-bottom:12px; background:var(--surface); padding:10px; border:1px solid var(--border); border-radius:6px;">
    <span style="font-size:0.85rem; font-weight:600;"><span id="dashSelectedCount">0</span> sélectionné(s)</span>
    <select name="bulk_action" style="padding:6px; border:1px solid var(--border); border-radius:4px; font-size:0.85rem;">
        <option value="">-- Action --</option>
        <option value="bulk_delete">🗑️ Supprimer</option>
        <option value="bulk_feature">✅ Approuver</option>
    </select>
    <button type="submit" class="header-btn" style="padding:6px 12px; font-size:0.8rem; height:auto; border:none; border-radius:4px;" onclick="return confirm('Appliquer cette action en masse ?')">Appliquer</button>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width:30px;"><input type="checkbox" id="dashSelectAll" style="cursor:pointer;"></th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Réponses</th>
                <th>Vues</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($latestPosts) {
                foreach ($latestPosts as $post) {
                    $replies = Reply::findByPostId($post->getId());
                    $replyCount = count($replies);
                    ?>
                    <tr>
                        <td><input type="checkbox" name="post_ids[]" value="<?= $post->getId() ?>" class="dash-post-cb" style="cursor:pointer;"></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars(substr($post->getTitle(), 0, 40)); ?></td>
                        <td><span class="badge blue">Utilisateur #<?php echo $post->getUserId(); ?></span></td>
                        <td><?php echo $replyCount; ?></td>
                        <td><?php echo $post->getViewCount(); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($post->getCreatedAt())); ?></td>
                        <td>
                            <a href="<?= htmlspecialchars(forum_admin_nav_base(), ENT_QUOTES, 'UTF-8') ?>?page=edit_post&id=<?= (int)$post->getId() ?>" class="action-btn" style="display:inline-block;text-decoration:none;text-align:center;">✏️ Éditer</a>
                            <form method="POST" action="<?= htmlspecialchars(forum_admin_nav_base(), ENT_QUOTES, 'UTF-8') ?>?page=dashboard" style="display:inline;" onsubmit="return confirm('Supprimer ce post ?')">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="item_id" value="<?= (int)$post->getId() ?>">
                                <button type="submit" class="action-btn danger" style="border:none;cursor:pointer;">🗑️ Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="7" style="text-align: center; color: var(--muted);">Aucun post pour le moment</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
</form>

<script>
    const dashSelectAll = document.getElementById('dashSelectAll');
    const dashCbs = document.querySelectorAll('.dash-post-cb');
    const dashBulkBar = document.getElementById('dashboardBulkBar');
    const dashCountEl = document.getElementById('dashSelectedCount');

    function updateDashBulk() {
        const checked = document.querySelectorAll('.dash-post-cb:checked').length;
        dashCountEl.textContent = checked;
        dashBulkBar.style.display = checked > 0 ? 'flex' : 'none';
    }

    if(dashSelectAll) {
        dashSelectAll.addEventListener('change', e => {
            dashCbs.forEach(cb => cb.checked = e.target.checked);
            updateDashBulk();
        });
    }

    dashCbs.forEach(cb => cb.addEventListener('change', () => {
        dashSelectAll.checked = document.querySelectorAll('.dash-post-cb:checked').length === dashCbs.length;
        updateDashBulk();
    }));
</script>
