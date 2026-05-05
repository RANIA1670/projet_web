<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Signalements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .actions { white-space: nowrap; }
        .actions a { margin-right: 10px; }
        .filters { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Backoffice - Gestion des Signalements</h1>
    <nav>
        <a href="/backoffice">Accueil</a> |
        <a href="/backoffice/signalements">Signalements</a> |
        <a href="/backoffice/interventions">Interventions</a> |
        <a href="/backoffice/techniciens">Techniciens</a> |
        <a href="/auth/deconnexion">Déconnexion</a>
    </nav>

    <div class="filters">
        <form method="get">
            <label>Statut: 
                <select name="statut">
                    <option value="">Tous</option>
                    <option value="nouveau" <?= $filters['statut'] == 'nouveau' ? 'selected' : '' ?>>Nouveau</option>
                    <option value="en_attente" <?= $filters['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="en_cours" <?= $filters['statut'] == 'en_cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="resolu" <?= $filters['statut'] == 'resolu' ? 'selected' : '' ?>>Résolu</option>
                    <option value="ferme" <?= $filters['statut'] == 'ferme' ? 'selected' : '' ?>>Fermé</option>
                </select>
            </label>
            <button type="submit">Filtrer</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($signalements as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['titre']) ?></td>
                <td><?= htmlspecialchars(substr($s['description'], 0, 50)) ?>...</td>
                <td><?= $s['statut'] ?></td>
                <td><?= $s['priorite'] ?></td>
                <td><?= $s['categorie_nom'] ?? 'N/A' ?></td>
                <td><?= $s['auteur_nom'] ?? 'Anonyme' ?></td>
                <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                <td class="actions">
                    <a href="/signalement/<?= $s['id'] ?>">Voir</a>
                    <a href="/backoffice/signalement/<?= $s['id'] ?>/edit">Éditer</a>
                    <form method="post" action="/signalement/<?= $s['id'] ?>/supprimer" style="display:inline;">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" onclick="return confirm('Supprimer ce signalement ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&statut=<?= $filters['statut'] ?>">Précédent</a>
        <?php endif; ?>
        Page <?= $page ?> / <?= $totalPages ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&statut=<?= $filters['statut'] ?>">Suivant</a>
        <?php endif; ?>
    </div>
</body>
</html>