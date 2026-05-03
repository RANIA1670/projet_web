<?php
/**
 * Vue Front-Office : Modifier une réponse — validation avancée
 */
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($currentUserId === 0) { header('Location: login.php'); exit; }

if (!isset($_GET['id'], $_GET['post_id']) || !is_numeric($_GET['id']) || !is_numeric($_GET['post_id'])) {
    header('Location: list_posts.php'); exit;
}
$replyId = (int)$_GET['id'];
$postId  = (int)$_GET['post_id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Reply.php';

$controller = new ForumController();
$reply      = Reply::findById($replyId);
if (!$reply)                                 { header('Location: view_post.php?id=' . $postId); exit; }
if ($reply->getUserId() !== $currentUserId)  { header('Location: view_post.php?id=' . $postId); exit; }

$errors  = [];
$oldText = $reply->getContent();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim((string)($_POST['reply_content'] ?? ''));
    $oldText = $content;

    $v = (new FormValidator())
        ->required('reply_content',        $content, 'La réponse')
        ->minLength('reply_content',       $content, 5,    'La réponse')
        ->maxLength('reply_content',       $content, 2000, 'La réponse')
        ->noScript('reply_content',        $content, 'La réponse')
        ->noExcessiveRepeat('reply_content', $content, 7, 'La réponse')
        ->hasMeaningfulContent('reply_content', $content, 'La réponse');

    if ($v->fails()) {
        $errors = $v->getErrors();
    } else {
        if ($controller->updateReply($replyId, $content)) {
            header('Location: view_post.php?id=' . $postId . '&reply_updated=1');
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
    <title>Modifier votre réponse — Forum CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0d1117;--surface:#161b27;--surface2:#1f2535;--accent:#4f8ef7;--accent2:#43e97b;--accent3:#f7971e;--accent4:#f64f59;--text:#e6edf3;--muted:#7d8590;--border:rgba(255,255,255,.08);--radius:14px;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
        .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;gap:16px;}
        .back-link{color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:500;transition:color .2s;}
        .back-link:hover{color:var(--text);}
        .topbar-title{font-weight:700;font-size:1rem;}
        .page{max-width:720px;margin:0 auto;padding:40px 20px;}
        .form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:36px;}
        .form-card h1{font-size:1.5rem;font-weight:800;margin-bottom:6px;}
        .form-subtitle{color:var(--muted);font-size:.875rem;margin-bottom:20px;}
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
        textarea.fc{resize:vertical;min-height:180px;line-height:1.6;}
        .field-error{display:none;font-size:.78rem;color:var(--accent4);margin-top:5px;}
        .field-error.visible{display:block;}
        .field-hint{font-size:.775rem;color:var(--muted);margin-top:5px;}
        .progress-wrap{height:3px;border-radius:99px;background:var(--surface2);margin-top:8px;overflow:hidden;}
        .progress-bar{height:100%;border-radius:99px;transition:width .3s,background .3s;width:0;}
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
    <span class="topbar-title">✏️ Modifier votre réponse</span>
</nav>
<div class="page">
    <div class="form-card">
        <h1>✏️ Modifier votre réponse</h1>
        <p class="form-subtitle">Seul l'auteur peut modifier sa réponse</p>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form id="replyForm" method="POST" novalidate>
            <div class="fg">
                <label for="reply_content">
                    Votre réponse <span style="color:var(--accent4)">*</span>
                    <span class="char-count" id="replyCount">0 / 2000</span>
                </label>
                <textarea class="fc <?= isset($errors['reply_content']) ? 'is-invalid' : 'is-valid' ?>"
                          id="reply_content" name="reply_content"
                          placeholder="Rédigez votre réponse…"
                          maxlength="2000"><?= htmlspecialchars($oldText) ?></textarea>
                <div class="progress-wrap"><div class="progress-bar" id="replyBar"></div></div>
                <div class="field-error <?= isset($errors['reply_content']) ? 'visible' : '' ?>" id="replyError">
                    <?= htmlspecialchars($errors['reply_content'] ?? '') ?>
                </div>
                <div class="field-hint">5 à 2 000 caractères — pas de HTML ni de scripts</div>
            </div>

            <div class="form-actions">
                <a href="view_post.php?id=<?= $postId ?>" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>
const RULES={reply_content:{min:5,max:2000,warnAt:1800,checks:[
    v=>v.trim().length===0 ?'La réponse est obligatoire.':null,
    v=>v.trim().length<5   ?'Minimum 5 caractères.':null,
    v=>v.trim().length>2000?'Maximum 2 000 caractères.':null,
    v=>/<script/i.test(v)  ?'Pas de scripts.':null,
    v=>/(.)\1{6,}/.test(v)?'Répétition excessive détectée.':null,
    v=>!/\p{L}/u.test(v)  ?'Doit contenir au moins quelques lettres.':null,
]}};
const state={reply_content:true};
function validate(f){
    const r=RULES[f],el=document.getElementById(f);
    const errEl=document.getElementById('replyError');
    const count=document.getElementById('replyCount');
    const bar=document.getElementById('replyBar');
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
const el=document.getElementById('reply_content');
el.addEventListener('input',()=>validate('reply_content'));
el.addEventListener('blur',()=>validate('reply_content'));
if(el.value)validate('reply_content');
</script>
</body>
</html>
