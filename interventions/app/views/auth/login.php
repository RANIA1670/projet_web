<!-- ========== AUTH - LOGIN ========== -->
<div style="min-height:calc(100vh - 70px);display:flex;align-items:center;justify-content:center;padding:40px 16px;background:var(--bg-body);">
    <div style="width:100%;max-width:460px;">

        <!-- Logo -->
        <div style="text-align:center;margin-bottom:36px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:1.8rem;margin:0 auto 16px;box-shadow:0 8px 24px rgba(44,62,80,.3);">
                <i class="fas fa-city"></i>
            </div>
            <h1 style="font-size:1.5rem;margin-bottom:4px;">Connexion</h1>
            <p style="font-size:.875rem;color:var(--text-muted);">Accédez à votre espace CityZen</p>
        </div>

        <div class="card" style="border-radius:var(--radius-xl);">
            <div class="card-body" style="padding:36px;">
                <form action="<?= APP_URL ?>/auth/connexion" method="POST" id="loginForm">
                    <div class="form-group">
                        <label class="form-label" for="loginEmail">Adresse email</label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="loginEmail" name="email" class="form-control" required
                                placeholder="votre@email.com" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="loginPassword">Mot de passe</label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="loginPassword" name="password" class="form-control" required
                                placeholder="••••••••" autocomplete="current-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>

                <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border-color);text-align:center;">
                    <p style="font-size:.875rem;color:var(--text-muted);">
                        Pas encore de compte ?
                        <a href="<?= APP_URL ?>/auth/inscription" style="color:var(--secondary);font-weight:600;">Créer un compte</a>
                    </p>
                </div>

                <!-- Demo credentials -->
                <div style="margin-top:16px;padding:14px;background:rgba(39,174,96,.06);border:1px solid rgba(39,174,96,.2);border-radius:var(--radius);font-size:.78rem;color:var(--text-secondary);">
                    <strong style="color:var(--secondary);"><i class="fas fa-info-circle"></i> Démo :</strong><br>
                    Admin : admin@cityzen.ma / password<br>
                    Citoyen : jean.martin@email.com / password
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-top:20px;">
            <a href="<?= APP_URL ?>/" style="font-size:.82rem;color:var(--text-muted);">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>
