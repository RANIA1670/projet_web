<?php
/**
 * Vue Front-Office : Modifier un post — validation avancée
 */
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($currentUserId === 0) { header('Location: login.php'); exit; }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header('Location: list_posts.php'); exit; }
$postId = (int)$_GET['id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Post.php';

$controller = new ForumController();
$post       = Post::findById($postId);

if (!$post)                                 { header('Location: list_posts.php'); exit; }
if ($post->getUserId() !== $currentUserId)  { header('Location: view_post.php?id=' . $postId); exit; }

$errors = [];
$old    = ['title' => $post->getTitle(), 'content' => $post->getContent()];

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
        if ($controller->updatePost($postId, $title, $content)) {
            header('Location: view_post.php?id=' . $postId . '&updated=1');
            exit;
        }
        $errors['global'] = 'Erreur lors de la modification. Veuillez réessayer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la discussion — Forum CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0d1117;--surface:#161b27;--surface2:#1f2535;
            --accent:#4f8ef7;--accent2:#43e97b;--accent3:#f7971e;--accent4:#f64f59;
            --text:#e6edf3;--muted:#7d8590;--border:rgba(255,255,255,.08);--radius:14px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
        .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;gap:16px;}
        .back-link{color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:6px;transition:color .2s;}
        .back-link:hover{color:var(--text);}
        .topbar-title{font-weight:700;font-size:1rem;}
        .page{max-width:760px;margin:0 auto;padding:40px 20px;}
        .form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:36px;}
        .form-card h1{font-size:1.6rem;font-weight:800;margin-bottom:6px;}
        .form-subtitle{color:var(--muted);font-size:.875rem;margin-bottom:20px;}
        .info-box{background:rgba(67,233,123,.07);border:1px solid rgba(67,233,123,.18);border-radius:10px;padding:13px 18px;font-size:.825rem;color:var(--muted);margin-bottom:24px;}
        .info-box strong{color:var(--accent2);}
        .alert-error{background:rgba(246,79,89,.10);border:1px solid rgba(246,79,89,.28);color:var(--accent4);border-radius:10px;padding:13px 18px;font-size:.875rem;margin-bottom:22px;}
        .fg{margin-bottom:22px;}
        .fg label{display:flex;justify-content:space-between;align-items:baseline;font-size:.825rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;}
        .char-count{font-size:.75rem;color:var(--muted);font-weight:400;text-transform:none;}
        .char-count.warn{color:var(--accent3);}
        .char-count.limit{color:var(--accent4);}
        .fc{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:9px;padding:11px 14px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;}
        .fc:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,142,247,.15);}
        .fc.is-valid{border-color:var(--accent2)!important;}
        .fc.is-invalid{border-color:var(--accent4)!important;box-shadow:0 0 0 3px rgba(246,79,89,.12)!important;}
        textarea.fc{resize:vertical;min-height:220px;line-height:1.6;}
        .field-error{display:none;font-size:.78rem;color:var(--accent4);margin-top:5px;}
        .field-error.visible{display:block;}
        .field-hint{font-size:.775rem;color:var(--muted);margin-top:5px;}
        .progress-wrap{height:3px;border-radius:99px;background:var(--surface2);margin-top:8px;overflow:hidden;}
        .progress-bar{height:100%;border-radius:99px;transition:width .3s,background .3s;width:0;}
        .meta-row{display:flex;gap:20px;font-size:.8rem;color:var(--muted);background:var(--surface2);border-radius:8px;padding:10px 14px;margin-bottom:22px;}
        .form-actions{display:flex;gap:14px;margin-top:28px;}
        .btn-submit{flex:1;padding:12px;background:linear-gradient(135deg,var(--accent3),#ffd700);color:#111;border:none;border-radius:9px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .2s,transform .2s;}
        .btn-submit:hover{opacity:.88;transform:translateY(-1px);}
        .btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}
        .btn-cancel{padding:12px 24px;background:transparent;color:var(--muted);border:1px solid var(--border);border-radius:9px;font-size:.9rem;font-weight:500;cursor:pointer;text-decoration:none;font-family:inherit;transition:all .2s;display:inline-flex;align-items:center;}
        .btn-cancel:hover{color:var(--text);border-color:rgba(255,255,255,.15);}
        @media(max-width:540px){.form-card{padding:22px 16px;}.form-actions{flex-direction:column;}}
    </style>
</head>
<body>
<nav class="topbar">
    <a href="view_post.php?id=<?= $postId ?>" class="back-link">← Retour à la discussion</a>
    <span class="topbar-title">✏️ Modifier la discussion</span>
</nav>
<div class="page">
    <div class="form-card">
        <h1>✏️ Modifier la discussion</h1>
        <p class="form-subtitle">Seul l'auteur peut modifier son message</p>

        <div class="info-box">
            <strong>ℹ️</strong> La date de dernière modification sera enregistrée automatiquement.
        </div>

        <div class="meta-row">
            <span>👤 Auteur : #<?= $post->getUserId() ?></span>
            <span>📅 Créé le : <?= date('d/m/Y à H:i', strtotime($post->getCreatedAt())) ?></span>
        </div>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form id="postForm" method="POST" novalidate>

            <div class="fg">
                <label for="title">
                    Titre <span style="color:var(--accent4)">*</span>
                    <span class="char-count" id="titleCount">0 / 150</span>
                </label>
                <input class="fc <?= isset($errors['title']) ? 'is-invalid' : 'is-valid' ?>"
                       type="text" id="title" name="title" maxlength="150" autocomplete="off"
                       value="<?= htmlspecialchars($old['title']) ?>">
                <div class="progress-wrap"><div class="progress-bar" id="titleBar"></div></div>
                <div class="field-error <?= isset($errors['title']) ? 'visible' : '' ?>" id="titleError">
                    <?= htmlspecialchars($errors['title'] ?? '') ?>
                </div>
                <div class="field-hint">5 à 150 caractères</div>
            </div>

            <div class="fg">
                <label for="content">
                    Contenu <span style="color:var(--accent4)">*</span>
                    <span class="char-count" id="contentCount">0 / 5000</span>
                </label>
                <textarea class="fc <?= isset($errors['content']) ? 'is-invalid' : 'is-valid' ?>"
                          id="content" name="content" maxlength="5000"><?= htmlspecialchars($old['content']) ?></textarea>
                <div class="progress-wrap"><div class="progress-bar" id="contentBar"></div></div>
                <div class="field-error <?= isset($errors['content']) ? 'visible' : '' ?>" id="contentError">
                    <?= htmlspecialchars($errors['content'] ?? '') ?>
                </div>
                <div class="field-hint">20 à 5 000 caractères</div>
            </div>

            <div class="form-actions">
                <a href="view_post.php?id=<?= $postId ?>" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
const RULES = {
    title:   { min:5,  max:150,  warnAt:120,  checks:[
        v=>v.trim().length===0      ?'Le titre est obligatoire.':null,
        v=>v.trim().length<5        ?'Minimum 5 caractères.':null,
        v=>v.trim().length>150      ?'Maximum 150 caractères.':null,
        v=>/<[^>]+>/.test(v)        ?'Pas de balises HTML.':null,
        v=>/(.)\1{5,}/.test(v)     ?'Répétition excessive détectée.':null,
        v=>!/\p{L}/u.test(v)       ?'Doit contenir au moins quelques lettres.':null,
    ]},
    content: { min:20, max:5000, warnAt:4500, checks:[
        v=>v.trim().length===0      ?'Le contenu est obligatoire.':null,
        v=>v.trim().length<20       ?'Minimum 20 caractères.':null,
        v=>v.trim().length>5000     ?'Maximum 5 000 caractères.':null,
        v=>/<script/i.test(v)       ?'Pas de scripts.':null,
        v=>/(.)\1{7,}/.test(v)     ?'Répétition excessive détectée.':null,
        v=>!/\p{L}/u.test(v)       ?'Doit contenir au moins quelques lettres.':null,
    ]}
};
const state={title:true,content:true};
function validate(f){
    const r=RULES[f],el=document.getElementById(f);
    const errEl=document.getElementById(f+'Error');
    const count=document.getElementById(f+'Count');
    const bar=document.getElementById(f+'Bar');
    const val=el.value,len=val.length;
    count.textContent=len+' / '+r.max;
    count.className='char-count'+(len>=r.max?' limit':len>=r.warnAt?' warn':'');
    bar.style.width=Math.min(len/r.max*100,100)+'%';
    bar.style.background=len<r.min?'#f64f59':len>=r.max?'#f64f59':len>=r.warnAt?'#f7971e':'#43e97b';
    let err=null;
    for(const c of r.checks){err=c(val);if(err)break;}
    if(err){el.classList.add('is-invalid');el.classList.remove('is-valid');errEl.textContent=err;errEl.classList.add('visible');state[f]=false;}
    else{el.classList.remove('is-invalid');el.classList.add('is-valid');errEl.classList.remove('visible');state[f]=true;}
    document.getElementById('submitBtn').disabled=!Object.values(state).every(Boolean);
}
Object.keys(RULES).forEach(f=>{
    const el=document.getElementById(f);
    el.addEventListener('input',()=>validate(f));
    el.addEventListener('blur',()=>validate(f));
    if(el.value)validate(f);
});
</script>
</body>
</html>
