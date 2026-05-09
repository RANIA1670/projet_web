<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titrePage ?? 'Admin' ?> — CityZen Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

<!-- Navbar top -->
<nav>
    <span class="nav-logo">City<span>Zen</span> <small style="font-size:.7rem;color:#E67E22;font-weight:600;">ADMIN</small></span>
    <a href="index.php">← Front Office</a>
</nav>

<div class="admin-layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-title">Principal</div>
        <a href="index.php?page=back_dashboard"
           <?= ($page??'')==='back_dashboard'?'class="actif"':'' ?>>🏠 Dashboard</a>

        <div class="sidebar-title">Gestion</div>
        <a href="index.php?page=back_event_liste"
           <?= str_contains($page??'','back_event')?'class="actif"':'' ?>>🎫 Événements</a>
        <a href="index.php?page=back_sponsor_liste"
           <?= str_contains($page??'','back_sponsor')?'class="actif"':'' ?>>💼 Sponsors</a>
        <a href="index.php?page=back_participation_liste"
           <?= str_contains($page??'','back_participation')?'class="actif"':'' ?>>👥 Participations</a>
        <a href="index.php?page=back_avis_liste"
           <?= str_contains($page??'','back_avis')?'class="actif"':'' ?>>⭐ Avis & Notations</a>
    </div>

    <!-- Contenu principal injecté ici -->
    <div class="main-admin">
