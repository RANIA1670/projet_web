<?php
// ================================================
//  VUE  : views/front/accueil.php
//  RÔLE : Page d'accueil publique — Front Office
// ================================================

// Charger les modèles pour afficher des stats
require_once __DIR__ . '/../../models/EventModel.php';
require_once __DIR__ . '/../../models/SponsorModel.php';
require_once __DIR__ . '/../../models/ParticipationModel.php';

$eventM  = new EventModel();
$sponsM  = new SponsorModel();
$participM = new ParticipationModel();

$events   = $eventM->findAll();           // Tous les events
$nbEvents = $eventM->count();
$nbPartic = $participM->count();
$nbSpons  = count($sponsM->findAll());

$titrePage = 'Accueil';
require __DIR__ . '/../layouts/front_header.php';
?>

<!-- HERO -->
<div class="hero">
    <h1>Votre ville, <span>vos actions</span></h1>
    <p>Découvrez les prochains événements de votre ville et inscrivez-vous en quelques secondes.</p>
    <form id="home-search" method="GET" action="index.php" class="hero-search" style="margin-top:24px;display:grid;grid-template-columns:repeat(5,minmax(150px,1fr));gap:12px;align-items:flex-end;">
        <input type="hidden" name="page" value="front_event_liste">
        <div>
            <label for="keyword">Recherche</label>
            <input type="search" id="keyword" name="keyword" placeholder="Titre, lieu, sponsor..." style="width:100%;"
                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        </div>
        <div>
            <label for="lieu">Lieu</label>
            <input type="search" id="lieu" name="lieu" placeholder="Ex : Tunis" style="width:100%;"
                   value="<?= htmlspecialchars($_GET['lieu'] ?? '') ?>">
        </div>
        <div>
            <label for="date_from">Date de</label>
            <input type="date" id="date_from" name="date_from" style="width:100%;"
                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </div>
        <div>
            <label for="date_to">Date à</label>
            <input type="date" id="date_to" name="date_to" style="width:100%;"
                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="submit" class="btn btn-vert">🔎 Rechercher</button>
            <a href="index.php" class="btn btn-gris">Réinitialiser</a>
        </div>
    </form>
    <div class="hero-stats">
        <div>
            <span class="hstat-num"><?= $nbEvents ?></span>
            <span class="hstat-lbl">Événements</span>
        </div>
        <div>
            <span class="hstat-num"><?= $nbSpons ?></span>
            <span class="hstat-lbl">Sponsors</span>
        </div>
        <div>
            <span class="hstat-num"><?= $nbPartic ?></span>
            <span class="hstat-lbl">Inscrits</span>
        </div>
    </div>
</div>

<div class="container">
    <h1>📅 Prochains événements</h1>

    <div class="cards">
        <?php if (empty($events)): ?>
            <p style="color:#aaa;">Aucun événement pour le moment.</p>
        <?php endif; ?>

        <?php foreach ($events as $ev): ?>
            <div class="card">
                <h3><?= htmlspecialchars($ev['titre']) ?></h3>
                <p><?= htmlspecialchars(mb_substr($ev['description'], 0, 90)) ?>…</p>
                <p class="date">📅 <?= date('d/m/Y', strtotime($ev['date_event'])) ?></p>
                <p class="lieu">📍 <?= htmlspecialchars($ev['lieu']) ?></p>
                <p style="margin-top:8px;">
                    <span class="sponsor-badge">💼 <?= htmlspecialchars($ev['nom_sponsor']) ?></span>
                </p>
                <a href="index.php?page=front_event_detail&id=<?= $ev['id_event'] ?>"
                   class="btn btn-bleu" style="margin-top:12px;">Voir + S'inscrire →</a>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:28px;">
        <a href="index.php?page=front_event_liste" class="btn btn-vert">📋 Tous les événements</a>
        <a href="index.php?page=front_sponsor_liste" class="btn btn-gris" style="margin-left:10px;">💼 Nos sponsors</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
