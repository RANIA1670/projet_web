<?php
// ================================================
//  VUE  : views/front/event/detail.php
//  RÔLE : Détail d'un événement + inscription + billet + avis
// ================================================

require_once __DIR__ . '/../../../models/AvisModel.php';

$avisModel  = new AvisModel();
$avgNote    = $avisModel->getAverageNote((int)$event['id_event']);
$nbAvis     = $avisModel->countByEvent((int)$event['id_event']);
$avisListe  = $avisModel->findByEvent((int)$event['id_event']);

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

        <!-- Note moyenne -->
        <?php if ($nbAvis > 0): ?>
            <p style="margin-top:12px;">
                <span class="detail-label">⭐ Note :</span>
                <span style="color:#E67E22;font-size:1.1rem;letter-spacing:2px;margin-right:6px;">
                    <?php
                        $rounded = round($avgNote);
                        echo str_repeat('★', (int)$rounded) . str_repeat('☆', 5 - (int)$rounded);
                    ?>
                </span>
                <strong><?= $avgNote ?>/5</strong>
                <span style="color:#94a3b8;font-size:.85rem;">(<?= $nbAvis ?> avis)</span>
                <a href="index.php?page=front_avis_noter&id_event=<?= $event['id_event'] ?>"
                   style="margin-left:12px;font-size:.83rem;color:#1e3a5f;text-decoration:underline;">
                    Voir les avis →
                </a>
            </p>
        <?php else: ?>
            <p style="margin-top:12px;">
                <span class="detail-label">⭐ Note :</span>
                <span style="color:#94a3b8;font-size:.88rem;">Aucun avis pour le moment</span>
            </p>
        <?php endif; ?>

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
            <?php
                // Récupérer l'id_participation inséré pour le billet
                require_once __DIR__ . '/../../../models/ParticipationModel.php';
                $pm    = new ParticipationModel();
                $lastP = $pm->findByEmailAndEvent($email_p, (int)$event['id_event']);
                $lastId = $lastP ? $lastP['id_participation'] : 0;
            ?>
            <div class="msg-succes">
                ✅ Inscription réussie ! Vous participez à cet événement.
            </div>

            <!-- Bouton Billet PDF -->
            <?php if ($lastId > 0): ?>
            <div style="margin:20px 0;padding:20px 24px;background:linear-gradient(135deg,#fff7ed,#fef3e2);border:1px solid #fed7aa;border-radius:14px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
                <div>
                    <div style="font-weight:700;color:#1e3a5f;margin-bottom:4px;">🎫 Votre billet est prêt !</div>
                    <div style="color:#64748b;font-size:.88rem;">Téléchargez-le et présentez-le à l'entrée de l'événement.</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="index.php?page=ticket_download&id_participation=<?= $lastId ?>"
                       style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#E67E22,#d35400);color:#fff;padding:12px 24px;border-radius:50px;text-decoration:none;font-weight:700;font-size:.95rem;box-shadow:0 4px 16px rgba(230,126,34,.35);">
                        ⬇️ Télécharger le billet PDF
                    </a>
                    <a href="index.php?page=front_avis_noter&id_event=<?= $event['id_event'] ?>&id_participation=<?= $lastId ?>"
                       style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#1e3a5f,#2d4f7c);color:#fff;padding:12px 24px;border-radius:50px;text-decoration:none;font-weight:600;font-size:.95rem;">
                        ⭐ Laisser un avis
                    </a>
                </div>
            </div>
            <?php endif; ?>

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
            <form method="POST" action="index.php?page=front_event_detail&id=<?= $event['id_event'] ?>">

                <label for="nom_participant">Votre nom complet *</label>
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

    <!-- Section avis approuvés -->
    <?php if (!empty($avisListe)): ?>
    <div style="margin-top:48px;">
        <h2>
            💬 Avis des participants
            <a href="index.php?page=front_avis_noter&id_event=<?= $event['id_event'] ?>"
               style="font-size:.8rem;font-weight:500;color:#E67E22;text-decoration:none;margin-left:12px;">
                + Laisser un avis
            </a>
        </h2>

        <?php foreach ($avisListe as $a): ?>
            <div style="background:#fff;border:1px solid #e8edf2;border-radius:14px;padding:18px 22px;margin-bottom:14px;box-shadow:0 2px 10px rgba(0,0,0,.04);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <strong style="color:#1e3a5f;"><?= htmlspecialchars($a['nom_participant']) ?></strong>
                    <div>
                        <span style="color:#E67E22;font-size:1rem;letter-spacing:2px;">
                            <?= str_repeat('★', (int)$a['note']) . str_repeat('☆', 5 - (int)$a['note']) ?>
                        </span>
                        <span style="color:#94a3b8;font-size:.8rem;margin-left:8px;">
                            <?= date('d/m/Y', strtotime($a['date_avis'])) ?>
                        </span>
                    </div>
                </div>
                <p style="color:#444;line-height:1.7;margin:0;font-size:.93rem;">
                    <?= nl2br(htmlspecialchars($a['commentaire'])) ?>
                </p>
            </div>
        <?php endforeach; ?>

        <a href="index.php?page=front_avis_noter&id_event=<?= $event['id_event'] ?>"
           style="display:inline-block;margin-top:4px;color:#1e3a5f;font-size:.88rem;text-decoration:underline;">
            Voir tous les avis & laisser le vôtre →
        </a>
    </div>
    <?php else: ?>
    <div style="margin-top:36px;padding:20px 24px;background:#f8fafc;border-radius:12px;border:1px dashed #e2e8f0;text-align:center;">
        <p style="color:#94a3b8;margin:0 0 12px;">Aucun avis pour l'instant. Soyez le premier à noter cet événement !</p>
        <a href="index.php?page=front_avis_noter&id_event=<?= $event['id_event'] ?>"
           class="btn btn-bleu" style="font-size:.88rem;">⭐ Laisser un avis</a>
    </div>
    <?php endif; ?>

</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
