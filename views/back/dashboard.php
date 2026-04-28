<?php
// ================================================
//  VUE  : views/back/dashboard.php
//  RÔLE : Tableau de bord administrateur
// ================================================

require_once __DIR__ . '/../../models/EventModel.php';
require_once __DIR__ . '/../../models/SponsorModel.php';
require_once __DIR__ . '/../../models/ParticipationModel.php';

$eventModel = new EventModel();
$sponsorModel = new SponsorModel();
$participationModel = new ParticipationModel();

$nbEvents      = $eventModel->count();
$nbSpons       = $sponsorModel->countSponsors();
$nbPartic      = $participationModel->count();
$upcoming7Days = $eventModel->countUpcoming(7);
$eventsWithParticipants = $eventModel->countEventsWithParticipants();
$activeSponsors = $sponsorModel->countActiveSponsors();
$topSponsors   = $eventModel->getTopSponsors(3);
$popularEvents = $eventModel->getPopularEvents(3);
$recents       = $participationModel->findRecent(5);
$msg           = $_GET['msg'] ?? '';
$sent          = (int)($_GET['sent'] ?? 0);
$failed        = (int)($_GET['failed'] ?? 0);

$upcomingPercent = $nbEvents > 0 ? round($upcoming7Days * 100 / $nbEvents) : 0;
$activeSponsorPercent = $nbSpons > 0 ? round($activeSponsors * 100 / $nbSpons) : 0;
$withParticipantsPercent = $nbEvents > 0 ? round($eventsWithParticipants * 100 / $nbEvents) : 0;

$eventsNoParticipants = max(0, $nbEvents - $eventsWithParticipants);
$eventsNoParticipantsPct = $nbEvents > 0 ? round($eventsNoParticipants * 100 / $nbEvents) : 0;

$inactiveSponsors = max(0, $nbSpons - $activeSponsors);
$inactiveSponsorsPct = $nbSpons > 0 ? round($inactiveSponsors * 100 / $nbSpons) : 0;

$participantsRecent = count($recents);
$recentParticipantsPct = $nbPartic > 0 ? round($participantsRecent * 100 / $nbPartic) : 0;
$topEventParticipants = $popularEvents ? $popularEvents[0]['participants'] : 0;
$topEventParticipantsPct = $nbPartic > 0 ? round($topEventParticipants * 100 / $nbPartic) : 0;
$participantsOtherPct = max(0, 100 - $recentParticipantsPct - $topEventParticipantsPct);

$titrePage = 'Dashboard';
require __DIR__ . '/../layouts/back_header.php';
?>

<?php if ($msg === 'rappels'): ?>
    <div class="msg-succes">
        ✅ Rappels envoyés : <?= $sent ?> email(s)
        <?php if ($failed > 0): ?> - <?= $failed ?> échec(s)<?php endif; ?>.
    </div>
<?php endif; ?>

<h1>🏠 Tableau de bord</h1>
<p style="color:#888; margin-bottom:26px;">Bienvenue dans l'interface d'administration CityZen.</p>

<!-- KPI -->
<div class="circle-grid">
    <div class="circle-card">
        <div class="circle-num"><?= $upcoming7Days ?></div>
        <div class="circle-pct"><?= $upcomingPercent ?>%</div>
        <div class="circle-lbl">Événements dans 7 jours</div>
    </div>
    <div class="circle-card orange">
        <div class="circle-num"><?= $activeSponsors ?></div>
        <div class="circle-pct"><?= $activeSponsorPercent ?>%</div>
        <div class="circle-lbl">Sponsors actifs</div>
    </div>
    <div class="circle-card bleu">
        <div class="circle-num"><?= $eventsWithParticipants ?></div>
        <div class="circle-pct"><?= $withParticipantsPercent ?>%</div>
        <div class="circle-lbl">Événements avec participants</div>
    </div>
</div>

<!-- Raccourcis -->
<h2 style="margin-bottom:12px;">⚡ Actions rapides</h2>
<div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:32px;">
    <a href="index.php?page=back_event_ajouter"       class="btn btn-vert">➕ Ajouter un événement</a>
    <a href="index.php?page=back_sponsor_ajouter"     class="btn btn-bleu">➕ Ajouter un sponsor</a>
    <a href="index.php?page=back_participation_ajouter" class="btn btn-orange">➕ Ajouter une participation</a>
    <a href="index.php?page=back_event_envoyer_rappels" class="btn btn-gris">📧 Envoyer rappels</a>
</div>

<div class="kpi-grid" style="margin-bottom:32px;">
    <div class="kpi-card violet">
        <div class="kpi-num"><?= $upcoming7Days ?></div>
        <div class="kpi-lbl">📅 Événements dans 7 jours</div>
    </div>
    <div class="kpi-card vert">
        <div class="kpi-num"><?= $topSponsors ? $topSponsors[0]['event_count'] : 0 ?></div>
        <div class="kpi-lbl">🏆 Sponsor le plus actif</div>
    </div>
    <div class="kpi-card bleu-clair">
        <div class="kpi-num"><?= $popularEvents ? $popularEvents[0]['participants'] : 0 ?></div>
        <div class="kpi-lbl">🔥 Participants max sur un événement</div>
    </div>
</div>

