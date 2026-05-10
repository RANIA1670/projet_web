<div class="admin-container container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= APP_URL ?>/backoffice" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
            <h1 class="mb-1"><i class="fas fa-tools"></i> Gestion des Interventions</h1>
            <p class="text-muted">Filtrer et gérer les interventions avec une vue claire et structurée.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= APP_URL ?>/backoffice/intervention/creer" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nouvelle intervention</a>
        </div>
    </div>

    <form method="GET" class="admin-filters mb-4 p-4 bg-white rounded shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" class="form-control" placeholder="ID, signalement, adresse...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="planifiee" <?= ($filters['statut'] ?? '') === 'planifiee' ? 'selected' : '' ?>>Planifiée</option>
                    <option value="en_cours" <?= ($filters['statut'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="terminee" <?= ($filters['statut'] ?? '') === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                    <option value="annulee" <?= ($filters['statut'] ?? '') === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Technicien</label>
                <select name="technicien_id" class="form-select">
                    <option value="">Tous</option>
                    <?php foreach ($techniciens as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($filters['technicien_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th><a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'ASC' ? 'DESC' : 'ASC'])) ?>">ID</a></th>
                    <th><a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'ASC' ? 'DESC' : 'ASC'])) ?>">Création</a></th>
                    <th><a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['sort' => 'date_planifiee', 'direction' => $sort === 'date_planifiee' && $direction === 'ASC' ? 'DESC' : 'ASC'])) ?>">Date prévue</a></th>
                    <th>Signalement</th>
                    <th><a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['sort' => 'technicien_nom', 'direction' => $sort === 'technicien_nom' && $direction === 'ASC' ? 'DESC' : 'ASC'])) ?>">Technicien</a></th>
                    <th><a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['sort' => 'statut', 'direction' => $sort === 'statut' && $direction === 'ASC' ? 'DESC' : 'ASC'])) ?>">Statut</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($interventions)): ?>
                    <tr><td colspan="7" class="text-center py-4">Aucune intervention trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($interventions as $inv): ?>
                    <tr>
                        <td>#<?= $inv['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($inv['created_at'])) ?></td>
                        <td><?= !empty($inv['date_planifiee']) ? date('d/m/Y', strtotime($inv['date_planifiee'])) : '<span class="text-muted">Non définie</span>' ?></td>
                        <td>
                            <a href="<?= APP_URL ?>/backoffice/intervention/<?= $inv['id'] ?>" class="text-decoration-none fw-bold">
                                #<?= $inv['signalement_id'] ?> - <?= htmlspecialchars($inv['signalement_titre']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($inv['technicien_nom']): ?>
                                <span class="badge bg-info text-dark"><i class="fas fa-hard-hat"></i> <?= htmlspecialchars($inv['technicien_nom']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Non assigné</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($inv['statut']) {
                                'planifiee' => 'badge-primary',
                                'en_cours' => 'badge-warning',
                                'terminee' => 'badge-success',
                                'annulee' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $statutLabel = match($inv['statut']) {
                                'planifiee' => 'Planifiée',
                                'en_cours' => 'En cours',
                                'terminee' => 'Terminée',
                                'annulee' => 'Annulée',
                                default => ucfirst($inv['statut'])
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="<?= APP_URL ?>/backoffice/intervention/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                                <a href="<?= APP_URL ?>/backoffice/intervention/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-pencil-alt"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-warning status-btn" 
                                    data-id="<?= $inv['id'] ?>" 
                                    data-statut="<?= $inv['statut'] ?>" 
                                    data-notes="<?= htmlspecialchars($inv['notes'] ?? '') ?>"
                                    title="Modifier le statut">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if (!$inv['technicien_id'] && in_array($inv['statut'], ['planifiee', 'en_cours'])): ?>
                                    <button type="button" class="btn btn-sm btn-primary assign-btn" data-id="<?= $inv['id'] ?>" title="Assigner un technicien">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                <?php endif; ?>
                                <form action="<?= APP_URL ?>/backoffice/intervention/<?= $inv['id'] ?>/supprimer" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette intervention ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-section mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong><?= $totalItems ?></strong> intervention(s) trouvée(s)
        </div>
        <div class="pagination-buttons">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= APP_URL ?>/backoffice/interventions?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="btn btn-sm <?= $i === $currentPage ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Modal Modifier Statut -->
<div id="statusModal" class="admin-modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier le statut</h3>
            <span class="close-modal">&times;</span>
        </div>
        <form id="statusForm" method="POST">
            <div class="modal-body">
                <p>Mettre à jour l'intervention <strong id="statusIntervIdLabel">#0</strong> :</p>
                <div class="form-group mb-3">
                    <label for="statutSelect" class="form-label">Statut</label>
                    <select name="statut" id="statutSelect" class="form-control" required>
                        <option value="planifiee">Planifiée</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                        <option value="annulee">Annulée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notesInput" class="form-label">Notes techniques</label>
                    <textarea name="notes" id="notesInput" class="form-control" rows="3" placeholder="Notes sur l'avancement..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline close-modal-btn">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Assigner Technicien -->
<div id="assignModal" class="admin-modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Assigner un technicien</h3>
            <span class="close-modal">&times;</span>
        </div>
        <form id="assignForm" method="POST">
            <div class="modal-body">
                <p>Choisissez le technicien pour l'intervention <strong id="intervIdLabel">#0</strong> :</p>
                <div class="form-group mb-3">
                    <label for="techSelect" class="form-label">Technicien</label>
                    <select name="technicien_id" id="techSelect" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($techniciens as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline close-modal-btn">Annuler</button>
                <button type="submit" class="btn btn-primary">Confirmer l'assignation</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
}
.admin-table th, .admin-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}
.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}
.admin-table tr:hover {
    background-color: #f8f9fa;
}

.admin-filters .form-control,
.admin-filters .form-select {
    border-radius: 6px;
}

.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.pagination-buttons a {
    margin-right: 5px;
}

/* Modal styles */
.admin-modal {
    position: fixed;
    z-index: 1000;
    left: 0; top: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.45);
    display: none;
    align-items: center; justify-content: center;
}
.modal-content {
    background: white; border-radius: 8px; width: 400px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close-modal { cursor: pointer; font-size: 1.5rem; }
.modal-body { padding: 20px; }
.modal-footer { padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const assignModal = document.getElementById('assignModal');
    const assignForm = document.getElementById('assignForm');
    const assignIdLabel = document.getElementById('intervIdLabel');
    const statusModal = document.getElementById('statusModal');
    const statusForm = document.getElementById('statusForm');
    const statusIdLabel = document.getElementById('statusIntervIdLabel');
    const statutSelect = document.getElementById('statutSelect');
    const notesInput = document.getElementById('notesInput');
    const closeBtns = document.querySelectorAll('.close-modal, .close-modal-btn');

    document.querySelectorAll('.assign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            assignIdLabel.textContent = '#' + id;
            assignForm.action = '<?= APP_URL ?>/backoffice/intervention/' + id + '/assigner';
            assignModal.style.display = 'flex';
        });
    });

    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const statut = this.getAttribute('data-statut');
            const notes = this.getAttribute('data-notes');

            statusIdLabel.textContent = '#' + id;
            statutSelect.value = statut;
            notesInput.value = notes;
            statusForm.action = '<?= APP_URL ?>/backoffice/intervention/' + id + '/statut';
            statusModal.style.display = 'flex';
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            assignModal.style.display = 'none';
            statusModal.style.display = 'none';
        });
    });

    window.onclick = (event) => {
        if (event.target == assignModal) assignModal.style.display = 'none';
        if (event.target == statusModal) statusModal.style.display = 'none';
    };
});
</script>










