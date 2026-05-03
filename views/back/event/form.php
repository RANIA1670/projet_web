<?php
// ================================================
//  VUE  : views/back/event/form.php
//  RÔLE : Formulaire Ajout ET Modification d'un event
//         La variable $modeEdit = true  → modification
//         La variable $modeEdit = false → ajout
//         AUCUN attribut required/type HTML5 !
// ================================================
$titrePage = isset($modeEdit) ? 'Modifier un événement' : 'Ajouter un événement';
require __DIR__ . '/../../layouts/back_header.php';
?>

<a href="index.php?page=back_event_liste" class="back-link">← Retour à la liste</a>
<h1><?= isset($modeEdit) ? '✏ Modifier l\'événement' : '➕ Ajouter un événement' ?></h1>

<?php if (!empty($erreurs)): ?>
    <div class="msg-erreur">
        <strong>⚠ Veuillez corriger les erreurs :</strong>
        <ul>
            <?php foreach ($erreurs as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-box">
    <form method="POST" action="index.php?page=<?= isset($modeEdit) ? 'back_event_modifier&id='.$event['id_event'] : 'back_event_ajouter' ?>">

        <!-- PAS d'attribut required, type="email", type="date" ni pattern HTML5 -->

        <label for="titre">Titre de l'événement *</label>
        <input type="text" id="titre" name="titre"
               placeholder="Ex : Hackathon Smart City"
               value="<?= htmlspecialchars($titre ?? '') ?>">

        <label for="description">Description *</label>
        <textarea id="description" name="description"
                  placeholder="Décrivez l'événement..."><?= htmlspecialchars($description ?? '') ?></textarea>

        <div class="form-row">
            <div>
                <label for="date_event">Date *</label>
                <input type="date" id="date_event" name="date_event"
                       value="<?= htmlspecialchars($date_event ?? '') ?>">
            </div>
            <div>
                <label for="lieu">Lieu *</label>
                <input type="text" id="lieu" name="lieu"
                       placeholder="Ex : Tunis, Cité des Sciences"
                       value="<?= htmlspecialchars($lieu ?? '') ?>">
            </div>
        </div>

        <label for="id_sponsor">Sponsor *</label>
        <select id="id_sponsor" name="id_sponsor">
            <option value="0">-- Choisir un sponsor --</option>
            <?php foreach ($sponsors as $sp): ?>
                <option value="<?= $sp['id_sponsor'] ?>"
                    <?= (isset($id_sponsor) && $id_sponsor == $sp['id_sponsor']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sp['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display:flex; gap:10px; margin-top:4px;">
            <button type="submit" class="btn <?= isset($modeEdit) ? 'btn-orange' : 'btn-vert' ?>">
                💾 <?= isset($modeEdit) ? 'Enregistrer les modifications' : 'Ajouter l\'événement' ?>
            </button>
            <a href="index.php?page=back_event_liste" class="btn btn-gris">Annuler</a>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
