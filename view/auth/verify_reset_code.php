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
    gap: 15px;
    justify-content: center;
    margin: 40px 0;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 123, 255, 0.08);
    border: 1px solid rgba(0, 123, 255, 0.1);
}

.code-input {
    width: 55px;
    height: 70px;
    font-size: 28px;
    text-align: center;
    border: 3px solid #e9ecef;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: #2c3e50;
    letter-spacing: 2px;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.code-input:focus {
    border-color: #007bff;
    outline: none;
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15), 0 8px 24px rgba(0, 123, 255, 0.12);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
}

.code-input:hover {
    border-color: #6c757d;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.code-input.filled {
    border-color: #28a745;
    background: linear-gradient(135deg, #f0fff4 0%, #e8f5e8 100%);
    color: #155724;
    animation: pulse-success 0.6s ease;
}

.code-input.error {
    border-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #fee 100%);
    color: #721c24;
    animation: shake 0.5s ease;
}

@keyframes pulse-success {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.code-input-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.code-input-label {
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.code-input:focus + .code-input-label {
    opacity: 1;
    color: #007bff;
    font-weight: 600;
}

.code-input.filled + .code-input-label {
    opacity: 1;
    color: #28a745;
    font-weight: 600;
}

.code-input.error + .code-input-label {
    opacity: 1;
    color: #dc3545;
    font-weight: 600;
}

/* Zone d'affichage du code */
.code-display-section {
    margin-top: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    text-align: center;
    animation: slideUp 0.5s ease-out;
}

.code-display-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.code-display-value {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}

.code-digit {
    width: 40px;
    height: 40px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    background: white;
    color: #adb5bd;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.code-digit.has-value {
    border-color: #007bff;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
    color: #007bff;
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    animation: digitPulse 0.6s ease;
}

.code-digit.empty {
    color: #adb5bd;
    background: #f8f9fa;
    border-color: #dee2e6;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes digitPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1.05); }
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
    const codeDisplay = document.getElementById('codeDisplayValue');
    
    // Auto-focus next input
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Only allow numbers
            const numericValue = value.replace(/\D/g, '');
            if (numericValue !== value) {
                e.target.value = numericValue;
            }
            
            if (numericValue.length === 1) {
                input.classList.remove('error');
                input.classList.add('filled');
                
                // Add haptic feedback effect
                input.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    input.style.transform = '';
                }, 100);
                
                // Move to next input
                if (index < codeInputs.length - 1) {
                    setTimeout(() => {
                        codeInputs[index + 1].focus();
                    }, 50);
                }
                
                // Update hidden input AND display
                updateHiddenCode();
                updateCodeDisplay();
                
                // Auto-submit when all fields are filled
                if (index === codeInputs.length - 1) {
                    const fullCode = Array.from(codeInputs).map(inp => inp.value).join('');
                    if (fullCode.length === 6) {
                        setTimeout(() => {
                            form.submit();
                        }, 200);
                    }
                }
            } else {
                input.classList.remove('filled');
                // Update display even when empty
                updateCodeDisplay();
            }
        });
        
        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && e.target.value === '') {
                e.preventDefault();
                if (index > 0) {
                    codeInputs[index - 1].focus();
                    codeInputs[index - 1].value = '';
                    codeInputs[index - 1].classList.remove('filled');
                    updateHiddenCode();
                }
            }
            
            // Handle arrow keys
            if (e.key === 'ArrowLeft' && index > 0) {
                e.preventDefault();
                codeInputs[index - 1].focus();
            }
            if (e.key === 'ArrowRight' && index < codeInputs.length - 1) {
                e.preventDefault();
                codeInputs[index + 1].focus();
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
                            input.classList.remove('error');
                        } else {
                            input.value = '';
                            input.classList.remove('filled');
                            input.classList.remove('error');
                        }
                    });
                    updateHiddenCode();
                    
                    if (code.length === 6) {
                        setTimeout(() => {
                            form.submit();
                        }, 200);
                    }
                }).catch(() => {
                    // Silently handle clipboard errors
                });
            }
            
            // Handle Enter key
            if (e.key === 'Enter') {
                e.preventDefault();
                const fullCode = Array.from(codeInputs).map(inp => inp.value).join('');
                if (fullCode.length === 6) {
                    form.submit();
                } else {
                    // Show error for incomplete code
                    codeInputs.forEach(input => {
                        if (input.value === '') {
                            input.classList.add('error');
                        }
                    });
                }
            }
        });
        
        // Add visual feedback on focus
        input.addEventListener('focus', function() {
            input.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            input.parentElement.style.transform = '';
        });
    });
    
    function updateHiddenCode() {
        const code = Array.from(codeInputs).map(input => input.value).join('');
        hiddenCodeInput.value = code;
    }
    
    function updateCodeDisplay() {
        const code = Array.from(codeInputs).map(input => input.value);
        const displayDigits = codeDisplay.querySelectorAll('.code-digit');
        
        displayDigits.forEach((digit, index) => {
            if (code[index] && code[index] !== '') {
                digit.textContent = code[index];
                digit.classList.add('has-value');
                digit.classList.remove('empty');
            } else {
                digit.textContent = '-';
                digit.classList.add('empty');
                digit.classList.remove('has-value');
            }
        });
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
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="0">
              <span class="code-input-label">1</span>
            </div>
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="1">
              <span class="code-input-label">2</span>
            </div>
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="2">
              <span class="code-input-label">3</span>
            </div>
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="3">
              <span class="code-input-label">4</span>
            </div>
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="4">
              <span class="code-input-label">5</span>
            </div>
            <div class="code-input-wrapper">
              <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-input-index="5">
              <span class="code-input-label">6</span>
            </div>
          </div>

          <div class="timer" id="timer">Code valide pendant: 15:00</div>

          <button type="submit" class="login-submit">Vérifier le code</button>
        </form>

        <!-- Zone d'affichage du code en cours -->
        <div class="code-display-section" id="codeDisplay">
          <div class="code-display-label">Votre code :</div>
          <div class="code-display-value" id="codeDisplayValue">
            <span class="code-digit">-</span>
            <span class="code-digit">-</span>
            <span class="code-digit">-</span>
            <span class="code-digit">-</span>
            <span class="code-digit">-</span>
            <span class="code-digit">-</span>
          </div>
        </div>

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
