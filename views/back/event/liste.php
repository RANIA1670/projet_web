<?php
// ================================================
//  VUE  : views/back/event/liste.php
//  RÔLE : Liste admin des événements + CRUD links
// ================================================
$titrePage = 'Admin — Événements';
require __DIR__ . '/../../layouts/back_header.php';
?>

<div class="back-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1>🎫 Gestion des Événements</h1>
        <p style="color:#666;margin:4px 0 0;">Filtrer les événements par titre, sponsor, lieu et dates.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="index.php?page=back_event_ajouter" class="btn btn-vert">➕ Ajouter</a>
        <a href="index.php?page=back_event_export_pdf&<?= http_build_query($filters) ?>" class="btn btn-bleu">📄 Export PDF</a>
    </div>
</div>

<form method="GET" action="index.php" class="search-form" style="display:grid;grid-template-columns:repeat(5,minmax(160px,1fr));gap:12px;align-items:flex-end;margin-bottom:18px;">
    <input type="hidden" name="page" value="back_event_liste">

    <div>
        <label for="keyword">Recherche</label>
        <input type="search" id="keyword" name="keyword"
               placeholder="Titre, sponsor, lieu, description"
               value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
    </div>

    <div>
        <label for="id_sponsor">Sponsor</label>
        <select id="id_sponsor" name="id_sponsor">
            <option value="0">Tous les sponsors</option>
            <?php foreach ($sponsors as $sp): ?>
                <option value="<?= $sp['id_sponsor'] ?>"
                    <?= ((int)($filters['id_sponsor'] ?? 0) === $sp['id_sponsor']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sp['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
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

    <div>
        <label for="lieu">Lieu</label>
        <input type="search" id="lieu" name="lieu"
               placeholder="Ville, salle..."
               value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>">
    </div>

    <div style="grid-column:1 / -1;display:flex;gap:10px;justify-content:flex-end;">
        <button type="submit" class="btn btn-gris">🔎 Filtrer</button>
        <a href="index.php?page=back_event_liste" class="btn btn-rouge">Réinitialiser</a>
    </div>
</form>

<?php if ($msg === 'ajoute'):  ?><div class="msg-succes">✅ Événement ajouté avec succès.</div><?php endif; ?>
<?php if ($msg === 'modifie'): ?><div class="msg-succes">✅ Événement modifié avec succès.</div><?php endif; ?>
<?php if ($msg === 'supprime'):?><div class="msg-succes">✅ Événement supprimé avec succès.</div><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Titre</th>
            <th>Date</th>
            <th>Lieu</th>
            <th>Sponsor</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($events)): ?>
            <tr><td colspan="6" style="color:#aaa;">Aucun événement.</td></tr>
        <?php else: ?>
            <?php foreach ($events as $ev): ?>
                <tr>
                    <td><?= $ev['id_event'] ?></td>
                    <td><strong><?= htmlspecialchars($ev['titre']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($ev['date_event'])) ?></td>
                    <td><?= htmlspecialchars($ev['lieu']) ?></td>
                    <td><span class="sponsor-badge"><?= htmlspecialchars($ev['nom_sponsor']) ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?page=back_event_modifier&id=<?= $ev['id_event'] ?>"
                               class="btn btn-orange">✏ Modifier</a>
                            <a href="index.php?page=back_event_supprimer&id=<?= $ev['id_event'] ?>"
                               class="btn btn-rouge"
                               onclick="return confirm('Supprimer cet événement et toutes ses participations ?');">
                               🗑 Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
