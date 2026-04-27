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
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-bottom:32px;">
    <div class="card">
        <h3>Top sponsors</h3>
        <?php if (empty($topSponsors)): ?>
            <p style="color:#888;">Aucun sponsor lié à un événement.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($topSponsors as $rank => $sponsor): ?>
                    <li><?= ($rank + 1) ?>. <strong><?= htmlspecialchars($sponsor['nom']) ?></strong> — <?= $sponsor['event_count'] ?> événement(s)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="card">
        <h3>Événements les plus populaires</h3>
        <?php if (empty($popularEvents)): ?>
            <p style="color:#888;">Aucun événement n'a de participations encore.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($popularEvents as $event): ?>
                    <li><strong><?= htmlspecialchars($event['titre']) ?></strong> — <?= $event['participants'] ?> participant(s)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
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

<?php require __DIR__ . '/../layouts/back_footer.php'; ?>
