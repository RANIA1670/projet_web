<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - CityZen</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { border: 1px solid #ccc; padding: 10px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .actions { white-space: nowrap; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Backoffice - Tableau de Bord</h1>
    <nav>
        <a href="/backoffice">Accueil</a> |
        <a href="/backoffice/signalements">Signalements</a> |
        <a href="/backoffice/interventions">Interventions</a> |
        <a href="/backoffice/techniciens">Techniciens</a> |
        <a href="/auth/deconnexion">Déconnexion</a>
    </nav>

    <div class="stats">
        <div class="stat">
            <h3>Signalements</h3>
            <p>Total: <?= $stats['total_signalements'] ?? 0 ?></p>
            <p>Nouveaux: <?= $stats['nouveaux'] ?? 0 ?></p>
            <p>En cours: <?= $stats['en_cours'] ?? 0 ?></p>
        </div>
        <div class="stat">
            <h3>Interventions</h3>
            <p>Total: <?= $stats['total_interventions'] ?? 0 ?></p>
            <p>Planifiées: <?= $stats['planifiees'] ?? 0 ?></p>
            <p>Terminées: <?= $stats['terminees'] ?? 0 ?></p>
        </div>
        <div class="stat">
            <h3>Utilisateurs</h3>
            <p>Citoyens: <?= $stats['citoyens'] ?? 0 ?></p>
            <p>Techniciens: <?= $stats['techniciens'] ?? 0 ?></p>
        </div>
    </div>

    <h2>Derniers Signalements</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentSignalements as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['titre']) ?></td>
                <td><?= $s['statut'] ?></td>
                <td><?= $s['priorite'] ?></td>
                <td><?= $s['categorie_nom'] ?? 'N/A' ?></td>
                <td><?= $s['auteur_nom'] ?? 'Anonyme' ?></td>
                <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                <td class="actions">
                    <a href="/signalement/<?= $s['id'] ?>">Voir</a>
                    <a href="/backoffice/signalement/<?= $s['id'] ?>/edit">Éditer</a>
                    <form method="post" action="/signalement/<?= $s['id'] ?>/supprimer" style="display:inline;">
                        <button type="submit" onclick="return confirm('Supprimer ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Dernières Interventions</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Statut</th>
                <th>Technicien</th>
                <th>Date Planifiée</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentInterventions as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= htmlspecialchars($i['titre']) ?></td>
                <td><?= $i['statut'] ?></td>
                <td><?= $i['technicien_nom'] ?? 'Non assigné' ?></td>
                <td><?= $i['date_planifiee'] ? date('d/m/Y', strtotime($i['date_planifiee'])) : 'N/A' ?></td>
                <td class="actions">
                    <a href="/intervention/<?= $i['id'] ?>">Voir</a>
                    <form method="post" action="/backoffice/intervention/<?= $i['id'] ?>/assigner" style="display:inline;">
                        <select name="technicien_id">
                            <option value="">Assigner</option>
                            <?php foreach ($techniciens as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= $t['prenom'] ?> <?= $t['nom'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Assigner</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>