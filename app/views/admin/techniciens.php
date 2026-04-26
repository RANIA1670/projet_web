<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-outline mb-2"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
            <h1><i class="fas fa-users-cog"></i> Gestion des Techniciens</h1>
        </div>
    </div>

    <div class="row g-4">
        <!-- Techniciens Disponibles -->
        <div class="col-md-6">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-user-check"></i> Techniciens Disponibles</h4>
                </div>
                <div class="card-body">
                    <?php 
                    $dispos = array_filter($techniciens, fn($t) => !$t['is_busy']);
                    if (empty($dispos)): ?>
                        <p class="text-muted text-center my-4">Aucun technicien disponible pour le moment.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($dispos as $tech): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($tech['prenom'] . ' ' . $tech['nom']) ?></h6>
                                        <small class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($tech['email']) ?></small><br>
                                        <small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($tech['telephone'] ?? 'Non renseigné') ?></small>
                                    </div>
                                    <span class="badge bg-success rounded-pill">Libre</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Techniciens Occupés -->
        <div class="col-md-6">
            <div class="card h-100 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-user-clock"></i> Techniciens en Intervention</h4>
                </div>
                <div class="card-body">
                    <?php 
                    $occupes = array_filter($techniciens, fn($t) => $t['is_busy']);
                    if (empty($occupes)): ?>
                        <p class="text-muted text-center my-4">Aucun technicien actuellement en intervention.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($occupes as $tech): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($tech['prenom'] . ' ' . $tech['nom']) ?></h6>
                                            <small class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($tech['email']) ?></small>
                                        </div>
                                        <span class="badge bg-warning text-dark rounded-pill">Occupé</span>
                                    </div>
                                    
                                    <div class="intervention-details bg-light p-2 rounded border">
                                        <small class="d-block fw-bold mb-1 text-primary">Intervention Actuelle :</small>
                                        <small class="d-block"><strong>ID:</strong> #<?= $tech['current_intervention']['id'] ?></small>
                                        <small class="d-block"><strong>Signalement:</strong> <?= htmlspecialchars($tech['current_intervention']['signalement_titre']) ?></small>
                                        <small class="d-block"><strong>Lieu:</strong> <?= htmlspecialchars($tech['current_intervention']['adresse']) ?></small>
                                        <div class="mt-2 text-end">
                                            <a href="<?= APP_URL ?>/intervention/<?= $tech['current_intervention']['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size: 11px;">Voir l'intervention</a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.card-header {
    border-top-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
    padding: 15px 20px;
}
.list-group-item {
    border-left: none;
    border-right: none;
    padding: 15px 0;
}
.list-group-item:first-child {
    padding-top: 0;
}
.list-group-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.intervention-details {
    font-size: 0.85rem;
}
</style>
