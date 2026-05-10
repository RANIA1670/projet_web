<?php
// ================================================
//  VUE  : views/back/sponsor/form.php
//  RÔLE : Formulaire ajout + modification sponsor
//         PAS de validation HTML5
// ================================================
$titrePage = isset($modeEdit) ? 'Modifier un sponsor' : 'Ajouter un sponsor';
require __DIR__ . '/../../layouts/back_header.php';
?>

<a href="index.php?page=back_sponsor_liste" class="back-link">← Retour</a>
<h1><?= isset($modeEdit) ? '✏ Modifier le sponsor' : '➕ Ajouter un sponsor' ?></h1>

<?php if (!empty($erreurs)): ?>
    <div class="msg-erreur">
        <strong>⚠ Erreurs :</strong>
        <ul>
            <?php foreach ($erreurs as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-box">
    <form method="POST"
          action="index.php?page=<?= isset($modeEdit) ? 'back_sponsor_modifier&id='.$sponsor['id_sponsor'] : 'back_sponsor_ajouter' ?>">

        <label for="nom">Nom du sponsor *</label>
        <input type="text" id="nom" name="nom"
               placeholder="Ex : Orange Tunisie"
               value="<?= htmlspecialchars($nom ?? '') ?>">

        <label for="email">Email *</label>
        <!-- type="text" intentionnel — validation PHP, pas HTML5 -->
        <input type="text" id="email" name="email"
               placeholder="Ex : contact@orange.tn"
               value="<?= htmlspecialchars($email ?? '') ?>">

        <label for="telephone">Téléphone *</label>
        <input type="text" id="telephone" name="telephone"
               placeholder="Ex : +216 71 700 000"
               value="<?= htmlspecialchars($telephone ?? '') ?>">

        <div style="display:flex; gap:10px; margin-top:4px;">
            <button type="submit" class="btn <?= isset($modeEdit) ? 'btn-orange' : 'btn-vert' ?>">
                💾 <?= isset($modeEdit) ? 'Enregistrer les modifications' : 'Ajouter le sponsor' ?>
            </button>
            <a href="index.php?page=back_sponsor_liste" class="btn btn-gris">Annuler</a>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
