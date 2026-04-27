<?php
// ================================================
//  VUE  : views/front/event/liste.php
//  RÔLE : Afficher tous les événements (public)
// ================================================
$titrePage = 'Événements';
require __DIR__ . '/../../layouts/front_header.php';
?>
<div class="container">
    <h1>🎫 Tous les événements</h1>

    <form method="GET" action="index.php" class="search-form" style="display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:12px;align-items:flex-end;margin-bottom:22px;">
        <input type="hidden" name="page" value="front_event_liste">
        <div>
            <label for="keyword">Recherche</label>
            <input type="search" id="keyword" name="keyword"
                   placeholder="Titre, lieu, sponsor..."
                   value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
        </div>
        <div>
            <label for="lieu">Lieu</label>
            <input type="search" id="lieu" name="lieu"
                   placeholder="Ex : Tunis"
                   value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>">
        </div>
        <div>
            <label for="date_from">Date de</label>
            <input type="date" id="date_from" name="date_from"
                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
        </div>
        <div>
            <label for="date_to">Date à</label>
            <input type="date" id="date_to" name="date_to"
                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </div>
        <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;gap:10px;">
            <button type="submit" class="btn btn-gris">🔎 Rechercher</button>
            <a href="index.php?page=front_event_liste" class="btn btn-rouge">Réinitialiser</a>
        </div>
    </form>

    <div class="cards">
        <?php if (empty($events)): ?>
            <p style="color:#aaa;">Aucun événement disponible.</p>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