<h2 style="margin-bottom:14px;">📊 Statistiques</h2>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:32px;">
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center; text-align:center;">
        <h3>Événements avec / sans participants</h3>
        <canvas id="eventPieChart" width="260" height="260" style="max-width:260px;"></canvas>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;width:100%;font-size:.95rem;">
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#2f8f4e;display:inline-block;"></span>Avec participants<br><strong><?= $eventsWithParticipants ?> (<?= $withParticipantsPercent ?>%)</strong></div>
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#d9534f;display:inline-block;"></span>Sans participants<br><strong><?= $eventsNoParticipants ?> (<?= $eventsNoParticipantsPct ?>%)</strong></div>
        </div>
    </div>
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center; text-align:center;">
        <h3>Sponsors actifs / inactifs</h3>
        <canvas id="sponsorPieChart" width="260" height="260" style="max-width:260px;"></canvas>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;width:100%;font-size:.95rem;">
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#2f8f4e;display:inline-block;"></span>Actifs<br><strong><?= $activeSponsors ?> (<?= $activeSponsorPercent ?>%)</strong></div>
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#d9534f;display:inline-block;"></span>Inactifs<br><strong><?= $inactiveSponsors ?> (<?= $inactiveSponsorsPct ?>%)</strong></div>
        </div>
    </div>
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center; text-align:center;">
        <h3>Événements dans 7 jours</h3>
        <canvas id="upcomingPieChart" width="260" height="260" style="max-width:260px;"></canvas>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;width:100%;font-size:.95rem;">
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#2f8f4e;display:inline-block;"></span>Dans 7 jours<br><strong><?= $upcoming7Days ?> (<?= $upcomingPercent ?>%)</strong></div>
            <div style="display:flex;align-items:center;gap:8px;justify-content:center;"><span style="width:12px;height:12px;border-radius:50%;background:#d9534f;display:inline-block;"></span>Après 7 jours<br><strong><?= max(0, $nbEvents - $upcoming7Days) ?> (<?= max(0, 100 - $upcomingPercent) ?>%)</strong></div>
        </div>
    </div>
</div>

<!-- Dernières inscriptions -->
<h2 style="margin-bottom:14px;">🕐 Dernières inscriptions</h2>
<table>
    <thead>
        <tr>
            <th>Participant</th>
            <th>Email</th>
            <th>Événement</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($recents)): ?>
            <tr><td colspan="3" style="color:#aaa;">Aucune inscription.</td></tr>
        <?php else: ?>
            <?php foreach ($recents as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nom_participant']) ?></td>
                    <td><?= htmlspecialchars($r['email_participant']) ?></td>
                    <td><?= htmlspecialchars($r['titre_event']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
    function drawPieChart(canvasId, values, colors, labels, centerText) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !canvas.getContext) return;

        const ctx = canvas.getContext('2d');
        const total = values.reduce((sum, v) => sum + v, 0);
        let startAngle = -0.5 * Math.PI;

        values.forEach((value, index) => {
            const sliceAngle = total === 0 ? 2 * Math.PI / values.length : (value / total) * 2 * Math.PI;
            ctx.beginPath();
            ctx.moveTo(canvas.width / 2, canvas.height / 2);
            ctx.arc(canvas.width / 2, canvas.height / 2, Math.min(canvas.width, canvas.height) / 2 - 12, startAngle, startAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = colors[index];
            ctx.fill();

            const midAngle = startAngle + sliceAngle / 2;
            const labelRadius = Math.min(canvas.width, canvas.height) / 2 - 60;
            const labelX = canvas.width / 2 + Math.cos(midAngle) * labelRadius;
            const labelY = canvas.height / 2 + Math.sin(midAngle) * labelRadius;
            const percentText = total === 0 ? '0%' : Math.round((value / total) * 100) + '%';

            ctx.fillStyle = '#fff';
            ctx.font = '600 14px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(percentText, labelX, labelY);

            startAngle += sliceAngle;
        });

        ctx.beginPath();
        ctx.arc(canvas.width / 2, canvas.height / 2, Math.min(canvas.width, canvas.height) / 2 - 22, 0, 2 * Math.PI);
        ctx.fillStyle = '#fff';
        ctx.fill();

        ctx.font = '600 16px Arial';
        ctx.fillStyle = '#333';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(centerText, canvas.width / 2, canvas.height / 2);
    }

    drawPieChart('eventPieChart', [<?= $eventsWithParticipants ?>, <?= $eventsNoParticipants ?>], ['#2f8f4e', '#d9534f'], ['Avec participants', 'Sans participants'], '<?= $nbEvents ?> événements');
    drawPieChart('sponsorPieChart', [<?= $activeSponsors ?>, <?= $inactiveSponsors ?>], ['#2f8f4e', '#d9534f'], ['Actifs', 'Inactifs'], '<?= $nbSpons ?> sponsors');
    drawPieChart('upcomingPieChart', [<?= $upcoming7Days ?>, <?= max(0, $nbEvents - $upcoming7Days) ?>], ['#2f8f4e', '#d9534f'], ['Dans 7 jours', 'Après 7 jours'], '<?= $nbEvents ?> événements');
</script>
<?php require __DIR__ . '/../layouts/back_footer.php'; ?>
