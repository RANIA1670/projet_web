<!-- ========== AUTH - REGISTER ========== -->
<div style="min-height:calc(100vh - 70px);display:flex;align-items:center;justify-content:center;padding:40px 16px;background:var(--bg-body);">
    <div style="width:100%;max-width:520px;">

        <div style="text-align:center;margin-bottom:36px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--secondary),var(--secondary-dark));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:1.8rem;margin:0 auto 16px;box-shadow:0 8px 24px rgba(39,174,96,.3);">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 style="font-size:1.5rem;margin-bottom:4px;">Créer un compte</h1>
            <p style="font-size:.875rem;color:var(--text-muted);">Rejoignez la communauté CityZen</p>
        </div>

        <div class="card" style="border-radius:var(--radius-xl);">
            <div class="card-body" style="padding:36px;">
                <form action="<?= APP_URL ?>/auth/inscription" method="POST" id="registerForm">
                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="regPrenom">Prénom <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-user"></i>
                                <input type="text" id="regPrenom" name="prenom" class="form-control" required placeholder="Votre prénom">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="regNom">Nom <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-user"></i>
                                <input type="text" id="regNom" name="nom" class="form-control" required placeholder="Votre nom">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="regEmail">Email <span class="required">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="regEmail" name="email" class="form-control" required placeholder="votre@email.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="regTel">Téléphone</label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-phone"></i>
                            <input type="tel" id="regTel" name="telephone" class="form-control" placeholder="+212 6 XX XX XX XX">
                        </div>
                    </div>
                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="regPassword">Mot de passe <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-lock"></i>
                                <input type="password" id="regPassword" name="password" class="form-control" required placeholder="Min. 6 caractères" minlength="6">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="regConfirm">Confirmer <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-lock"></i>
                                <input type="password" id="regConfirm" name="password_confirm" class="form-control" required placeholder="Répétez">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                </form>
                <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border-color);text-align:center;">
                    <p style="font-size:.875rem;color:var(--text-muted);">
                        Déjà un compte ?
                        <a href="<?= APP_URL ?>/auth/connexion" style="color:var(--secondary);font-weight:600;">Se connecter</a>
                    </p>
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
