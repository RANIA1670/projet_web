<?php
// ================================================
//  VUE  : views/back/participation/liste.php
// ================================================
$titrePage = 'Admin — Participations';
require __DIR__ . '/../../layouts/back_header.php';
?>

<div class="back-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h1>👥 Gestion des Participations</h1>
        <p style="color:#666;margin-top:4px;">Recherche par participant, email, événement ou dates.</p>
    </div>
    <a href="index.php?page=back_participation_ajouter" class="btn btn-vert">➕ Ajouter</a>
</div>

<form method="GET" action="index.php" class="search-form">
    <input type="hidden" name="page" value="back_participation_liste">
    <div>
        <label for="keyword">Recherche</label>
        <input type="search" id="keyword" name="keyword" placeholder="Nom, email ou événement"
               value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
    </div>
    <div>
        <label for="id_event">Événement</label>
        <select id="id_event" name="id_event">
            <option value="0">Tous les événements</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= $ev['id_event'] ?>"
                    <?= ((int)($filters['id_event'] ?? 0) === $ev['id_event']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev['titre']) ?>
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
    <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-gris">🔎 Filtrer</button>
        <a href="index.php?page=back_participation_liste" class="btn btn-rouge">Réinitialiser</a>
    </div>
</form>

<?php if ($msg === 'ajoute'):  ?><div class="msg-succes">✅ Participation ajoutée.</div><?php endif; ?>
<?php if ($msg === 'modifie'): ?><div class="msg-succes">✅ Participation modifiée.</div><?php endif; ?>
<?php if ($msg === 'supprime'):?><div class="msg-succes">✅ Participation supprimée.</div><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Participant</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Âge</th>
            <th>Sexe</th>
            <th>Événement</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($participations)): ?>
            <tr><td colspan="8" style="color:#aaa;">Aucune participation.</td></tr>
        <?php else: ?>
            <?php foreach ($participations as $p): ?>
                <tr>
                    <td><?= $p['id_participation'] ?></td>
                    <td><strong><?= htmlspecialchars($p['nom_participant']) ?></strong></td>
                    <td><?= htmlspecialchars($p['email_participant']) ?></td>
                    <td><?= htmlspecialchars($p['numero_participant'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['age_participant'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['sexe_participant'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['titre_event']) ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?page=back_participation_modifier&id=<?= $p['id_participation'] ?>"
                               class="btn btn-orange">✏ Modifier</a>
                            <a href="index.php?page=back_participation_supprimer&id=<?= $p['id_participation'] ?>"
                               class="btn btn-rouge"
                               onclick="return confirm('Supprimer cette participation ?');">🗑 Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
