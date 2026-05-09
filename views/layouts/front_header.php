<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titrePage ?? 'CityZen' ?> — CityZen</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

<nav>
    <span class="nav-logo">City<span>Zen</span></span>
    <a href="index.php" <?= ($page??'')==='accueil'?'class="actif"':'' ?>>Accueil</a>
    <a href="index.php?page=front_event_liste" <?= str_contains($page??'','front_event')?'class="actif"':'' ?>>Événements</a>
    <a href="index.php?page=front_sponsor_liste" <?= ($page??'')==='front_sponsor_liste'?'class="actif"':'' ?>>Sponsors</a>
    <a href="index.php?page=back_dashboard" class="nav-admin">⚙ Admin</a>
</nav>
