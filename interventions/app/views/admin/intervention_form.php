<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= APP_URL ?>/backoffice/interventions" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour aux interventions</a>
        <a href="<?= APP_URL ?>/backoffice" class="btn btn-sm btn-outline-success">Dashboard admin</a>
    </div>

    <div class="bg-white rounded shadow-sm p-4">
        <h2><?= $action === 'edit' ? 'Modifier l’intervention' : 'Créer une nouvelle intervention' ?></h2>
        <form method="POST" action="<?= $action === 'edit' ? APP_URL . '/backoffice/intervention/' . $intervention['id'] . '/edit' : APP_URL . '/backoffice/intervention/creer' ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Signalement lié</label>
                    <select name="signalement_id" class="form-select" required>
                        <option value="">-- Sélectionner un signalement --</option>
                        <?php foreach ($signalements as $signalement): ?>
                            <option value="<?= $signalement['id'] ?>" <?= $intervention && $intervention['signalement_id'] == $signalement['id'] ? 'selected' : '' ?>>
                                #<?= $signalement['id'] ?> - <?= htmlspecialchars($signalement['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Technicien</label>
                    <select name="technicien_id" class="form-select">
                        <option value="">-- Aucun technicien --</option>
                        <?php foreach ($techniciens as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $intervention && $intervention['technicien_id'] == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date prévue</label>
                    <input type="date" name="date_planifiee" class="form-control" value="<?= $intervention['date_planifiee'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select" required>
                        <?php $statusOptions = ['planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée']; ?>
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $intervention && $intervention['statut'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($intervention['notes'] ?? '') ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Enregistrer' : 'Créer' ?></button>
                    <a href="<?= APP_URL ?>/backoffice/interventions" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>










