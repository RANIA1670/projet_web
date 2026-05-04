<?php

declare(strict_types=1);

// Démarrer la session avant toute autre chose
cityzen_session_start();

cityzen_render_head('Nouveau mot de passe');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$success = (bool) ($success ?? false);
?>
<style>
.reset-container {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
}

.password-strength {
    margin-top: 10px;
    height: 5px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: width 0.3s ease, background 0.3s ease;
    width: 0%;
}

.strength-weak { background: #dc3545; width: 33%; }
.strength-medium { background: #ffc107; width: 66%; }
.strength-strong { background: #28a745; width: 100%; }

.strength-text {
    font-size: 0.8rem;
    margin-top: 5px;
    color: #666;
}

.requirements {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    font-size: 0.9rem;
}

.requirements h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.requirements ul {
    margin: 0;
    padding-left: 20px;
}

.requirements li {
    margin-bottom: 5px;
    color: #666;
}

.requirements li.valid {
    color: #28a745;
    text-decoration: line-through;
}

.requirements li.invalid {
    color: #dc3545;
}

.success-animation {
    display: none;
    text-align: center;
    padding: 40px 20px;
}

.success-animation .checkmark {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 20px;
    animation: scaleIn 0.5s ease;
}

.success-animation h2 {
    color: #28a745;
    margin-bottom: 10px;
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

.show-password-toggle {
    position: relative;
}

.show-password-toggle input {
    padding-right: 40px;
}

.show-password-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 0.9rem;
    padding: 5px;
}

.show-password-btn:hover {
    color: #007bff;
}
</style>

<script>
const cityzenResetSuccess = <?= $success ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const password2Input = document.getElementById('password2');
    const strengthBar = document.querySelector('.password-strength-bar');
    const strengthText = document.querySelector('.strength-text');
    const requirements = document.querySelectorAll('.requirements li');
    
    // Password requirements check
    const checks = {
        length: false,
        letter: false,
        number: false
    };
    
    function updateRequirements() {
        const password = passwordInput.value;
        
        checks.length = password.length >= 8;
        checks.letter = /[a-zA-Z]/.test(password);
        checks.number = /\d/.test(password);
        
        requirements.forEach(req => {
            const checkType = req.dataset.check;
            if (checks[checkType]) {
                req.classList.add('valid');
                req.classList.remove('invalid');
            } else {
                req.classList.add('invalid');
                req.classList.remove('valid');
            }
        });
        
        updateStrength();
    }
    
    function updateStrength() {
        const password = passwordInput.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        
        strengthBar.className = 'password-strength-bar';
        
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
        } else if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
            strengthText.textContent = 'Faible';
            strengthText.style.color = '#dc3545';
        } else if (strength <= 4) {
            strengthBar.classList.add('strength-medium');
            strengthText.textContent = 'Moyen';
            strengthText.style.color = '#ffc107';
        } else {
            strengthBar.classList.add('strength-strong');
            strengthText.textContent = 'Fort';
            strengthText.style.color = '#28a745';
        }
    }
    
    function checkPasswordsMatch() {
        const password = passwordInput.value;
        const password2 = password2Input.value;
        
        if (password2.length > 0) {
            if (password === password2) {
                password2Input.style.borderColor = '#28a745';
                document.querySelector('.password-match').textContent = '✓ Les mots de passe correspondent';
                document.querySelector('.password-match').style.color = '#28a745';
            } else {
                password2Input.style.borderColor = '#dc3545';
                document.querySelector('.password-match').textContent = '✗ Les mots de passe ne correspondent pas';
                document.querySelector('.password-match').style.color = '#dc3545';
            }
        } else {
            password2Input.style.borderColor = '';
            document.querySelector('.password-match').textContent = '';
        }
    }
    
    // Toggle password visibility
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        
        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = '🙈';
        } else {
            input.type = 'password';
            button.textContent = '👁️';
        }
    }
    
    // Event listeners
    passwordInput.addEventListener('input', updateRequirements);
    password2Input.addEventListener('input', checkPasswordsMatch);
    
    // Initialize
    updateRequirements();

    if (cityzenResetSuccess) {
        showSuccess();
    }
});

function showSuccess() {
    document.querySelector('.reset-form').style.display = 'none';
    document.querySelector('.success-animation').style.display = 'block';
    
    // Redirect to login after 3 seconds
    setTimeout(() => {
        window.location.href = '<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>';
    }, 3000);
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
    <div class="reset-container">
      <section class="login-panel reset-form">
        <h1>Nouveau mot de passe</h1>
        <p class="login-lead">Choisissez un nouveau mot de passe sécurisé pour votre compte CityZen.</p>

        <?php if ($user): ?>
          <div class="user-info">
            <div class="username"><?= htmlspecialchars($user['username']) ?></div>
            <div class="contact"><?= htmlspecialchars($user['email'] ?? $user['phone'] ?? 'N/A') ?></div>
          </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
          <div class="error-message" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" method="post" action="">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="code" value="<?= htmlspecialchars($code ?? '') ?>">
          
          <label class="login-field show-password-toggle">
            <span>Nouveau mot de passe</span>
            <input type="password" name="password" id="password" 
                   autocomplete="new-password" minlength="8" required
                   value="<?= htmlspecialchars((string) ($old['password'] ?? '')) ?>">
            <button type="button" class="show-password-btn" id="toggle_password" 
                    onclick="togglePassword('password', 'toggle_password')">👁️</button>
            <?php if (isset($errors['password'])): ?>
              <small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['password']) ?></small>
            <?php endif; ?>
            
            <div class="password-strength">
              <div class="password-strength-bar"></div>
            </div>
            <div class="strength-text"></div>
          </label>

          <label class="login-field show-password-toggle">
            <span>Confirmer le mot de passe</span>
            <input type="password" name="password2" id="password2" 
                   autocomplete="new-password" minlength="8" required
                   value="<?= htmlspecialchars((string) ($old['password2'] ?? '')) ?>">
            <button type="button" class="show-password-btn" id="toggle_password2" 
                    onclick="togglePassword('password2', 'toggle_password2')">👁️</button>
            <?php if (isset($errors['password2'])): ?>
              <small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['password2']) ?></small>
            <?php endif; ?>
            <div class="password-match"></div>
          </label>

          <div class="requirements">
            <h4>Le mot de passe doit contenir:</h4>
            <ul>
              <li data-check="length">Au moins 8 caractères</li>
              <li data-check="letter">Au moins 1 lettre</li>
              <li data-check="number">Au moins 1 chiffre</li>
            </ul>
          </div>

          <button type="submit" class="login-submit">Réinitialiser le mot de passe</button>
        </form>

        <p class="login-footer-link">
          <a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Retour à la connexion</a>
        </p>
      </section>

      <div class="success-animation">
        <div class="checkmark">✓</div>
        <h2>Mot de passe réinitialisé!</h2>
        <p>Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
        <p>Redirection automatique...</p>
      </div>
    </div>
  </main>
</div>

<?php cityzen_render_footer(); ?>
