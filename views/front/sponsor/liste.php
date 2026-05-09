<?php
// ================================================
//  VUE  : views/front/sponsor/liste.php
//  RÔLE : Liste publique des sponsors
// ================================================
$titrePage = 'Nos Sponsors';
require __DIR__ . '/../../layouts/front_header.php';
?>
<div class="container">
    <h1>💼 Nos Sponsors</h1>

    <form method="GET" action="index.php" class="search-form" style="display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;align-items:flex-end;margin-bottom:18px;">
        <input type="hidden" name="page" value="front_sponsor_liste">
        <div>
            <label for="keyword">Rechercher</label>
            <input type="search" id="keyword" name="keyword" placeholder="Nom, email, téléphone"
                   value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-gris">🔎 Rechercher</button>
            <a href="index.php?page=front_sponsor_liste" class="btn btn-rouge">Réinitialiser</a>
        </div>
    </form>

    <div class="cards">
        <?php if (empty($sponsors)): ?>
            <p style="color:#aaa;">Aucun sponsor pour le moment.</p>
        <?php else: ?>
            <?php foreach ($sponsors as $sp): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($sp['nom']) ?></h3>
                    <p>📧 <?= htmlspecialchars($sp['email']) ?></p>
                    <p>📞 <?= htmlspecialchars($sp['telephone']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
