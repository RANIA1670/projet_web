<?php

require_once __DIR__ . '/../includes/layout.php';

cityzen_require_agent();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['set_user_role'])) {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $_SESSION['cityzen_dash_flash'] = ['type' => 'error', 'msg' => 'Jeton de securite invalide. Rechargez la page.'];
    } else {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $role = (string) ($_POST['new_role'] ?? '');
        if (!in_array($role, ['admin', 'user'], true)) {
            $_SESSION['cityzen_dash_flash'] = ['type' => 'error', 'msg' => 'Role invalide.'];
        } else {
            $res = cityzen_update_user_role($uid, $role);
            if (!$res['ok']) {
                $_SESSION['cityzen_dash_flash'] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Erreur.')];
            } else {
                $sid = (int) ($_SESSION['cityzen_user']['id'] ?? 0);
                if ($sid === $uid && isset($res['user']) && is_array($res['user'])) {
                    cityzen_apply_session_user($res['user']);
                }
                $_SESSION['cityzen_dash_flash'] = ['type' => 'success', 'msg' => 'Role mis a jour.'];
            }
        }
    }
    header('Location: ' . cityzen_asset('admin/dashboard.php') . '#utilisateurs', true, 303);
    exit;
}

$dashFlash = $_SESSION['cityzen_dash_flash'] ?? null;
unset($_SESSION['cityzen_dash_flash']);
$allUsers = cityzen_users_load()['users'];

cityzen_render_head('Tableau de bord');
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'dashboard' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Tableau de bord - <?= htmlspecialchars($cityzen['city_name']) ?></h1>
        <p class="admin-header-lead">Vue agents : les memes indicateurs et signalements que le portail public, orientes traitement.</p>
      </div>
      <div class="admin-user">
        <span><?= htmlspecialchars($cityzen['current_date']) ?></span>
        <button class="avatar avatar-warning" type="button">O</button>
        <button class="avatar avatar-success" type="button"><?= htmlspecialchars(cityzen_user_initials()) ?></button>
      </div>
    </header>

    <section class="admin-stats">
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-green" data-count-to="<?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?>"><?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['reports']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['reports']['trend']) ?></small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong data-count-to="<?= htmlspecialchars(rtrim($cityzen['stats']['resolution']['value'], '%')) ?>" data-count-suffix="%"><?= htmlspecialchars($cityzen['stats']['resolution']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['resolution']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['resolution']['trend']) ?></small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-orange" data-count-to="<?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?>"><?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['projects']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['projects']['trend']) ?></small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-red" data-count-to="<?= htmlspecialchars((string) $cityzen['stats']['alerts']['value']) ?>"><?= htmlspecialchars((string) $cityzen['stats']['alerts']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['alerts']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['alerts']['trend']) ?></small>
      </article>
    </section>

    <section class="admin-grid">
      <article class="panel" id="projets">
        <h2>Participation par quartier</h2>
        <div class="district-list">
          <?php foreach ($cityzen['districts'] as $district): ?>
            <div class="district-row">
              <span><?= htmlspecialchars($district['name']) ?></span>
              <div class="progress-track">
                <div class="progress-fill <?= htmlspecialchars($district['tone']) ?>" data-width="<?= (int) $district['value'] ?>" style="width: 0"></div>
              </div>
              <strong><?= htmlspecialchars((string) $district['value']) ?>%</strong>
            </div>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="panel" id="signalements">
        <h2>Signalements cette semaine</h2>
        <div class="week-chart">
          <?php foreach ($cityzen['weekly_reports'] as $bar): ?>
            <div class="bar-item">
              <div class="bar <?= htmlspecialchars($bar['tone']) ?>" data-height="<?= max(18, (int) $bar['value']) ?>" style="height: 10px"></div>
              <span><?= htmlspecialchars($bar['day']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-cards">
          <div class="summary-card success">
            <strong><?= htmlspecialchars((string) $cityzen['weekly_summary']['resolved']) ?></strong>
            <span>Resolus</span>
          </div>
          <div class="summary-card danger">
            <strong><?= htmlspecialchars((string) $cityzen['weekly_summary']['pending']) ?></strong>
            <span>En attente</span>
          </div>
        </div>
      </article>
    </section>

    <section class="panel users-admin-section" id="utilisateurs">
      <h2>Comptes utilisateurs</h2>
      <p class="panel-lead">Les inscriptions creent un compte <strong>citoyen</strong>. Promouvez ou retirez le role administrateur ici (il doit toujours rester au moins un admin).</p>

      <?php if (is_array($dashFlash) && isset($dashFlash['msg'])): ?>
        <p class="admin-flash <?= ($dashFlash['type'] ?? '') === 'success' ? 'admin-flash-success' : 'admin-flash-error' ?>" role="status"><?= htmlspecialchars((string) $dashFlash['msg']) ?></p>
      <?php endif; ?>

      <div class="reports-table users-table">
        <div class="table-head users-table-head">
          <span>Utilisateur</span>
          <span>Role</span>
          <span>Inscription</span>
          <span>Action</span>
        </div>
        <?php foreach ($allUsers as $urow): ?>
          <?php
            $uid = (int) ($urow['id'] ?? 0);
            $uname = (string) ($urow['username'] ?? '');
            $urole = (string) ($urow['role'] ?? 'user');
            $udate = (string) ($urow['created_at'] ?? '');
          ?>
          <div class="table-row users-table-row">
            <span><strong><?= htmlspecialchars($uname) ?></strong></span>
            <span><em class="pill <?= $urole === 'admin' ? 'progress' : 'done' ?>"><?= $urole === 'admin' ? 'Administrateur' : 'Citoyen' ?></em></span>
            <span class="users-date"><?= htmlspecialchars($udate) ?></span>
            <span>
              <form class="users-role-form" method="post" action="">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
                <input type="hidden" name="set_user_role" value="1">
                <input type="hidden" name="user_id" value="<?= $uid ?>">
                <label class="users-role-label">
                  <span class="visually-hidden">Nouveau role</span>
                  <select name="new_role" class="users-role-select">
                    <option value="user" <?= $urole === 'user' ? 'selected' : '' ?>>Citoyen</option>
                    <option value="admin" <?= $urole === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                  </select>
                </label>
                <button type="submit" class="users-role-submit">Appliquer</button>
              </form>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel reports-table-section" id="citoyens">
      <h2>Derniers signalements - A traiter</h2>
      <p class="panel-lead">Aligne sur la liste publique ; ici en mode file de traitement.</p>
      <div class="reports-table">
        <div class="table-head">
          <span>Incident</span>
          <span>Quartier</span>
          <span>Categorie</span>
          <span>Date</span>
          <span>Statut</span>
        </div>
        <?php foreach ($cityzen['recent_reports'] as $report): ?>
          <div class="table-row interactive-row" data-row>
            <span><?= htmlspecialchars($report['title']) ?></span>
            <span><?= htmlspecialchars($report['district']) ?></span>
            <span><?= htmlspecialchars($report['category']) ?></span>
            <span><?= htmlspecialchars($report['date']) ?></span>
            <span><em class="pill <?= htmlspecialchars($report['severity']) ?>"><?= htmlspecialchars($report['status']) ?></em></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
