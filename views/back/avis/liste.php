<?php
// ================================================
//  VUE  : views/back/avis/liste.php
//  RÔLE : Modération des avis en back-office
// ================================================
$titrePage = 'Modération des avis';
require __DIR__ . '/../../layouts/back_header.php';
?>

<h1>⭐ Modération des avis</h1>

<?php if ($msg === 'approuve'): ?>
    <div class="msg-succes">✅ Avis approuvé et publié avec succès.</div>
<?php elseif ($msg === 'rejete'): ?>
    <div class="msg-succes" style="background:#fef3cd;color:#856404;border-color:#ffd700;">🗑️ Avis rejeté et supprimé.</div>
<?php endif; ?>

<!-- Filtres -->
<form method="GET" action="index.php" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;margin-bottom:24px;background:#f8fafc;padding:18px;border-radius:12px;border:1px solid #e2e8f0;">
    <input type="hidden" name="page" value="back_avis_liste">
    <div>
        <label style="display:block;margin-bottom:6px;font-weight:600;">Statut</label>
        <select name="approuve" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:8px;">
            <option value="">Tous</option>
            <option value="0" <?= ($_GET['approuve'] ?? '') === '0' ? 'selected' : '' ?>>⏳ En attente</option>
            <option value="1" <?= ($_GET['approuve'] ?? '') === '1' ? 'selected' : '' ?>>✅ Approuvés</option>
        </select>
    </div>
    <div>
        <label style="display:block;margin-bottom:6px;font-weight:600;">Événement</label>
        <select name="id_event" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:8px;">
            <option value="0">Tous</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= $ev['id_event'] ?>" <?= (int)($_GET['id_event'] ?? 0) === (int)$ev['id_event'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-bleu">🔎 Filtrer</button>
    <a href="index.php?page=back_avis_liste" class="btn btn-gris">Réinitialiser</a>
</form>

<?php if ($pending > 0): ?>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:1.8rem;">🔔</span>
        <div>
            <strong style="color:#c2410c;"><?= $pending ?> avis en attente de validation</strong>
            <div style="color:#9a3412;font-size:.87rem;">Pas encore visibles par les visiteurs.</div>
        </div>
        <a href="index.php?page=back_avis_liste&approuve=0" class="btn btn-orange" style="margin-left:auto;">Voir</a>
    </div>
<?php endif; ?>

<?php if (empty($avis)): ?>
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:3rem;margin-bottom:12px;">💬</div>
        <p>Aucun avis trouvé.</p>
    </div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Participant</th><th>Événement</th>
                    <th>Note</th><th>Commentaire</th><th>Date</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avis as $a): ?>
                    <tr>
                        <td><?= $a['id_avis'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($a['nom_participant']) ?></strong><br>
                            <small style="color:#94a3b8;"><?= htmlspecialchars($a['email_participant']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($a['titre_event']) ?></td>
                        <td style="color:#E67E22;font-size:1.1rem;letter-spacing:2px;">
                            <?= str_repeat('★', (int)$a['note']) . str_repeat('☆', 5 - (int)$a['note']) ?>
                            <div style="color:#64748b;font-size:.75rem;letter-spacing:0;"><?= $a['note'] ?>/5</div>
                        </td>
                        <td style="max-width:240px;color:#444;font-size:.9rem;">
                            <?= htmlspecialchars(mb_substr($a['commentaire'], 0, 110)) ?><?= strlen($a['commentaire']) > 110 ? '…' : '' ?>
                        </td>
                        <td style="white-space:nowrap;color:#64748b;font-size:.87rem;">
                            <?= date('d/m/Y H:i', strtotime($a['date_avis'])) ?>
                        </td>
                        <td>
                            <?php if ($a['approuve']): ?>
                                <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:50px;font-size:.8rem;font-weight:600;">✅ Approuvé</span>
                            <?php else: ?>
                                <span style="background:#fef3c7;color:#92400e;padding:4px 12px;border-radius:50px;font-size:.8rem;font-weight:600;">⏳ En attente</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if (!$a['approuve']): ?>
                                <a href="index.php?page=back_avis_approuver&id=<?= $a['id_avis'] ?>"
                                   class="btn btn-vert" style="font-size:.8rem;padding:6px 12px;"
                                   onclick="return confirm('Approuver cet avis ?')">✅ Approuver</a>
                            <?php endif; ?>
                            <a href="index.php?page=back_avis_rejeter&id=<?= $a['id_avis'] ?>"
                               class="btn btn-rouge" style="font-size:.8rem;padding:6px 12px;"
                               onclick="return confirm('Supprimer cet avis ?')">🗑️ Rejeter</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="color:#94a3b8;font-size:.85rem;margin-top:12px;"><?= count($avis) ?> avis trouvé(s)</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/back_footer.php'; ?>
