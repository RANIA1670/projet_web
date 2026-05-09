<?php
// ================================================
//  VUE  : views/front/event/avis.php
//  RÔLE : Formulaire de notation d'un événement (étoiles)
// ================================================
require __DIR__ . '/../../layouts/front_header.php';
?>

<style>
/* ---- Star Rating ---- */
.star-rating { display:flex; flex-direction:row-reverse; justify-content:flex-end; gap:6px; }
.star-rating input { display:none; }
.star-rating label {
    font-size: 2.4rem;
    color: #d1d5db;
    cursor: pointer;
    transition: color .15s, transform .15s;
    line-height: 1;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #E67E22;
}
.star-rating label:hover { transform: scale(1.2); }

.avis-card {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    transition: box-shadow .2s;
}
.avis-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); }
.stars-display { color: #E67E22; font-size: 1.2rem; letter-spacing: 2px; }
</style>

<div class="container">

    <a href="index.php?page=front_event_detail&id=<?= (int)$idEvent ?>" class="back-link">← Retour à l'événement</a>

    <h1>⭐ Laisser un avis</h1>
    <p style="color:#666;margin-bottom:28px;">
        Notez l'événement : <strong><?= htmlspecialchars($event['titre']) ?></strong>
    </p>

    <?php if ($dejaNote): ?>
        <!-- Déjà noté -->
        <div class="msg-succes" style="font-size:1.05rem;">
            ✅ Vous avez déjà laissé un avis pour cet événement. Merci pour votre retour !
        </div>

    <?php elseif ($succes): ?>
        <!-- Succès après soumission -->
        <div style="text-align:center;padding:48px 24px;">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,#2f8f4e,#27ae60);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">✅</div>
            <h2 style="color:#1e3a5f;margin-bottom:10px;">Merci pour votre avis !</h2>
            <p style="color:#555;margin-bottom:28px;">Votre avis est en attente de validation par notre équipe. Il sera publié sous peu.</p>
            <a href="index.php?page=front_event_detail&id=<?= (int)$idEvent ?>" class="btn btn-bleu">← Retour à l'événement</a>
        </div>

    <?php else: ?>
        <!-- Formulaire -->
        <?php if (!empty($erreurs)): ?>
            <div class="msg-erreur">
                <strong>⚠ Veuillez corriger :</strong>
                <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (!$participant): ?>
            <div class="msg-erreur">
                ⚠ Vous devez d'abord vous inscrire à cet événement pour laisser un avis.
                <br><a href="index.php?page=front_event_detail&id=<?= (int)$idEvent ?>" class="btn btn-bleu" style="margin-top:12px;display:inline-block;">S'inscrire</a>
            </div>
        <?php else: ?>
        <div class="form-box" style="max-width:600px;">
            <form method="POST" action="index.php?page=front_avis_noter&id_event=<?= (int)$idEvent ?>&id_participation=<?= (int)$idParticipation ?>">

                <!-- Étoiles -->
                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:12px;font-weight:600;color:#1e3a5f;">Votre note *</label>
                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="note" value="<?= $i ?>">
                            <label for="star<?= $i ?>" title="<?= $i ?> étoile<?= $i > 1 ? 's' : '' ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <p style="color:#94a3b8;font-size:.82rem;margin-top:8px;">Cliquez sur les étoiles pour noter</p>
                </div>

                <!-- Commentaire -->
                <label for="commentaire">Votre commentaire *</label>
                <textarea id="commentaire" name="commentaire" rows="5"
                          placeholder="Partagez votre expérience (minimum 5 caractères)..."
                          style="resize:vertical;"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>

                <div style="color:#94a3b8;font-size:.82rem;margin-bottom:20px;">Maximum 1000 caractères.</div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn btn-vert">⭐ Publier mon avis</button>
                    <a href="index.php?page=front_event_detail&id=<?= (int)$idEvent ?>" class="btn btn-gris">Annuler</a>
                </div>

            </form>
        </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- Avis existants approuvés -->
    <?php
    require_once __DIR__ . '/../../../models/AvisModel.php';
    $avisModel   = new AvisModel();
    $avisListe   = $avisModel->findByEvent((int)$idEvent);
    $avgNote     = $avisModel->getAverageNote((int)$idEvent);
    $nbAvis      = $avisModel->countByEvent((int)$idEvent);
    ?>

    <?php if (!empty($avisListe)): ?>
        <div style="margin-top:48px;">
            <h2>
                💬 Avis des participants
                <span style="background:#E67E22;color:#fff;padding:4px 14px;border-radius:50px;font-size:.75rem;vertical-align:middle;margin-left:8px;">
                    <?= $nbAvis ?> avis — <?= $avgNote ?>/5 ★
                </span>
            </h2>

            <?php foreach ($avisListe as $a): ?>
                <div class="avis-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <div>
                            <strong style="color:#1e3a5f;"><?= htmlspecialchars($a['nom_participant']) ?></strong>
                            <span style="color:#94a3b8;font-size:.82rem;margin-left:10px;">
                                <?= date('d/m/Y', strtotime($a['date_avis'])) ?>
                            </span>
                        </div>
                        <div class="stars-display">
                            <?= str_repeat('★', (int)$a['note']) . str_repeat('☆', 5 - (int)$a['note']) ?>
                        </div>
                    </div>
                    <p style="color:#444;line-height:1.7;margin:0;">
                        <?= nl2br(htmlspecialchars($a['commentaire'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
