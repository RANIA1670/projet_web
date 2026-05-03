<?php
/**
 * Vue Front-Office : Créer un nouveau post — validation avancée
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($currentUserId === 0) { header('Location: login.php'); exit; }

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';

$controller = new ForumController();
$errors     = [];
$old        = ['title' => '', 'content' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim((string)($_POST['title']   ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $old     = ['title' => $title, 'content' => $content];

    $v = (new FormValidator())
        ->required('title',   $title,   'Le titre')
        ->minLength('title',  $title,   5,   'Le titre')
        ->maxLength('title',  $title,   150, 'Le titre')
        ->noScript('title',   $title,   'Le titre')
        ->noExcessiveRepeat('title', $title, 6, 'Le titre')
        ->hasMeaningfulContent('title', $title, 'Le titre')
        ->required('content',  $content,  'Le contenu')
        ->minLength('content', $content,  20,  'Le contenu')
        ->maxLength('content', $content,  5000,'Le contenu')
        ->noScript('content',  $content,  'Le contenu')
        ->noExcessiveRepeat('content', $content, 8, 'Le contenu')
        ->hasMeaningfulContent('content', $content, 'Le contenu');

    if ($v->fails()) {
        $errors = $v->getErrors();
    } else {
        if ($controller->createPost($currentUserId, $title, $content)) {
            header('Location: list_posts.php?created=1');
            exit;
        }
        $errors['global'] = 'Erreur lors de la création. Veuillez réessayer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une discussion — Forum CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117; --surface: #161b27; --surface2: #1f2535;
            --accent: #4f8ef7; --accent2: #43e97b; --accent4: #f64f59;
            --text: #e6edf3; --muted: #7d8590; --border: rgba(255,255,255,.08);
            --radius: 14px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

        .topbar {
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 28px; display:flex; align-items:center; gap:16px;
        }
        .back-link {
            color:var(--muted); text-decoration:none; font-size:.875rem; font-weight:500;
            display:flex; align-items:center; gap:6px; transition:color .2s;
        }
        .back-link:hover { color:var(--text); }
        .topbar-title { font-weight:700; font-size:1rem; }

        .page { max-width:760px; margin:0 auto; padding:40px 20px; }

        /* ── Form card ── */
        .form-card {
            background:var(--surface); border:1px solid var(--border);
            border-radius:var(--radius); padding:36px;
        }
        .form-card h1 {
            font-size:1.6rem; font-weight:800; margin-bottom:6px;
        }
        .form-subtitle { color:var(--muted); font-size:.875rem; margin-bottom:28px; }

        /* ── Rules box ── */
        .rules-box {
            background:rgba(79,142,247,.07); border:1px solid rgba(79,142,247,.18);
            border-radius:10px; padding:16px 20px; margin-bottom:28px;
            font-size:.825rem; color:var(--muted);
        }
        .rules-box strong { color:var(--accent); display:block; margin-bottom:8px; font-size:.875rem; }
        .rules-box ul { list-style:none; display:flex; flex-direction:column; gap:4px; }
        .rules-box li::before { content:'• '; color:var(--accent); }

        /* ── Global error ── */
        .alert-error {
            background:rgba(246,79,89,.10); border:1px solid rgba(246,79,89,.28);
            color:var(--accent4); border-radius:10px; padding:13px 18px;
            font-size:.875rem; margin-bottom:22px;
        }

        /* ── Form group ── */
        .fg { margin-bottom:22px; }
        .fg label {
            display:flex; justify-content:space-between; align-items:baseline;
            font-size:.825rem; font-weight:600; color:var(--muted);
            text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;
        }
        .char-count { font-size:.75rem; color:var(--muted); font-weight:400; text-transform:none; }
        .char-count.warn  { color:var(--accent3, #f7971e); }
        .char-count.limit { color:var(--accent4); }

        .fc {
            width:100%; background:var(--surface2); border:1px solid var(--border);
            color:var(--text); border-radius:9px; padding:11px 14px;
            font-size:.9rem; font-family:inherit; outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .fc:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(79,142,247,.15); }
        .fc.is-valid   { border-color:var(--accent2) !important; }
        .fc.is-invalid { border-color:var(--accent4) !important; box-shadow:0 0 0 3px rgba(246,79,89,.12) !important; }
        textarea.fc { resize:vertical; min-height:220px; line-height:1.6; }

        /* Field error message */
        .field-error {
            display:none; font-size:.78rem; color:var(--accent4);
            margin-top:5px; padding-left:2px;
        }
        .field-error.visible { display:block; }

        /* Field hint */
        .field-hint { font-size:.775rem; color:var(--muted); margin-top:5px; }

        /* Progress bar */
        .progress-wrap { height:3px; border-radius:99px; background:var(--surface2); margin-top:8px; overflow:hidden; }
        .progress-bar  { height:100%; border-radius:99px; transition:width .3s, background .3s; width:0; }

        /* ── Actions ── */
        .form-actions { display:flex; gap:14px; margin-top:28px; }
        .btn-submit {
            flex:1; padding:12px; background:linear-gradient(135deg,var(--accent),#7b6cf6);
            color:#fff; border:none; border-radius:9px; font-size:.95rem; font-weight:700;
            cursor:pointer; font-family:inherit; transition:opacity .2s, transform .2s;
        }
        .btn-submit:hover { opacity:.88; transform:translateY(-1px); }
        .btn-submit:disabled { opacity:.5; cursor:not-allowed; transform:none; }
        .btn-cancel {
            padding:12px 24px; background:transparent; color:var(--muted);
            border:1px solid var(--border); border-radius:9px; font-size:.9rem;
            font-weight:500; cursor:pointer; text-decoration:none; font-family:inherit;
            transition:all .2s; display:inline-flex; align-items:center;
        }
        .btn-cancel:hover { color:var(--text); border-color:rgba(255,255,255,.15); }

        @media(max-width:540px){
            .form-card { padding:22px 16px; }
            .form-actions { flex-direction:column; }
        }
    </style>
</head>
<body>

<nav class="topbar">
    <a href="list_posts.php" class="back-link">← Retour au forum</a>
    <span class="topbar-title">✍️ Nouvelle discussion</span>
</nav>

<div class="page">
    <div class="form-card">
        <h1>✍️ Créer une discussion</h1>
        <p class="form-subtitle">Partagez votre sujet avec la communauté CityZen</p>

        <!-- Rules -->
        <div class="rules-box">
            <strong>📋 Règles de publication</strong>
            <ul>
                <li>Titre : 5 à 150 caractères, significatif</li>
                <li>Contenu : 20 à 5 000 caractères</li>
                <li>Pas de balises HTML ni de scripts</li>
                <li>Pas de répétitions excessives ou de mots promotionnels</li>
                <li>Soyez respectueux et constructif</li>
            </ul>
        </div>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form id="postForm" method="POST" novalidate>

            <!-- Titre -->
            <div class="fg">
                <label for="title">
                    Titre de la discussion <span style="color:var(--accent4)">*</span>
                    <span class="char-count" id="titleCount">0 / 150</span>
                </label>
                <input class="fc <?= isset($errors['title']) ? 'is-invalid' : (isset($_POST['title']) ? 'is-valid' : '') ?>"
                       type="text" id="title" name="title"
                       placeholder="Ex : Problème de stationnement rue des Lilas…"
                       value="<?= htmlspecialchars($old['title']) ?>"
                       maxlength="150" autocomplete="off">
                <div class="progress-wrap"><div class="progress-bar" id="titleBar"></div></div>
                <div class="field-error <?= isset($errors['title']) ? 'visible' : '' ?>" id="titleError">
                    <?= htmlspecialchars($errors['title'] ?? '') ?>
                </div>
                <div class="field-hint">Soyez précis et descriptif · minimum 5 caractères</div>
            </div>

            <!-- Contenu -->
            <div class="fg">
                <label for="content">
                    Contenu <span style="color:var(--accent4)">*</span>
                    <span class="char-count" id="contentCount">0 / 5000</span>
                </label>
                <textarea class="fc <?= isset($errors['content']) ? 'is-invalid' : (isset($_POST['content']) ? 'is-valid' : '') ?>"
                          id="content" name="content"
                          placeholder="Décrivez votre sujet en détail…"
                          maxlength="5000"><?= htmlspecialchars($old['content']) ?></textarea>
                <div class="progress-wrap"><div class="progress-bar" id="contentBar"></div></div>
                <div class="field-error <?= isset($errors['content']) ? 'visible' : '' ?>" id="contentError">
                    <?= htmlspecialchars($errors['content'] ?? '') ?>
                </div>
                <div class="field-hint">Minimum 20 caractères · maximum 5 000 caractères</div>
            </div>

            <div class="form-actions">
                <a href="list_posts.php" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    ✅ Publier la discussion
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ── Validation JS avancée ── */
const RULES = {
    title: {
        min: 5, max: 150, label: 'Le titre',
        warnAt: 120,
        checks: [
            v => v.trim().length === 0       ? 'Le titre est obligatoire.'                          : null,
            v => v.trim().length < 5         ? 'Le titre doit contenir au moins 5 caractères.'      : null,
            v => v.trim().length > 150       ? 'Le titre ne peut pas dépasser 150 caractères.'      : null,
            v => /<[^>]+>/.test(v)           ? 'Le titre ne doit pas contenir de balises HTML.'     : null,
            v => /(.)\1{5,}/.test(v)        ? 'Le titre contient une répétition excessive.'        : null,
            v => !/\p{L}/u.test(v)          ? 'Le titre doit contenir au moins quelques lettres.'  : null,
        ]
    },
    content: {
        min: 20, max: 5000, label: 'Le contenu',
        warnAt: 4500,
        checks: [
            v => v.trim().length === 0       ? 'Le contenu est obligatoire.'                           : null,
            v => v.trim().length < 20        ? 'Le contenu doit contenir au moins 20 caractères.'      : null,
            v => v.trim().length > 5000      ? 'Le contenu ne peut pas dépasser 5 000 caractères.'     : null,
            v => /<script/i.test(v)          ? 'Le contenu ne doit pas contenir de scripts.'           : null,
            v => /(.)\1{7,}/.test(v)        ? 'Le contenu contient une répétition excessive.'         : null,
            v => !/\p{L}/u.test(v)          ? 'Le contenu doit contenir au moins quelques lettres.'   : null,
        ]
    }
};

const fieldIds = Object.keys(RULES);
const state    = {};
fieldIds.forEach(f => state[f] = false);

function validate(field) {
    const rule  = RULES[field];
    const el    = document.getElementById(field);
    const errEl = document.getElementById(field + 'Error');
    const count = document.getElementById(field + 'Count');
    const bar   = document.getElementById(field + 'Bar');
    const val   = el.value;
    const len   = val.length;

    // char counter
    count.textContent = len + ' / ' + rule.max;
    count.className   = 'char-count' + (len >= rule.max ? ' limit' : len >= rule.warnAt ? ' warn' : '');

    // progress bar
    const pct = Math.min(len / rule.max * 100, 100);
    bar.style.width = pct + '%';
    bar.style.background = len < rule.min ? '#f64f59'
                          : len >= rule.max ? '#f64f59'
                          : len >= rule.warnAt ? '#f7971e'
                          : '#43e97b';

    // rule checks
    let error = null;
    for (const check of rule.checks) {
        error = check(val);
        if (error) break;
    }

    if (error) {
        el.classList.add('is-invalid'); el.classList.remove('is-valid');
        errEl.textContent = error; errEl.classList.add('visible');
        state[field] = false;
    } else {
        el.classList.remove('is-invalid'); el.classList.add('is-valid');
        errEl.classList.remove('visible');
        state[field] = true;
    }
    updateSubmit();
}

function updateSubmit() {
    document.getElementById('submitBtn').disabled = !fieldIds.every(f => state[f]);
}

fieldIds.forEach(f => {
    const el = document.getElementById(f);
    el.addEventListener('input',  () => validate(f));
    el.addEventListener('blur',   () => validate(f));
    // Init on load if value present (server-side re-fill)
    if (el.value) validate(f);
});
</script>
</body>
</html>
