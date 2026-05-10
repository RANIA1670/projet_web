<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page introuvable | CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if(defined('APP_URL')): ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/main.css">
    <?php endif; ?>
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#F4F6F9; }
        .err-wrap { text-align:center; padding:40px 20px; max-width:540px; }
        .err-code { font-family:'Montserrat',sans-serif; font-size:10rem; font-weight:900; color:#2C3E50; line-height:1; margin-bottom:0; background: linear-gradient(135deg,#2C3E50,#27AE60); -webkit-background-clip:text;-webkit-text-fill-color:transparent; }
        .err-title { font-family:'Montserrat',sans-serif; font-size:1.8rem; font-weight:700; color:#2C3E50; margin:12px 0; }
        .err-desc  { color:#7F8C8D; font-size:1rem; line-height:1.7; margin-bottom:32px; }
    </style>
</head>
<body>
    <div class="err-wrap">
        <div class="err-code">404</div>
        <h1 class="err-title">Page introuvable</h1>
        <p class="err-desc">La page que vous cherchez n'existe pas ou a été déplacée. Retournez à l'accueil pour continuer votre navigation.</p>
        <?php if(defined('APP_URL')): ?>
        <a href="<?= APP_URL ?>/" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
        <a href="<?= APP_URL ?>/signalements" class="btn btn-outline btn-lg" style="margin-left:12px;">
            <i class="fas fa-list"></i> Signalements
        </a>
        <?php else: ?>
        <a href="/" style="display:inline-block;padding:12px 28px;background:#27AE60;color:#fff;border-radius:12px;font-family:'Montserrat';font-weight:700;text-decoration:none;">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
        <?php endif; ?>
    </div>
</body>
</html>
