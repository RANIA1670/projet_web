<!-- ========== DEMANDE D'INTERVENTION ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/interventions">Interventions</a>
            <i class="fas fa-chevron-right"></i>
            <span>Demande</span>
        </div>
        <h1><i class="fas fa-hard-hat" style="color:var(--secondary);"></i> Demande d'Intervention</h1>
        <p>Soumettez votre demande d'intervention auprès de nos équipes techniques.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;max-width:1100px;margin:0 auto;">

            <!-- Form -->
            <div class="card" style="border-radius:var(--radius-xl);">
                <div class="card-header" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:48px;height:48px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:1.3rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div>
                            <h3 style="color:var(--white);margin:0;font-size:1.05rem;">Formulaire de demande</h3>
                            <p style="color:rgba(255,255,255,.65);font-size:.8rem;margin:0;">Remplissez toutes les informations nécessaires</p>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding:36px;">
                    <form action="<?= APP_URL ?>/intervention/demande" method="POST" id="demandeForm">

                        <div style="background:var(--gray-50);border-radius:var(--radius);padding:20px;margin-bottom:28px;border-left:4px solid var(--secondary);">
                            <h4 style="font-size:.9rem;margin-bottom:4px;color:var(--secondary);">
                                <i class="fas fa-user"></i> Informations du demandeur
                            </h4>
                        </div>

                        <div class="form-row form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="nom_demandeur">Nom complet <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-user"></i>
                                    <input type="text" id="nom_demandeur" name="nom_demandeur" class="form-control" required placeholder="Votre nom complet">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email_demandeur">Email <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-envelope"></i>
                                    <input type="email" id="email_demandeur" name="email_demandeur" class="form-control" required placeholder="votre@email.com">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="telephone">Téléphone</label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-phone"></i>
                                <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="+212 6 XX XX XX XX">
                            </div>
                        </div>

                        <div style="background:var(--gray-50);border-radius:var(--radius);padding:20px;margin:28px 0;border-left:4px solid var(--accent);">
                            <h4 style="font-size:.9rem;margin-bottom:4px;color:var(--accent);">
                                <i class="fas fa-tools"></i> Détails de l'intervention
                            </h4>
                        </div>

                        <div class="form-row form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="type_intervention">Type d'intervention</label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-wrench"></i>
                                    <select id="type_intervention" name="type_intervention" class="form-control">
                                        <option value="">-- Choisir --</option>
                                        <option value="voirie">Voirie & Routes</option>
                                        <option value="eclairage">Éclairage Public</option>
                                        <option value="espaces_verts">Espaces Verts</option>
                                        <option value="eau">Eau & Assainissement</option>
                                        <option value="securite">Sécurité</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="urgence">Niveau d'urgence <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-exclamation-triangle"></i>
                                    <select id="urgence" name="urgence" class="form-control" required>
                                        <option value="normal">🟢 Normal</option>
                                        <option value="urgent">🟠 Urgent</option>
                                        <option value="tres_urgent">🔴 Très urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="signalement_id">Signalement associé</label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-link"></i>
                                <select id="signalement_id" name="signalement_id" class="form-control">
                                    <option value="">-- Aucun signalement associé --</option>
                                    <?php foreach($signalements as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (isset($_GET['signalement_id'])&&$_GET['signalement_id']==$s['id'])?'selected':'' ?>>
                                        #<?= str_pad($s['id'],5,'0',STR_PAD_LEFT) ?> — <?= htmlspecialchars(mb_substr($s['titre'],0,50)) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <div class="form-group" style="background:rgba(52,152,219,.05);padding:15px;border-radius:var(--radius);border:1px dashed var(--primary);">
                            <label class="form-label" for="technicien_id" style="color:var(--primary);"><i class="fas fa-hard-hat"></i> Technicien à envoyer <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-user-cog"></i>
                                <select id="technicien_id" name="technicien_id" class="form-control">
                                    <option value="">-- Choisir le technicien --</option>
                                    <?php foreach($techniciens as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-hint">En tant qu'admin, vous pouvez assigner directement un technicien.</div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="description">Description de la demande <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-control" required rows="5"
                                placeholder="Décrivez précisément l'intervention souhaitée, le problème à résoudre et toute information utile pour nos techniciens..."></textarea>
                        </div>

                        <div style="display:flex;gap:14px;justify-content:flex-end;padding-top:20px;border-top:1px solid var(--border-color);">
                            <a href="<?= APP_URL ?>/interventions" class="btn btn-outline">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="demandeSubmitBtn">
                                <i class="fas fa-paper-plane"></i> Soumettre la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div style="display:flex;flex-direction:column;gap:20px;">
                <div class="card">
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                            <div style="width:44px;height:44px;background:rgba(39,174,96,.1);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--secondary);font-size:1.2rem;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h4 style="font-size:.9rem;margin:0;">Délais moyens</h4>
                                <p style="font-size:.78rem;margin:0;color:var(--text-muted);">Selon le niveau d'urgence</p>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <?php foreach([['🟢','Normal','3-5 jours ouvrables'],['🟠','Urgent','24-48 heures'],['🔴','Très urgent','< 24 heures']] as [$emoji,$label,$delay]): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--gray-50);border-radius:var(--radius-sm);">
                                <span style="font-size:.82rem;font-weight:600;"><?= $emoji ?> <?= $label ?></span>
                                <span style="font-size:.78rem;color:var(--text-muted);"><?= $delay ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="card" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-dark) 100%);border:none;">
                    <div class="card-body">
                        <h4 style="color:var(--white);font-size:.9rem;margin-bottom:12px;"><i class="fas fa-headset"></i> Besoin d'aide ?</h4>
                        <p style="color:rgba(255,255,255,.65);font-size:.82rem;margin-bottom:20px;">Notre équipe est disponible pour vous accompagner.</p>
                        <a href="<?= APP_URL ?>/contact" class="btn btn-outline-white btn-sm btn-block">
                            <i class="fas fa-envelope"></i> Nous contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.getElementById('demandeForm').addEventListener('submit',function(){
    const btn=document.getElementById('demandeSubmitBtn');
    btn.innerHTML='<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:8px;"></span> Envoi...';
    btn.disabled=true;
});
</script>
<style>@media(max-width:900px){.container>div[style*="grid-template-columns"]{grid-template-columns:1fr !important;}}</style>
