<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Interventions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .actions { white-space: nowrap; }
        .actions a, .actions form { margin-right: 10px; display: inline; }
    </style>
</head>
<body>
    <h1>Backoffice - Gestion des Interventions</h1>
    <nav>
        <a href="/backoffice">Accueil</a> |
        <a href="/backoffice/signalements">Signalements</a> |
        <a href="/backoffice/interventions">Interventions</a> |
        <a href="/backoffice/techniciens">Techniciens</a> |
        <a href="/auth/deconnexion">Déconnexion</a>
    </nav>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Signalement</th>
                <th>Statut</th>
                <th>Technicien</th>
                <th>Date Planifiée</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interventions as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= htmlspecialchars($i['titre']) ?></td>
                <td><a href="/signalement/<?= $i['signalement_id'] ?>"><?= $i['signalement_titre'] ?? 'N/A' ?></a></td>
                <td><?= $i['statut'] ?></td>
                <td><?= $i['technicien_nom'] ?? 'Non assigné' ?></td>
                <td><?= $i['date_planifiee'] ? date('d/m/Y', strtotime($i['date_planifiee'])) : 'N/A' ?></td>
                <td class="actions">
                    <a href="/intervention/<?= $i['id'] ?>">Voir</a>
                    <form method="post" action="/backoffice/intervention/<?= $i['id'] ?>/assigner" style="display:inline;">
                        <select name="technicien_id">
                            <option value="">Assigner Technicien</option>
                            <?php foreach ($techniciens as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= $t['prenom'] ?> <?= $t['nom'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Assigner</button>
                    </form>
                    <form method="post" action="/backoffice/intervention/<?= $i['id'] ?>/statut" style="display:inline;">
                        <select name="statut">
                            <option value="planifiee">Planifiée</option>
                            <option value="en_cours">En cours</option>
                            <option value="terminee">Terminée</option>
                            <option value="annulee">Annulée</option>
                        </select>
                        <button type="submit">Changer Statut</button>
                    </form>
                    <form method="post" action="/backoffice/intervention/<?= $i['id'] ?>/supprimer" style="display:inline;">
                        <button type="submit" onclick="return confirm('Supprimer cette intervention ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>