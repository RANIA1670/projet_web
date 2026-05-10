<?php
// ================================================
//  VUE  : views/front/ticket/confirmation.php
//  RÔLE : Page de confirmation d'inscription + téléchargement billet
// ================================================
$titrePage = 'Confirmation d\'inscription';
require __DIR__ . '/../../layouts/front_header.php';
?>
<div class="container">

    <div style="max-width:620px; margin:60px auto; text-align:center;">

        <!-- Icône succès animée -->
        <div style="width:90px;height:90px;background:linear-gradient(135deg,#1e3a5f,#2f8f4e);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 28px;box-shadow:0 8px 32px rgba(30,58,95,.25);">
            <span style="font-size:42px;">🎫</span>
        </div>

        <h1 style="color:#1e3a5f;margin-bottom:10px;">Inscription confirmée !</h1>
        <p style="color:#555;font-size:1.05rem;margin-bottom:32px;">
            Félicitations <strong><?= htmlspecialchars($participant['nom_participant']) ?></strong> !<br>
            Vous êtes bien inscrit(e) à l'événement :
        </p>

        <!-- Carte événement -->
        <div style="background:linear-gradient(135deg,#1e3a5f 0%,#2d4f7c 100%);border-radius:16px;padding:28px;color:#fff;margin-bottom:32px;text-align:left;box-shadow:0 8px 32px rgba(30,58,95,.2);">
            <div style="font-size:.8rem;letter-spacing:2px;color:#E67E22;text-transform:uppercase;margin-bottom:8px;">Événement</div>
            <h2 style="margin:0 0 16px;font-size:1.4rem;"><?= htmlspecialchars($event['titre']) ?></h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:.95rem;">
                <div>
                    <span style="color:#94a3b8;">📅 Date</span><br>
                    <strong><?= date('d/m/Y', strtotime($event['date_event'])) ?></strong>
                </div>
                <div>
                    <span style="color:#94a3b8;">📍 Lieu</span><br>
                    <strong><?= htmlspecialchars($event['lieu']) ?></strong>
                </div>
                <div>
                    <span style="color:#94a3b8;">💼 Sponsor</span><br>
                    <strong><?= htmlspecialchars($event['nom_sponsor']) ?></strong>
                </div>
                <div>
                    <span style="color:#94a3b8;">🎟️ Code</span><br>
                    <strong style="color:#E67E22;font-family:monospace;font-size:1.05rem;">
                        EV<?= str_pad($event['id_event'], 3, '0', STR_PAD_LEFT) ?>-PART<?= str_pad($participant['id_participation'], 4, '0', STR_PAD_LEFT) ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- Bouton téléchargement -->
        <a href="index.php?page=ticket_download&id_participation=<?= $participant['id_participation'] ?>"
           style="display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,#E67E22,#d35400);color:#fff;padding:16px 36px;border-radius:50px;text-decoration:none;font-weight:700;font-size:1.05rem;box-shadow:0 6px 20px rgba(230,126,34,.4);transition:transform .2s,box-shadow .2s;margin-bottom:16px;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 28px rgba(230,126,34,.5)'"
           onmouseout="this.style.transform='';this.style.boxShadow='0 6px 20px rgba(230,126,34,.4)'">
            <span style="font-size:1.3rem;">⬇️</span>
            Télécharger mon billet PDF
        </a>

        <p style="color:#94a3b8;font-size:.85rem;margin-bottom:32px;">
            Présentez ce billet à l'entrée de l'événement.
        </p>

        <!-- Boutons retour -->
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="index.php?page=front_event_detail&id=<?= $event['id_event'] ?>"
               class="btn btn-gris">← Retour à l'événement</a>
            <a href="index.php?page=front_event_liste"
               class="btn btn-bleu">🎫 Voir tous les événements</a>
        </div>

    </div>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
