<?php
/**
 * Dashboard Statistics - Statistiques détaillées
 */

$allPosts = Post::findAll();
$totalViews = 0;
$postsWithMostReplies = [];

foreach ($allPosts as $post) {
    $totalViews += $post->getViewCount();
    $replies = Reply::findByPostId($post->getId());
    $postsWithMostReplies[] = [
        'post' => $post,
        'reply_count' => count($replies)
    ];
}

// Trier par nombre de réponses
usort($postsWithMostReplies, function($a, $b) {
    return $b['reply_count'] <=> $a['reply_count'];
});

$topPosts = array_slice($postsWithMostReplies, 0, 5);
$avgViewsPerPost = count($allPosts) > 0 ? round($totalViews / count($allPosts)) : 0;
$avgRepliesPerPost = count($allPosts) > 0 ? round(array_sum(array_column($postsWithMostReplies, 'reply_count')) / count($allPosts)) : 0;
$activePostsCount = count(array_filter($postsWithMostReplies, fn($p) => $p['reply_count'] > 0));
$activePostRate = count($allPosts) > 0 ? round(($activePostsCount / count($allPosts)) * 100) : 0;
?>

<style>
    .round-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .round-stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 14px;
        text-align: center;
    }
    .round-ring {
        --pct: 0;
        width: 118px;
        height: 118px;
        border-radius: 50%;
        margin: 0 auto 10px;
        background: conic-gradient(var(--accent) calc(var(--pct) * 1%), #e8edf2 0);
        position: relative;
        display: grid;
        place-items: center;
    }
    .round-ring::before {
        content: '';
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid var(--border);
        position: absolute;
    }
    .round-ring-value {
        position: relative;
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--text);
    }
    .round-stat-title {
        font-size: .78rem;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
</style>

<div class="round-stats-grid">
    <div class="round-stat-card">
        <div class="round-ring" style="--pct: <?= min(100, $avgViewsPerPost) ?>">
            <span class="round-ring-value"><?php echo $totalViews; ?></span>
        </div>
        <div class="round-stat-title">👀 Total Vues</div>
    </div>
    <div class="round-stat-card">
        <div class="round-ring" style="--pct: <?= min(100, $avgViewsPerPost) ?>">
            <span class="round-ring-value"><?php echo $avgViewsPerPost; ?></span>
        </div>
        <div class="round-stat-title">📊 Vues Moy/Post</div>
    </div>
    <div class="round-stat-card">
        <div class="round-ring" style="--pct: <?= min(100, $avgRepliesPerPost * 10) ?>">
            <span class="round-ring-value"><?php echo $avgRepliesPerPost; ?></span>
        </div>
        <div class="round-stat-title">💬 Réponses Moy/Post</div>
    </div>
    <div class="round-stat-card">
        <div class="round-ring" style="--pct: <?= min(100, $activePostRate) ?>">
            <span class="round-ring-value"><?php echo $activePostRate; ?>%</span>
        </div>
        <div class="round-stat-title">🏆 Taux de Posts Actifs</div>
    </div>
</div>

<h3 style="font-size: 18px; font-weight: 700; margin: 32px 0 16px 0;">🏆 Posts les plus actifs</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Réponses</th>
                <th>Vues</th>
                <th>Ratio Réponses/Vues</th>
                <th>Créé le</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($topPosts) {
                foreach ($topPosts as $item) {
                    $post = $item['post'];
                    $replyCount = $item['reply_count'];
                    $viewCount = $post->getViewCount();
                    $ratio = $viewCount > 0 ? round(($replyCount / $viewCount) * 100, 2) : 0;
                    ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($post->getTitle()); ?></td>
                        <td><span class="badge orange"><?php echo $replyCount; ?></span></td>
                        <td><?php echo $viewCount; ?></td>
                        <td><?php echo $ratio; ?>%</td>
                        <td><?php echo date('d/m/Y', strtotime($post->getCreatedAt())); ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="5" style="text-align: center; color: var(--muted);">Aucune donnée</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
