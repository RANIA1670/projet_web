<?php

declare(strict_types=1);

// Démarrer la session avant toute autre chose
cityzen_session_start();

cityzen_render_head('Vérification du code');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
?>
<style>
.verify-container {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
}

.code-input-group {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 30px 0;
}

.code-input {
    width: 50px;
    height: 60px;
    font-size: 24px;
    text-align: center;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.code-input:focus {
    border-color: #007bff;
    outline: none;
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.code-input.filled {
    border-color: #28a745;
    background: #f8fff9;
}

.timer {
    text-align: center;
    font-size: 1.1rem;
    color: #666;
    margin: 20px 0;
}

.timer.warning {
    color: #ffc107;
}

.timer.danger {
    color: #dc3545;
}

.resend-section {
    text-align: center;
    margin: 20px 0;
}

.resend-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s ease;
}

.resend-btn:hover:not(:disabled) {
    background: #5a6268;
}

.resend-btn:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
}

.success-animation {
    display: none;
    text-align: center;
    padding: 20px;
}

.success-animation .checkmark {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 10px;
}

.user-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
}

.user-info .username {
    font-weight: bold;
    color: #333;
    font-size: 1.1rem;
}

.user-info .contact {
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInputs = document.querySelectorAll('.code-input');
    const hiddenCodeInput = document.getElementById('hidden_code');
    const timerElement = document.getElementById('timer');
    const resendBtn = document.getElementById('resend_btn');
    const form = document.querySelector('.login-form');
    
    // Auto-focus next input
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            if (value.length === 1) {
                input.classList.add('filled');
                
                // Move to next input
                if (index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
                
                // Update hidden input
                updateHiddenCode();
                
                // Auto-submit when all fields are filled
                if (index === codeInputs.length - 1) {
                    setTimeout(() => form.submit(), 100);
                }
            } else {
                input.classList.remove('filled');
            }
        });
        
        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && e.target.value === '') {
                if (index > 0) {
                    codeInputs[index - 1].focus();
                    codeInputs[index - 1].value = '';
                    codeInputs[index - 1].classList.remove('filled');
                }
            }
            
            // Handle paste
            if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                navigator.clipboard.readText().then(text => {
                    const code = text.replace(/\D/g, '').slice(0, 6);
                    codeInputs.forEach((input, i) => {
                        if (i < code.length) {
                            input.value = code[i];
                            input.classList.add('filled');
                        } else {
                            input.value = '';
                            input.classList.remove('filled');
                        }
                    });
                    updateHiddenCode();
                    
                    if (code.length === 6) {
                        setTimeout(() => form.submit(), 100);
                    }
                });
            }
        });
    });
    
    function updateHiddenCode() {
        const code = Array.from(codeInputs).map(input => input.value).join('');
        hiddenCodeInput.value = code;
    }
    
    // Timer countdown
    let timeLeft = <?= $timeLeft ?? 900 ?>; // 15 minutes in seconds
    const timerInterval = setInterval(() => {
        timeLeft--;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = 'Code expiré';
            timerElement.classList.add('danger');
            resendBtn.disabled = false;
            resendBtn.textContent = 'Renvoyer le code';
        } else {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `Code valide pendant: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 60) {
                timerElement.classList.add('danger');
            } else if (timeLeft <= 300) {
                timerElement.classList.add('warning');
            }
            
            // Enable resend button after 2 minutes
            if (timeLeft <= 900 - 120) {
                resendBtn.disabled = false;
                resendBtn.textContent = 'Renvoyer le code';
            }
        }
    }, 1000);
    
    // Focus first input
    codeInputs[0].focus();
});

function showSuccess() {
    document.querySelector('.verify-form').style.display = 'none';
    document.querySelector('.success-animation').style.display = 'block';
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
    <div class="verify-container">
      <section class="login-panel verify-form">
        <h1>Vérification du code</h1>
        <p class="login-lead">Entrez le code à 6 chiffres que vous avez reçu.</p>

        <?php if ($user): ?>
          <div class="user-info">
            <div class="username"><?= htmlspecialchars($user['username']) ?></div>
            <div class="contact">
              Code envoyé à: 
              <?= htmlspecialchars($user['contact'] ?? ($user['email'] ?? $user['phone'] ?? 'N/A')) ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
          <div class="error-message" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" method="post" action="">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="code" id="hidden_code" required>
          
          <div class="code-input-group">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
          </div>

          <div class="timer" id="timer">Code valide pendant: 15:00</div>

          <button type="submit" class="login-submit">Vérifier le code</button>
        </form>

        <div class="resend-section">
          <button type="button" class="resend-btn" id="resend_btn" disabled>
            Patientez...
          </button>
        </div>

        <p class="login-footer-link">
          <a href="<?= htmlspecialchars(cityzen_asset('controller/forgot_password_new.php')) ?>">Demander un autre code</a>
        </p>

        <p class="login-footer-link">
          <a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Retour à la connexion</a>
        </p>
      </section>

      <div class="success-animation">
        <div class="checkmark">✓</div>
        <h2>Code vérifié!</h2>
        <p>Redirection vers la réinitialisation du mot de passe...</p>
      </div>
    </div>
  </main>
</div>

<?php cityzen_render_footer(); ?>
