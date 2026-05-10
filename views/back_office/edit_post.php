<?php
/**
 * Vue Back-Office : Modifier un post — validation avancée
 */
require_once __DIR__ . '/../../config/ForumRedirect.php';

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if ($currentUserId === 0 || !$isAdmin) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}
$postId = (int)$_GET['id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Post.php';

$post = Post::findById($postId);
if (!$post) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}

$errors  = [];
$old     = ['title' => $post->getTitle(), 'content' => $post->getContent()];
$success = '';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strcasecmp(trim((string)$_SERVER['HTTP_X_REQUESTED_WITH']), 'XMLHttpRequest') === 0;

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
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'errors' => $errors,
            ]);
            exit;
        }
    } else {
        $controller = new ForumController();
        if ($controller->updatePost($postId, $title, $content)) {
            $success = 'Post modifié avec succès.';
            $post    = Post::findById($postId);
            $old     = ['title' => $post->getTitle(), 'content' => $post->getContent()];
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok' => true,
                    'message' => $success,
                    'post' => [
                        'title' => $old['title'],
                        'content' => $old['content'],
                    ],
                ]);
                exit;
            }
        } else {
            $errors['global'] = 'Erreur lors de la modification.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'errors' => $errors,
                ]);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la discussion — Admin CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#F4F6F8;--surface:#2F3C4F;--surface2:#EEF1F4;--card:#FFFFFF;--accent:#34495E;--accent2:#2ECC71;--accent3:#F39C12;--accent4:#E74C3C;--text:#2C3E50;--muted:#7F8C8D;--nav-muted:#9BA4B5;--border:rgba(0,0,0,.08);--radius:12px;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
        .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between;gap:16px;}
        .topbar-brand{font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.3px;}
        .topbar-nav{display:flex;gap:10px;}
        .nav-btn{padding:8px 16px;border-radius:8px;text-decoration:none;font-size:.85rem;font-weight:500;color:var(--nav-muted);border:1px solid rgba(255,255,255,.15);transition:all .2s;}
        .nav-btn:hover{background:rgba(255,255,255,.08);color:#fff;}
        .nav-btn.active{background:var(--accent2);color:#fff;border-color:var(--accent2);}
        .page{max-width:760px;margin:0 auto;padding:40px 20px;}
        .form-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:36px;box-shadow:0 2px 14px rgba(0,0,0,.04);}
        .form-card h1{font-size:1.5rem;font-weight:800;margin-bottom:6px;}
        .form-subtitle{color:var(--muted);font-size:.875rem;margin-bottom:20px;}
        .mode-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(52,73,94,.08);color:var(--accent);border:1px solid rgba(52,73,94,.2);border-radius:20px;padding:4px 14px;font-size:.78rem;font-weight:600;margin-bottom:22px;}
        .meta-row{display:flex;gap:20px;font-size:.8rem;color:var(--muted);background:var(--surface2);border-radius:8px;padding:10px 14px;margin-bottom:22px;flex-wrap:wrap;}
        .alert-error{background:rgba(231,76,60,.08);border:1px solid rgba(231,76,60,.24);color:var(--accent4);border-radius:10px;padding:13px 18px;font-size:.875rem;margin-bottom:22px;}
        .alert-success{background:rgba(46,204,113,.10);border:1px solid rgba(46,204,113,.3);color:#1f8a4a;border-radius:10px;padding:13px 18px;font-size:.875rem;margin-bottom:22px;}
        .fg{margin-bottom:22px;}
        .fg label{display:flex;justify-content:space-between;align-items:baseline;font-size:.825rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;}
        .char-count{font-size:.75rem;color:var(--muted);font-weight:400;text-transform:none;}
        .char-count.warn{color:var(--accent3);}
        .char-count.limit{color:var(--accent4);}
        .fc{width:100%;background:#fff;border:1px solid #D5D8DC;color:var(--text);border-radius:9px;padding:11px 14px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;}
        .fc:focus{border-color:var(--accent2);box-shadow:0 0 0 3px rgba(46,204,113,.2);}
        .fc.is-valid{border-color:var(--accent2)!important;}
        .fc.is-invalid{border-color:var(--accent4)!important;box-shadow:0 0 0 3px rgba(246,79,89,.12)!important;}
        .fc[disabled]{opacity:.5;cursor:not-allowed;}
        textarea.fc{resize:vertical;min-height:220px;line-height:1.6;}
        .field-error{display:none;font-size:.78rem;color:var(--accent4);margin-top:5px;}
        .field-error.visible{display:block;}
        .field-hint{font-size:.775rem;color:var(--muted);margin-top:5px;}
        .progress-wrap{height:3px;border-radius:99px;background:#e9edf1;margin-top:8px;overflow:hidden;}
        .progress-bar{height:100%;border-radius:99px;transition:width .3s,background .3s;width:0;}
        .form-actions{display:flex;gap:14px;margin-top:28px;}
        .btn-submit{flex:1;padding:12px;background:var(--accent2);color:#fff;border:none;border-radius:9px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .2s,transform .2s;}
        .btn-submit:hover{opacity:.88;transform:translateY(-1px);}
        .btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}
        .btn-cancel{padding:12px 24px;background:#f1f4f7;color:var(--muted);border:1px solid #dde3ea;border-radius:9px;font-size:.9rem;font-weight:500;cursor:pointer;text-decoration:none;font-family:inherit;transition:all .2s;display:inline-flex;align-items:center;}
        .btn-cancel:hover{color:var(--text);border-color:#cfd7df;}
        .submit-status{display:none;margin-top:12px;font-size:.84rem;font-weight:500;}
        .submit-status.show{display:block;}
        .submit-status.pending{color:var(--muted);}
        .submit-status.error{color:var(--accent4);}
        .submit-status.success{color:#1f8a4a;}
        @media(max-width:540px){.form-card{padding:22px 16px;}.form-actions{flex-direction:column;}}
    </style>
</head>
<body>
<nav class="topbar">
    <div class="topbar-brand">🛠️ CityZen Admin</div>
    <div class="topbar-nav">
        <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>" class="nav-btn">🏠 Dashboard</a>
        <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>?page=statistics" class="nav-btn">📈 Statistiques</a>
        <a href="<?= htmlspecialchars(forum_list_url('page=home')) ?>" class="nav-btn">👁️ Forum</a>
        <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>" class="nav-btn active">✏️ Édition</a>
    </div>
</nav>
<div class="page">
    <div class="form-card">
        <h1>✏️ Modifier une discussion</h1>
        <p class="form-subtitle">Édition par l'administrateur</p>
        <div class="mode-badge">🔐 Mode modération — Admin</div>

        <div class="meta-row">
            <span>👤 Auteur : #<?= $post->getUserId() ?></span>
            <span>📅 Créé le : <?= date('d/m/Y à H:i', strtotime($post->getCreatedAt())) ?></span>
        </div>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-success" id="serverSuccess">✅ <?= htmlspecialchars($success) ?></div>
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
                <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">💾 Enregistrer</button>
            </div>
            <p class="submit-status" id="submitStatus" aria-live="polite"></p>
        </form>
    </div>
</div>
<script>
const RULES={
    title:{min:5,max:150,warnAt:120,checks:[v=>v.trim().length===0?'Obligatoire.':null,v=>v.trim().length<5?'Min 5 caractères.':null,v=>v.trim().length>150?'Max 150 caractères.':null,v=>/<[^>]+>/.test(v)?'Pas de HTML.':null,v=>/(.)\1{5,}/.test(v)?'Répétition excessive.':null,v=>!/\p{L}/u.test(v)?'Doit contenir des lettres.':null]},
    content:{min:20,max:5000,warnAt:4500,checks:[v=>v.trim().length===0?'Obligatoire.':null,v=>v.trim().length<20?'Min 20 caractères.':null,v=>v.trim().length>5000?'Max 5000 caractères.':null,v=>/<script/i.test(v)?'Pas de scripts.':null,v=>/(.)\1{7,}/.test(v)?'Répétition excessive.':null,v=>!/\p{L}/u.test(v)?'Doit contenir des lettres.':null]}
};
const state={title:true,content:true};
const formEl=document.getElementById('postForm');
const submitBtn=document.getElementById('submitBtn');
const submitStatus=document.getElementById('submitStatus');
const serverSuccess=document.getElementById('serverSuccess');
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
    let err=null;for(const c of r.checks){err=c(val);if(err)break;}
    if(err){el.classList.add('is-invalid');el.classList.remove('is-valid');errEl.textContent=err;errEl.classList.add('visible');state[f]=false;}
    else{el.classList.remove('is-invalid');el.classList.add('is-valid');errEl.classList.remove('visible');state[f]=true;}
    submitBtn.disabled=!Object.values(state).every(Boolean);
}
Object.keys(RULES).forEach(f=>{
    const el=document.getElementById(f);
    el.addEventListener('input',()=>validate(f));
    el.addEventListener('blur',()=>validate(f));
    if(el.value)validate(f);
});

function setSubmitStatus(text, cls){
    submitStatus.textContent=text;
    submitStatus.className='submit-status show '+cls;
}

function applyServerErrors(errors){
    ['title','content'].forEach(function(name){
        const input=document.getElementById(name);
        const errEl=document.getElementById(name+'Error');
        const msg=(errors && errors[name]) ? String(errors[name]) : '';
        if(msg){
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            errEl.textContent=msg;
            errEl.classList.add('visible');
            state[name]=false;
        }
    });
    submitBtn.disabled=!Object.values(state).every(Boolean);
}

formEl.addEventListener('submit',async function(e){
    e.preventDefault();
    if(serverSuccess){ serverSuccess.style.display='none'; }

    validate('title');
    validate('content');
    if(!Object.values(state).every(Boolean)) return;

    submitBtn.disabled=true;
    setSubmitStatus('Enregistrement en cours...', 'pending');

    try{
        const formData=new FormData(formEl);
        const response=await fetch(window.location.href,{
            method:'POST',
            headers:{'X-Requested-With':'XMLHttpRequest'},
            body:new URLSearchParams(formData),
            credentials:'same-origin'
        });
        const data=await response.json();
        if(!response.ok || !data.ok){
            applyServerErrors(data.errors || {});
            setSubmitStatus((data.errors && data.errors.global) ? data.errors.global : 'Erreur lors de la modification.', 'error');
            return;
        }
        setSubmitStatus(data.message || 'Post modifié avec succès.', 'success');
        setTimeout(function(){
            var base = document.querySelector('a.nav-btn[href]');
            window.location.href = base ? base.getAttribute('href') : 'index.php';
        }, 800);
    }catch(_err){
        setSubmitStatus('Impossible de contacter le serveur.', 'error');
    }finally{
        submitBtn.disabled=!Object.values(state).every(Boolean);
    }
});
</script>
</body>
</html>
