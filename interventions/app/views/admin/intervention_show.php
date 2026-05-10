<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= APP_URL ?>/backoffice/interventions" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour aux interventions</a>
        </div>
        <div>
            <a href="<?= APP_URL ?>/backoffice" class="btn btn-sm btn-outline-success">Dashboard admin</a>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm p-4">
        <h2>Détail de l'intervention #<?= $intervention['id'] ?></h2>
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <h5>Signalement lié</h5>
                <p><strong>#<?= $intervention['signalement_id'] ?></strong> - <?= htmlspecialchars($intervention['signalement_titre']) ?></p>
                <p><strong>Adresse :</strong> <?= htmlspecialchars($intervention['adresse']) ?></p>
                <p><strong>Catégorie :</strong> <?= htmlspecialchars($intervention['categorie_nom']) ?></p>
                <p><strong>Priorité :</strong> <?= htmlspecialchars($intervention['priorite']) ?></p>
                <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($intervention['signalement_desc'])) ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <h5>Intervention</h5>
                <p><strong>Statut :</strong> <?= ucfirst(str_replace('_', ' ', $intervention['statut'])) ?></p>
                <p><strong>Date prévue :</strong> <?= !empty($intervention['date_planifiee']) ? date('d/m/Y', strtotime($intervention['date_planifiee'])) : '<span class="text-muted">Non définie</span>' ?></p>
                <p><strong>Technicien :</strong> <?= $intervention['technicien_nom'] ? htmlspecialchars($intervention['technicien_nom']) : '<span class="text-muted">Non assigné</span>' ?></p>
                <p><strong>Notes :</strong><br><?= nl2br(htmlspecialchars($intervention['notes'] ?? 'Aucune note.')) ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= APP_URL ?>/backoffice/intervention/<?= $intervention['id'] ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i> Modifier l'intervention</a>
            <form action="<?= APP_URL ?>/backoffice/intervention/<?= $intervention['id'] ?>/supprimer" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette intervention ?');">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
</div>










