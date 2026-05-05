<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Techniciens</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .actions { white-space: nowrap; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Backoffice - Gestion des Techniciens</h1>
    <nav>
        <a href="/backoffice">Accueil</a> |
        <a href="/backoffice/signalements">Signalements</a> |
        <a href="/backoffice/interventions">Interventions</a> |
        <a href="/backoffice/techniciens">Techniciens</a> |
        <a href="/auth/deconnexion">Déconnexion</a>
    </nav>

    <p><a href="/backoffice/technicien/creer">Ajouter un Technicien</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($techniciens as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['nom']) ?></td>
                <td><?= htmlspecialchars($t['prenom']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= $t['telephone'] ?? 'N/A' ?></td>
                <td class="actions">
                    <a href="/backoffice/technicien/<?= $t['id'] ?>/edit">Éditer</a>
                    <form method="post" action="/backoffice/technicien/<?= $t['id'] ?>/supprimer" style="display:inline;">
                        <button type="submit" onclick="return confirm('Supprimer ce technicien ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>