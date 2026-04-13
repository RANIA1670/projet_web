<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Événements</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; margin: 2px; text-decoration: none; }
        .btn-add { background: green; color: white; padding: 10px; display: inline-block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Liste des Événements</h1>
    <a href="index.php?controller=event&action=create" class="btn-add">+ Ajouter un événement</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Date</th>
                <th>Lieu</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($events)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">Aucun événement trouvé</td>
            </tr>
            <?php else: ?>
                <?php foreach($events as $event): ?>
                <tr>
                    <td><?= $event['id'] ?></td>
                    <td><?= htmlspecialchars($event['name']) ?></td>
                    <td><?= $event['event_date'] ?></td>
                    <td><?= htmlspecialchars($event['location']) ?></td>
                    <td>
                        <a href="index.php?controller=event&action=show&id=<?= $event['id'] ?>" class="btn">Voir</a>
                        <a href="index.php?controller=event&action=edit&id=<?= $event['id'] ?>" class="btn">Modifier</a>
                        <a href="index.php?controller=event&action=delete&id=<?= $event['id'] ?>" 
                           onclick="return confirm('Supprimer cet événement ?')" class="btn">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>