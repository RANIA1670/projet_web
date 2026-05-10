<?php
/**
 * Vue Front-Office : Créer un nouveau post — validation avancée
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    $_SESSION['user_id'] = 1;
}

require_once __DIR__ . '/../../config/ForumRedirect.php';
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Poll.php';
require_once __DIR__ . '/../../models/EmailSubscription.php';

$controller = new ForumController();
$errors     = [];
$old        = ['title' => '', 'content' => '', 'poll_question' => '', 'poll_option_1' => '', 'poll_option_2' => '', 'poll_option_3' => '', 'poll_option_4' => '', 'notify_email' => '', 'notify_enabled' => '0'];
$currentUserId = (int)$_SESSION['user_id'];
$isAjax     = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim((string)($_POST['title']   ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $pollQuestion = trim((string)($_POST['poll_question'] ?? ''));
    $notifyEmail = trim((string)($_POST['notify_email'] ?? ''));
    $notifyEnabled = isset($_POST['notify_enabled']) ? '1' : '0';
    $pollOptions = [
        trim((string)($_POST['poll_option_1'] ?? '')),
        trim((string)($_POST['poll_option_2'] ?? '')),
        trim((string)($_POST['poll_option_3'] ?? '')),
        trim((string)($_POST['poll_option_4'] ?? '')),
    ];
    $old     = [
        'title' => $title,
        'content' => $content,
        'poll_question' => $pollQuestion,
        'poll_option_1' => $pollOptions[0],
        'poll_option_2' => $pollOptions[1],
        'poll_option_3' => $pollOptions[2],
        'poll_option_4' => $pollOptions[3],
        'notify_email' => $notifyEmail,
        'notify_enabled' => $notifyEnabled,
    ];

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
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => reset($errors) ?? 'Erreur de validation']);
            exit;
        }
    } else {
        if ($notifyEnabled === '1' && !filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['notify_email'] = 'Adresse email invalide.';
        }
    }

    if (empty($errors)) {
        if ($controller->createPost($currentUserId, $title, $content)) {
            $latestPost = Post::findLatestByUserId($currentUserId);
            if ($pollQuestion !== '') {
                if ($latestPost) {
                    Poll::createForPost($latestPost->getId(), $pollQuestion, $pollOptions);
                }
            }
            if ($latestPost && $notifyEnabled === '1' && $notifyEmail !== '') {
                EmailSubscription::subscribe($latestPost->getId(), $notifyEmail);
            }
            if ($isAjax) {
                echo json_encode(['success' => true]);
                exit;
            }
            exit;
        }
        $errors['global'] = 'Erreur lors de la création. Veuillez réessayer.';
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $errors['global']]);
            exit;
        }
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
        /* Même charte que la liste du forum (list_posts) — une seule identité visuelle */
        :root {
            --bg: #F4F6F8;
            --surface: #FFFFFF;
            --surface2: #EEF1F4;
            --accent: #2ECC71;
            --accent-dark: #27ae60;
            --accent-warn: #F39C12;
            --accent-error: #E74C3C;
            --navy: #34495E;
            --sidebar: #2F3C4F;
            --link-muted: #9BA4B5;
            --text: #2C3E50;
            --muted: #7F8C8D;
            --border: #E8ECF0;
            --radius: 10px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .hero {
            background: var(--sidebar);
            border-bottom: 1px solid rgba(0,0,0,.12);
            padding: 36px 24px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(46, 204, 113, 0.14) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero h1 {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
            position: relative;
        }
        .hero p {
            color: var(--link-muted);
            font-size: 1rem;
            max-width: 520px;
            margin: 0 auto 18px;
            line-height: 1.55;
            position: relative;
        }
        .hero-back {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            font-size: .9rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: var(--radius);
            border: 1px solid rgba(255,255,255,.2);
            transition: background .2s, border-color .2s;
        }
        .hero-back:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.35);
        }

        .page { max-width: 760px; margin: 0 auto; padding: 32px 20px 48px; }

        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px 36px;
            box-shadow: 0 4px 20px rgba(46, 204, 113, 0.08);
        }
        .form-card h2 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .form-subtitle { color: var(--muted); font-size: .9rem; margin-bottom: 24px; }

        .rules-box {
            background: rgba(46, 204, 113, 0.08);
            border: 1px solid rgba(46, 204, 113, 0.28);
            border-radius: var(--radius);
            padding: 16px 18px;
            margin-bottom: 24px;
            font-size: .825rem;
            color: var(--text);
        }
        .rules-box strong { color: var(--navy); display: block; margin-bottom: 8px; font-size: .875rem; }
        .rules-box ul { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .rules-box li::before { content: '✓ '; color: var(--accent-dark); font-weight: 700; }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.35);
            color: #c0392b;
            border-radius: var(--radius);
            padding: 13px 18px;
            font-size: .875rem;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(46, 204, 113, 0.12);
            border: 1px solid rgba(46, 204, 113, 0.4);
            color: var(--navy);
            border-radius: var(--radius);
            padding: 13px 18px;
            font-size: .9rem;
            margin-bottom: 20px;
        }

        .fg { margin-bottom: 22px; }
        .fg label {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: .78rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 8px;
        }
        .req { color: var(--accent-error); font-weight: 700; }
        .char-count { font-size: .75rem; color: var(--muted); font-weight: 400; text-transform: none; }
        .char-count.warn { color: var(--accent-warn); }
        .char-count.limit { color: var(--accent-error); }

        .fc {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 11px 14px;
            font-size: .9rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .fc:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
        }
        .fc.is-valid { border-color: var(--accent) !important; }
        .fc.is-invalid { border-color: var(--accent-error) !important; box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.12) !important; }
        textarea.fc { resize: vertical; min-height: 220px; line-height: 1.6; }

        .field-error { display: none; font-size: .78rem; color: var(--accent-error); margin-top: 5px; }
        .field-error.visible { display: block; }
        .field-hint { font-size: .775rem; color: var(--muted); margin-top: 5px; }

        .progress-wrap { height: 3px; border-radius: 99px; background: #e8ecf0; margin-top: 8px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 99px; transition: width .3s, background .3s; width: 0; }

        .form-actions { display: flex; gap: 14px; margin-top: 28px; flex-wrap: wrap; }
        .btn-submit {
            flex: 1;
            min-width: 200px;
            padding: 12px 20px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 4px 16px rgba(46, 204, 113, 0.35);
            transition: opacity .2s, transform .2s;
        }
        .btn-submit:hover { opacity: .92; transform: translateY(-1px); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .btn-cancel {
            padding: 12px 22px;
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            font-family: inherit;
            transition: color .2s, border-color .2s;
            display: inline-flex;
            align-items: center;
        }
        .btn-cancel:hover { color: var(--navy); border-color: var(--navy); }

        @media (max-width: 540px) {
            .form-card { padding: 22px 16px; }
            .form-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<header class="hero">
    <h1>✍️ Nouvelle discussion</h1>
    <p>Rédigez votre sujet ici, puis publiez-le pour qu’il apparaisse dans la liste des discussions.</p>
    <a href="<?= htmlspecialchars(forum_front_url('page=home')) ?>" class="hero-back">← Retour au forum</a>
</header>

<div class="page">
    <div class="form-card">
        <h2>Créer une discussion</h2>
        <p class="form-subtitle">Rédigez le titre et le texte de votre sujet. Les règles ci-dessous s’appliquent à toute publication.</p>

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

        <form id="postForm" method="POST">

            <!-- Titre -->
            <div class="fg">
                <label for="title">
                    Titre de la discussion <span class="req">*</span>
                    <span class="char-count" id="titleCount">0 / 150</span>
                </label>
                <input class="fc <?= isset($errors['title']) ? 'is-invalid' : (isset($_POST['title']) ? 'is-valid' : '') ?>"
                       type="text" id="title" name="title"
                       placeholder="Ex : Problème de stationnement rue des Lilas…"
                       value="<?= htmlspecialchars($old['title']) ?>"
                       maxlength="150" minlength="5" required autocomplete="off">
                <div class="progress-wrap"><div class="progress-bar" id="titleBar"></div></div>
                <div class="field-error <?= isset($errors['title']) ? 'visible' : '' ?>" id="titleError">
                    <?= htmlspecialchars($errors['title'] ?? '') ?>
                </div>
                <div class="field-hint">Soyez précis et descriptif · minimum 5 caractères</div>
            </div>

            <!-- Contenu -->
            <div class="fg">
                <label for="content">
                    Contenu <span class="req">*</span>
                    <span class="char-count" id="contentCount">0 / 5000</span>
                </label>
                <textarea class="fc <?= isset($errors['content']) ? 'is-invalid' : (isset($_POST['content']) ? 'is-valid' : '') ?>"
                          id="content" name="content"
                          placeholder="Décrivez votre sujet en détail…"
                          maxlength="5000" minlength="20" required><?= htmlspecialchars($old['content']) ?></textarea>
                <div class="progress-wrap"><div class="progress-bar" id="contentBar"></div></div>
                <div class="field-error <?= isset($errors['content']) ? 'visible' : '' ?>" id="contentError">
                    <?= htmlspecialchars($errors['content'] ?? '') ?>
                </div>
                <div class="field-hint">Minimum 20 caractères · maximum 5 000 caractères</div>
            </div>

            <div class="fg">
                <label for="poll_question">
                    Mini sondage (optionnel)
                    <span class="char-count">2 options minimum si activé</span>
                </label>
                <input class="fc" type="text" id="poll_question" name="poll_question"
                       placeholder="Ex : Quelle priorité pour ce sujet ?"
                       maxlength="255" value="<?= htmlspecialchars($old['poll_question']) ?>">
                <div class="field-hint">Si vous remplissez la question, renseignez au moins 2 options.</div>
            </div>
            <div class="fg" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <input class="fc" type="text" name="poll_option_1" maxlength="255" placeholder="Option 1"
                       value="<?= htmlspecialchars($old['poll_option_1']) ?>">
                <input class="fc" type="text" name="poll_option_2" maxlength="255" placeholder="Option 2"
                       value="<?= htmlspecialchars($old['poll_option_2']) ?>">
                <input class="fc" type="text" name="poll_option_3" maxlength="255" placeholder="Option 3 (facultatif)"
                       value="<?= htmlspecialchars($old['poll_option_3']) ?>">
                <input class="fc" type="text" name="poll_option_4" maxlength="255" placeholder="Option 4 (facultatif)"
                       value="<?= htmlspecialchars($old['poll_option_4']) ?>">
            </div>

            <div class="fg">
                <label for="notify_email">
                    Notifications email (optionnel)
                    <span class="char-count">Recevoir un email à chaque réponse</span>
                </label>
                <input class="fc <?= isset($errors['notify_email']) ? 'is-invalid' : '' ?>" type="email" id="notify_email" name="notify_email"
                       placeholder="exemple@domaine.com" value="<?= htmlspecialchars($old['notify_email']) ?>">
                <div class="field-error <?= isset($errors['notify_email']) ? 'visible' : '' ?>">
                    <?= htmlspecialchars($errors['notify_email'] ?? '') ?>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;color:var(--muted);font-size:.82rem;text-transform:none;letter-spacing:0;">
                    <input type="checkbox" name="notify_enabled" value="1" <?= $old['notify_enabled'] === '1' ? 'checked' : '' ?>>
                    Activer le bouton "Send email" (abonnement aux réponses)
                </label>
            </div>

            <div class="form-actions">
                <a href="<?= htmlspecialchars(forum_front_url('page=home')) ?>" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">
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
    bar.style.background = len < rule.min ? '#E74C3C'
                          : len >= rule.max ? '#E74C3C'
                          : len >= rule.warnAt ? '#F39C12'
                          : '#2ECC71';

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
    /* Soumission toujours autorisée : validation serveur obligatoire. */
}

fieldIds.forEach(f => {
    const el = document.getElementById(f);
    el.addEventListener('input',  () => validate(f));
    el.addEventListener('blur',   () => validate(f));
    // Init on load if value present (server-side re-fill)
    if (el.value) validate(f);
});

/* ── AJAX Submission ── */
document.getElementById('postForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const title   = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();
    
    if (!state.title || !state.content) {
        alert('Veuillez corriger les erreurs avant de publier.');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?page=home';
        } else {
            alert(data.error || 'Erreur lors de la publication.');
            submitBtn.disabled = false;
        }
    })
    .catch(err => {
        alert('Erreur réseau. Veuillez réessayer.');
        console.error(err);
        submitBtn.disabled = false;
    });
});
</script>
</html>
