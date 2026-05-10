<?php
// ================================================
//  VUE  : views/back/participation/form.php
//  RÔLE : Formulaire ajout + modification participation
//         PAS de validation HTML5
// ================================================
$titrePage = isset($modeEdit) ? 'Modifier une participation' : 'Ajouter une participation';
require __DIR__ . '/../../layouts/back_header.php';
?>

<a href="index.php?page=back_participation_liste" class="back-link">← Retour</a>
<h1><?= isset($modeEdit) ? '✏ Modifier la participation' : '➕ Ajouter une participation' ?></h1>

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
          action="index.php?page=<?= isset($modeEdit) ? 'back_participation_modifier&id='.$participation['id_participation'] : 'back_participation_ajouter' ?>">

        <label for="nom_participant">Nom du participant *</label>
        <input type="text" id="nom_participant" name="nom_participant"
               placeholder="Ex : Ali Ben Salah"
               value="<?= htmlspecialchars($nom_participant ?? '') ?>">

        <label for="email_participant">Email *</label>
        <!-- type="text" — validation faite en PHP uniquement -->
        <input type="text" id="email_participant" name="email_participant"
               placeholder="Ex : ali@email.com"
               value="<?= htmlspecialchars($email_participant ?? '') ?>">

        <label for="numero_participant">Téléphone *</label>
        <input type="text" id="numero_participant" name="numero_participant"
               placeholder="Ex : +216 71 000 000"
               value="<?= htmlspecialchars($numero_participant ?? '') ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label for="age_participant">Âge *</label>
                <input type="text" id="age_participant" name="age_participant"
                       placeholder="Ex : 28"
                       value="<?= htmlspecialchars($age_participant ?? '') ?>">
            </div>
            <div>
                <label for="sexe_participant">Sexe *</label>
                <select id="sexe_participant" name="sexe_participant">
                    <option value="">-- Choisir --</option>
                    <option value="Homme" <?= (isset($sexe_participant) && $sexe_participant === 'Homme') ? 'selected' : '' ?>>Homme</option>
                    <option value="Femme" <?= (isset($sexe_participant) && $sexe_participant === 'Femme') ? 'selected' : '' ?>>Femme</option>
                    <option value="Autre" <?= (isset($sexe_participant) && $sexe_participant === 'Autre') ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
        </div>

        <label for="id_event">Événement *</label>
        <select id="id_event" name="id_event">
            <option value="0">-- Choisir un événement --</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= $ev['id_event'] ?>"
                    <?= (isset($id_event) && $id_event == $ev['id_event']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display:flex; gap:10px; margin-top:4px;">
            <button type="submit" class="btn <?= isset($modeEdit) ? 'btn-orange' : 'btn-vert' ?>">
                💾 <?= isset($modeEdit) ? 'Enregistrer' : 'Ajouter' ?>
            </button>
            <a href="index.php?page=back_participation_liste" class="btn btn-gris">Annuler</a>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
