<?php
// ================================================
//  VUE  : views/back/sponsor/liste.php
// ================================================
$titrePage = 'Admin — Sponsors';
require __DIR__ . '/../../layouts/back_header.php';
?>

<div class="back-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h1>💼 Gestion des Sponsors</h1>
        <p style="color:#666;margin-top:4px;">Rechercher un sponsor par nom, email ou téléphone.</p>
    </div>
    <a href="index.php?page=back_sponsor_ajouter" class="btn btn-vert">➕ Ajouter</a>
</div>

<form method="GET" action="index.php" class="search-form">
    <input type="hidden" name="page" value="back_sponsor_liste">
    <div>
        <label for="keyword">Recherche</label>
        <input type="search" id="keyword" name="keyword" placeholder="Nom, email, téléphone"
               value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
    </div>
    <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-gris">🔎 Filtrer</button>
        <a href="index.php?page=back_sponsor_liste" class="btn btn-rouge">Réinitialiser</a>
    </div>
</form>

<?php if ($msg === 'ajoute'):  ?><div class="msg-succes">✅ Sponsor ajouté.</div><?php endif; ?>
<?php if ($msg === 'modifie'): ?><div class="msg-succes">✅ Sponsor modifié.</div><?php endif; ?>
<?php if ($msg === 'supprime'):?><div class="msg-succes">✅ Sponsor supprimé.</div><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>#</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($sponsors)): ?>
            <tr><td colspan="5" style="color:#aaa;">Aucun sponsor.</td></tr>
        <?php else: ?>
            <?php foreach ($sponsors as $sp): ?>
                <tr>
                    <td><?= $sp['id_sponsor'] ?></td>
                    <td><strong><?= htmlspecialchars($sp['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($sp['email']) ?></td>
                    <td><?= htmlspecialchars($sp['telephone']) ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?page=back_sponsor_modifier&id=<?= $sp['id_sponsor'] ?>"
                               class="btn btn-orange">✏ Modifier</a>
                            <a href="index.php?page=back_sponsor_supprimer&id=<?= $sp['id_sponsor'] ?>"
                               class="btn btn-rouge"
                               onclick="return confirm('Supprimer ce sponsor ?');">🗑 Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
