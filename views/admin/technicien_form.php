<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - <?= $technicien ? 'Éditer' : 'Ajouter' ?> Technicien</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; }
        button { margin-top: 20px; padding: 10px; }
    </style>
</head>
<body>
    <h1>Backoffice - <?= $technicien ? 'Éditer' : 'Ajouter' ?> Technicien</h1>
    <nav>
        <a href="/backoffice">Accueil</a> |
        <a href="/backoffice/techniciens">Techniciens</a> |
        <a href="/auth/deconnexion">Déconnexion</a>
    </nav>

    <form method="post">
        <label>Nom: <input type="text" name="nom" value="<?= htmlspecialchars($technicien['nom'] ?? '') ?>" required></label>
        <label>Prénom: <input type="text" name="prenom" value="<?= htmlspecialchars($technicien['prenom'] ?? '') ?>" required></label>
        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($technicien['email'] ?? '') ?>" required></label>
        <label>Téléphone: <input type="text" name="telephone" value="<?= htmlspecialchars($technicien['telephone'] ?? '') ?>"></label>
        <label>Mot de passe <?= $technicien ? '(laisser vide pour ne pas changer)' : '' ?>: <input type="password" name="password" <?= $technicien ? '' : 'required' ?>></label>
        <button type="submit"><?= $technicien ? 'Mettre à jour' : 'Ajouter' ?></button>
    </form>

    <?php if ($flash): ?>
    <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>;"><?= $flash['message'] ?></p>
    <?php endif; ?>
</body>
</html>