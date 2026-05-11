<?php
/**
 * Vue Back-Office : Modifier une réponse — validation avancée
 */
require_once __DIR__ . '/../../config/ForumRedirect.php';

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if ($currentUserId === 0 || !$isAdmin) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}

if (!isset($_GET['id'], $_GET['post_id']) || !is_numeric($_GET['id']) || !is_numeric($_GET['post_id'])) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}
$replyId = (int)$_GET['id'];
$postId  = (int)$_GET['post_id'];

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../controllers/FormValidator.php';
require_once __DIR__ . '/../../models/Reply.php';

$reply = Reply::findById($replyId);
if (!$reply) {
    header('Location: ' . forum_admin_nav_base());
    exit;
}

$errors  = [];
$oldText = $reply->getContent();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim((string)($_POST['reply_content'] ?? ''));
    $oldText = $content;

    $v = (new FormValidator())
        ->required('reply_content',          $content, 'La réponse')
        ->minLength('reply_content',         $content, 5,    'La réponse')
        ->maxLength('reply_content',         $content, 2000, 'La réponse')
        ->noScript('reply_content',          $content, 'La réponse')
        ->noExcessiveRepeat('reply_content', $content, 7, 'La réponse')
        ->hasMeaningfulContent('reply_content', $content, 'La réponse');

    if ($v->fails()) {
        $errors = $v->getErrors();
    } else {
        $controller = new ForumController();
        if ($controller->updateReply($replyId, $content)) {
            $success = 'Réponse modifiée avec succès.';
            $reply   = Reply::findById($replyId);
            $oldText = $reply->getContent();
        } else {
            $errors['global'] = 'Erreur lors de la modification.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une réponse — Admin CityZen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg:   #2F3C4F;
            --header-bg:    #2F3C4F;
            --page-bg:      #F4F6F8;
            --card-bg:      #FFFFFF;
            --green:        #2ECC71;
            --navy:         #34495E;
            --orange:       #F39C12;
            --title:        #2C3E50;
            --text-sec:     #7F8C8D;
            --border:       #E8ECF0;
            --radius:       10px;
            --shadow:       0 2px 12px rgba(0,0,0,.08);
            --danger:       #E74C3C;
            --danger-bg:    rgba(231,76,60,0.1);
            --success:      #2ECC71;
            --success-bg:   rgba(46,204,113,0.1);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--page-bg); color:var(--title); min-height:100vh; }
        
        .topbar { background:var(--header-bg); border-bottom:1px solid rgba(255,255,255,0.08); padding:16px 28px; display:flex; align-items:center; gap:16px; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
        .back-link { color:rgba(255,255,255,0.8); text-decoration:none; font-size:.875rem; font-weight:500; transition:color .2s; }
        .back-link:hover { color:#FFF; }
        .topbar-title { font-weight:700; font-size:1rem; color:var(--orange); }
        
        .page { max-width:720px; margin:0 auto; padding:40px 20px; }
        .form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius); padding:36px; box-shadow:var(--shadow); }
        .form-card h1 { font-size:1.5rem; font-weight:800; margin-bottom:6px; color:var(--navy); }
        .form-subtitle { color:var(--text-sec); font-size:.875rem; margin-bottom:20px; }
        
        .mode-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(243,156,18,0.15); color:#d68910; border:1px solid rgba(243,156,18,0.25); border-radius:20px; padding:4px 14px; font-size:.78rem; font-weight:600; margin-bottom:22px; }
        .meta-row { display:flex; gap:20px; font-size:.8rem; color:var(--text-sec); background:var(--page-bg); border:1px solid var(--border); border-radius:8px; padding:10px 14px; margin-bottom:22px; }
        
        .alert-error { background:var(--danger-bg); border:1px solid rgba(231,76,60,.28); color:var(--danger); border-radius:8px; padding:13px 18px; font-size:.875rem; margin-bottom:22px; }
        .alert-success { background:var(--success-bg); border:1px solid rgba(46,204,113,.28); color:var(--success); border-radius:8px; padding:13px 18px; font-size:.875rem; margin-bottom:22px; }
        
        .fg { margin-bottom:22px; }
        .fg label { display:flex; justify-content:space-between; align-items:baseline; font-size:.825rem; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
        .char-count { font-size:.75rem; color:var(--text-sec); font-weight:400; text-transform:none; }
        .char-count.warn { color:var(--orange); }
        .char-count.limit { color:var(--danger); }
        
        .fc { width:100%; background:#fff; border:1px solid #ced4da; color:var(--title); border-radius:6px; padding:11px 14px; font-size:.9rem; font-family:inherit; outline:none; transition:border-color .2s,box-shadow .2s; }
        .fc:focus { border-color:var(--green); box-shadow:0 0 0 3px rgba(46,204,113,.15); }
        .fc.is-valid { border-color:var(--green)!important; }
        .fc.is-invalid { border-color:var(--danger)!important; box-shadow:0 0 0 3px rgba(231,76,60,.12)!important; }
        textarea.fc { resize:vertical; min-height:200px; line-height:1.6; }
        
        .field-error { display:none; font-size:.78rem; color:var(--danger); margin-top:5px; }
        .field-error.visible { display:block; }
        .field-hint { font-size:.775rem; color:var(--text-sec); margin-top:5px; }
        
        .progress-wrap { height:4px; border-radius:99px; background:var(--page-bg); margin-top:8px; overflow:hidden; border:1px solid var(--border); }
        .progress-bar { height:100%; border-radius:99px; transition:width .3s,background .3s; width:0; }
        
        .form-actions { display:flex; gap:14px; margin-top:28px; }
        .btn-submit { flex:1; padding:12px; background:var(--green); color:#fff; border:none; border-radius:6px; font-size:.95rem; font-weight:700; cursor:pointer; font-family:inherit; transition:opacity .2s,transform .2s; }
        .btn-submit:hover { opacity:.88; transform:translateY(-1px); }
        .btn-submit:disabled { opacity:.5; cursor:not-allowed; transform:none; }
        .btn-cancel { padding:12px 24px; background:var(--page-bg); color:var(--navy); border:1px solid #ced4da; border-radius:6px; font-size:.9rem; font-weight:600; cursor:pointer; text-decoration:none; font-family:inherit; transition:all .2s; display:inline-flex; align-items:center; }
        .btn-cancel:hover { background:#e2e6ea; border-color:#dae0e5; }
        
        @media(max-width:540px) { .form-card { padding:22px 16px; } .form-actions { flex-direction:column; } }
    </style>
</head>
<body>
<nav class="topbar">
    <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>" class="back-link">← Tableau de bord</a>
    <span class="topbar-title">🛠️ Mode Admin</span>
</nav>
<div class="page">
    <div class="form-card">
        <h1>✏️ Modifier une réponse</h1>
        <p class="form-subtitle">Édition par l'administrateur</p>
        <div class="mode-badge">🔐 Mode modération — Admin</div>

        <div class="meta-row">
            <span>👤 Auteur : #<?= $reply->getUserId() ?></span>
            <span>📅 Créé le : <?= date('d/m/Y à H:i', strtotime($reply->getCreatedAt())) ?></span>
        </div>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form id="replyForm" method="POST" novalidate>
            <div class="fg">
                <label for="reply_content">
                    Contenu de la réponse <span style="color:var(--danger)">*</span>
                    <span class="char-count" id="replyCount">0 / 2000</span>
                </label>
                <textarea class="fc <?= isset($errors['reply_content']) ? 'is-invalid' : 'is-valid' ?>"
                          id="reply_content" name="reply_content" maxlength="2000"><?= htmlspecialchars($oldText) ?></textarea>
                <div class="progress-wrap"><div class="progress-bar" id="replyBar"></div></div>
                <div class="field-error <?= isset($errors['reply_content']) ? 'visible' : '' ?>" id="replyError">
                    <?= htmlspecialchars($errors['reply_content'] ?? '') ?>
                </div>
                <div class="field-hint">5 à 2 000 caractères — pas de HTML ni de scripts</div>
            </div>

            <div class="form-actions">
                <a href="<?= htmlspecialchars(forum_post_url($postId)) ?>" class="btn-cancel">❌ Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>
const checks=[v=>v.trim().length===0?'Obligatoire.':null,v=>v.trim().length<5?'Min 5 caractères.':null,v=>v.trim().length>2000?'Max 2000 caractères.':null,v=>/<script/i.test(v)?'Pas de scripts.':null,v=>/(.)\1{6,}/.test(v)?'Répétition excessive.':null,v=>!/\p{L}/u.test(v)?'Doit contenir des lettres.':null];
function validate(){
    const el=document.getElementById('reply_content');
    const errEl=document.getElementById('replyError');
    const count=document.getElementById('replyCount');
    const bar=document.getElementById('replyBar');
    const val=el.value,len=val.length;
    count.textContent=len+' / 2000';
    count.className='char-count'+(len>=2000?' limit':len>=1800?' warn':'');
    bar.style.width=Math.min(len/2000*100,100)+'%';
    bar.style.background=len<5?'#f64f59':len>=2000?'#f64f59':len>=1800?'#f7971e':'#43e97b';
    let err=null;for(const c of checks){err=c(val);if(err)break;}
    if(err){el.classList.add('is-invalid');el.classList.remove('is-valid');errEl.textContent=err;errEl.classList.add('visible');document.getElementById('submitBtn').disabled=true;}
    else{el.classList.remove('is-invalid');el.classList.add('is-valid');errEl.classList.remove('visible');document.getElementById('submitBtn').disabled=false;}
}
const el=document.getElementById('reply_content');
el.addEventListener('input',validate);
el.addEventListener('blur',validate);
if(el.value)validate();
</script>
</body>
</html>
