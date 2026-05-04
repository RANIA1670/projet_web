<?php

declare(strict_types=1);

// Démarrer la session avant toute autre chose
cityzen_session_start();

cityzen_render_head('Mot de passe oublié');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
?>
<style>
.forgot-password-container {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
}

.method-selector {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.method-option {
    flex: 1;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.method-option:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.1);
}

.method-option.selected {
    border-color: #007bff;
    background: #e3f2fd;
}

.method-option input[type="radio"] {
    display: none;
}

.method-option .icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.method-option .label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.method-option .description {
    font-size: 0.9rem;
    color: #666;
}

.contact-field {
    display: none;
    margin-bottom: 20px;
}

.contact-field.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}

.resend-link {
    text-align: center;
    margin-top: 20px;
}

.resend-link a {
    color: #007bff;
    text-decoration: none;
    font-size: 0.9rem;
}

.resend-link a:hover {
    text-decoration: underline;
}

.loading {
    display: none;
    text-align: center;
    padding: 20px;
}

.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #007bff;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodOptions = document.querySelectorAll('.method-option');
    const contactFields = document.querySelectorAll('.contact-field');
    
    methodOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            methodOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Hide all contact fields
            contactFields.forEach(field => field.classList.remove('active'));
            
            // Show corresponding contact field
            const method = this.querySelector('input[type="radio"]').value;
            const targetField = document.getElementById(method + '_field');
            if (targetField) {
                targetField.classList.add('active');
                targetField.querySelector('input').focus();
            }
        });
    });
    
    // Auto-select first option if none selected
    if (!document.querySelector('.method-option.selected')) {
        document.querySelector('.method-option').click();
    }
});

function showLoading() {
    document.querySelector('.loading').style.display = 'block';
    document.querySelector('.login-form').style.display = 'none';
}

function hideLoading() {
    document.querySelector('.loading').style.display = 'none';
    document.querySelector('.login-form').style.display = 'block';
}
</script>

<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <a href="<?= htmlspecialchars(cityzen_asset('index.php')) ?>">Accueil public</a>
      <a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Connexion</a>
    </nav>
  </header>

  <main class="page public-page">
    <div class="forgot-password-container">
      <section class="login-panel">
        <h1>Mot de passe oublié</h1>
        <p class="login-lead">Choisissez comment recevoir votre code de réinitialisation.</p>

        <?php if ($error !== ''): ?>
          <div class="error-message" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success !== ''): ?>
          <div class="success-message" role="status"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="loading">
          <div class="spinner"></div>
          <p>Envoi du code en cours...</p>
        </div>

        <form class="login-form" method="post" action="" novalidate onsubmit="showLoading()">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          
          <div class="method-selector">
            <label class="method-option">
              <input type="radio" name="method" value="email" checked>
              <div class="icon">📧</div>
              <div class="label">Email</div>
              <div class="description">Recevoir le code par email</div>
            </label>
            
            <label class="method-option">
              <input type="radio" name="method" value="sms">
              <div class="icon">📱</div>
              <div class="label">SMS</div>
              <div class="description">Recevoir le code par SMS</div>
            </label>
          </div>

          <div id="email_field" class="contact-field active">
            <label class="login-field">
              <span>Email</span>
              <input type="email" name="email" autocomplete="email" required 
                     value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>"
                     placeholder="Entrez votre adresse email">
              <?php if (isset($errors['email'])): ?>
                <small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['email']) ?></small>
              <?php endif; ?>
            </label>
          </div>

          <div id="sms_field" class="contact-field">
            <label class="login-field">
              <span>Téléphone</span>
              <input type="tel" name="phone" autocomplete="tel" required 
                     value="<?= htmlspecialchars((string) ($old['phone'] ?? '')) ?>"
                     placeholder="Entrez votre numéro de téléphone">
              <?php if (isset($errors['phone'])): ?>
                <small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['phone']) ?></small>
              <?php endif; ?>
            </label>
          </div>

          <button type="submit" class="login-submit">Envoyer le code</button>
        </form>

        <div class="resend-link">
          <a href="<?= htmlspecialchars(cityzen_asset('controller/verify_reset_code.php')) ?>">J'ai déjà un code</a>
        </div>

        <p class="login-footer-link"><a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Retour à la connexion</a></p>
      </section>
    </div>
  </main>
</div>

<?php cityzen_render_footer(); ?>
