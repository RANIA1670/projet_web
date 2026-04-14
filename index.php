<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CityZen — Projet</title>
  <style>
    body { margin: 0; font-family: 'Inter', sans-serif; background: #F4F7FB; color: #0F172A; }
    .page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .card { width: 100%; max-width: 760px; background: #fff; border: 1px solid #E2E8F0; border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.08); padding: 40px; }
    .brand { display: inline-flex; align-items: center; gap: 10px; font-size: 1.75rem; font-weight: 800; }
    .brand span { color: #22C55E; }
    .subtitle { margin-top: 12px; color: #475569; line-height: 1.7; }
    .actions { display: grid; gap: 16px; margin-top: 32px; }
    .actions a { display: block; padding: 18px 22px; border-radius: 18px; text-decoration: none; font-weight: 700; color: #fff; background: #22C55E; text-align: center; transition: transform .2s, box-shadow .2s; }
    .actions a:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(34,197,94,.18); }
    .note { margin-top: 24px; color: #64748B; font-size: .95rem; }
  </style>
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="brand">City<span>Zen</span></div>
      <p class="subtitle">Utilisez la structure du projet pour accéder à l’interface front-end client ou à l’administration back-end.</p>
      <div class="actions">
        <a href="views/front/index.php">Front-end client</a>
        <a href="views/back/index.php">Administration</a>
      </div>
      <p class="note">Si vous souhaitez étendre ce projet, je peux aussi créer les dossiers `controllers`, `models` et `assets`.</p>
    </div>
  </div>
</body>
</html>
