<?php
// ================================================
//  VUE  : views/front/event/detail.php
//  RÔLE : Détail d'un événement + formulaire inscription
//         Validation PHP — PAS HTML5
// ================================================
$titrePage = htmlspecialchars($event['titre']);
require __DIR__ . '/../../layouts/front_header.php';
?>
<div class="container">

    <a href="index.php?page=front_event_liste" class="back-link">← Retour aux événements</a>

    <h1>🎫 <?= htmlspecialchars($event['titre']) ?></h1>

    <!-- Détail de l'événement -->
    <div class="detail-box">
        <p><span class="detail-label">📅 Date :</span> <?= date('d/m/Y', strtotime($event['date_event'])) ?></p>
        <p><span class="detail-label">📍 Lieu :</span> <?= htmlspecialchars($event['lieu']) ?></p>
        <p><span class="detail-label">💼 Sponsor :</span>
            <span class="sponsor-badge"><?= htmlspecialchars($event['nom_sponsor']) ?></span>
        </p>
        <p style="margin-top:12px;"><span class="detail-label">📝 Description :</span></p>
        <p style="margin-top:6px; color:#555; line-height:1.7;">
            <?= nl2br(htmlspecialchars($event['description'])) ?>
        </p>
    </div>

    <!-- Participants inscrits -->
    <div class="participants-list">
        <h2>👥 Participants inscrits (<?= count($participants) ?>)</h2>
        <?php if (empty($participants)): ?>
            <p style="color:#aaa; font-size:.88rem;">Aucun participant pour le moment. Soyez le premier !</p>
        <?php else: ?>
            <ul>
                <?php foreach ($participants as $p): ?>
                    <li>✔ <strong><?= htmlspecialchars($p['nom_participant']) ?></strong>
                        — <em><?= htmlspecialchars($p['email_participant']) ?></em>
                        <?php if (!empty($p['numero_participant'])): ?>
                            — 📞 <?= htmlspecialchars($p['numero_participant']) ?>
                        <?php endif; ?>
                        <?php if (!empty($p['age_participant'])): ?>
                            — Âge <?= htmlspecialchars($p['age_participant']) ?>
                        <?php endif; ?>
                        <?php if (!empty($p['sexe_participant'])): ?>
                            — <?= htmlspecialchars($p['sexe_participant']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Formulaire d'inscription -->
    <div style="margin-top:32px;">
        <h2>📩 S'inscrire à cet événement</h2>

        <?php if ($succes): ?>
            <div class="msg-succes">✅ Inscription réussie ! Vous participez à cet événement.</div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="msg-erreur">
                <strong>⚠ Veuillez corriger les erreurs suivantes :</strong>
                <ul>
                    <?php foreach ($erreurs as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-box">
            <!-- action pointe vers ce même contrôleur avec l'ID -->
            <form method="POST" action="index.php?page=front_event_detail&id=<?= $event['id_event'] ?>">

                <label for="nom_participant">Votre nom complet *</label>
                <!-- PAS d'attribut required HTML5 — validation 100% PHP -->
                <input type="text" id="nom_participant" name="nom_participant"
                       placeholder="Ex : Ali Ben Salah"
                       value="<?= htmlspecialchars($nom_p ?? '') ?>">

                <label for="email_participant">Votre email *</label>
                <input type="text" id="email_participant" name="email_participant"
                       placeholder="Ex : ali@email.com"
                       value="<?= htmlspecialchars($email_p ?? '') ?>">

                <label for="numero_participant">Votre numéro de téléphone *</label>
                <input type="text" id="numero_participant" name="numero_participant"
                       placeholder="Ex : +216 71 000 000"
                       value="<?= htmlspecialchars($numero_p ?? '') ?>">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label for="age_participant">Âge *</label>
                        <input type="text" id="age_participant" name="age_participant"
                               placeholder="Ex : 28"
                               value="<?= htmlspecialchars($age_p ?? '') ?>">
                    </div>
                    <div>
                        <label for="sexe_participant">Sexe *</label>
                        <select id="sexe_participant" name="sexe_participant">
                            <option value="">-- Choisir --</option>
                            <option value="Homme" <?= (isset($sexe_p) && $sexe_p === 'Homme') ? 'selected' : '' ?>>Homme</option>
                            <option value="Femme" <?= (isset($sexe_p) && $sexe_p === 'Femme') ? 'selected' : '' ?>>Femme</option>
                            <option value="Autre" <?= (isset($sexe_p) && $sexe_p === 'Autre') ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:14px;">
                    <button type="submit" class="btn btn-vert">✅ S'inscrire</button>
                    <a href="index.php?page=front_event_liste" class="btn btn-gris">Annuler</a>
                </div>

            </form>
        </div>
    </div>

</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
