<!-- ========== CONTACT ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Contact</span>
        </div>
        <h1><i class="fas fa-envelope" style="color:var(--secondary);"></i> Contactez-nous</h1>
        <p>Notre équipe est à votre écoute pour toute question ou suggestion.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Info Card -->
            <div class="contact-info-card reveal">
                <h2 class="contact-info-title">Nous sommes là pour vous aider</h2>
                <p class="contact-info-desc">Contactez notre équipe pour toute question, suggestion ou demande d'information concernant la plateforme CityZen.</p>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info-text">
                        <strong>Adresse</strong>
                        <span>15 Avenue Habib Bourguiba<br>Tunis, Tunisie 1000</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="contact-info-text">
                        <strong>Téléphone</strong>
                        <span>+216 71 000 001<br>+216 71 000 002</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-info-text">
                        <strong>Email</strong>
                        <span>contact@cityzen.tn<br>support@cityzen.tn</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                    <div class="contact-info-text">
                        <strong>Horaires</strong>
                        <span>Lun - Ven : 08h00 - 17h00<br>Sam : 09h00 - 13h00</span>
                    </div>
                </div>

                <div style="margin-top:32px;padding-top:28px;border-top:1px solid rgba(255,255,255,.15);">
                    <p style="font-size:.8rem;color:rgba(255,255,255,.5);margin-bottom:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;">Suivez-nous</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card reveal" style="border-radius:var(--radius-xl);">
                <div class="card-body" style="padding:40px;">
                    <h3 style="font-size:1.3rem;margin-bottom:8px;">Envoyez-nous un message</h3>
                    <p style="font-size:.875rem;margin-bottom:32px;">Nous vous répondrons dans les meilleurs délais.</p>

                    <form action="<?= APP_URL ?>/contact" method="POST" id="contactForm">

                        <div class="form-row form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="contactNom">Votre nom <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-user"></i>
                                    <input type="text" id="contactNom" name="nom" class="form-control" required placeholder="Nom complet">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contactEmail">Email <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-envelope"></i>
                                    <input type="email" id="contactEmail" name="email" class="form-control" required placeholder="votre@email.com">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="contactSujet">Sujet <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-tag"></i>
                                <select id="contactSujet" name="sujet" class="form-control" required>
                                    <option value="">-- Choisir un sujet --</option>
                                    <option value="Signalement">Question sur un signalement</option>
                                    <option value="Intervention">Question sur une intervention</option>
                                    <option value="Suivi">Suivi de dossier</option>
                                    <option value="Technique">Problème technique</option>
                                    <option value="Partenariat">Partenariat</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="contactMessage">Message <span class="required">*</span></label>
                            <textarea id="contactMessage" name="message" class="form-control" required rows="6"
                                placeholder="Décrivez votre demande en détail..."></textarea>
                        </div>

                        <!-- Honeypot anti-spam -->
                        <div style="display:none;">
                            <input type="text" name="_hp" tabindex="-1">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="contactSubmitBtn">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div style="margin-top:72px;" class="reveal">
            <div class="section-header" style="margin-bottom:36px;">
                <div class="section-badge"><i class="fas fa-question-circle"></i> FAQ</div>
                <h2 class="section-title">Questions <span>fréquentes</span></h2>
                <div class="title-line"></div>
            </div>
            <div class="grid grid-2" style="gap:16px;">
                <?php foreach([
                    ['Comment signaler un problème ?', 'Cliquez sur "Signaler un problème" depuis n\'importe quelle page, remplissez le formulaire avec titre, description, catégorie et localisation.'],
                    ['Comment suivre mon signalement ?', 'Allez sur "Suivi d\'intervention" et entrez le numéro de référence reçu après votre signalement.'],
                    ['Combien de temps pour une intervention ?', 'Le délai varie según la priorité : urgente (< 24h), haute (2-5 jours), moyenne (1-2 semaines), faible (< 1 mois).'],
                    ['Puis-je soumettre sans compte ?', 'Oui, vous pouvez signaler anonymement. Un compte vous permet de suivre vos signalements facilement.'],
                ] as [$q, $r]): ?>
                <div class="card" style="cursor:pointer;" onclick="this.querySelector('.faq-answer').style.display=this.querySelector('.faq-answer').style.display==='none'||!this.querySelector('.faq-answer').style.display?'block':'none'">
                    <div class="card-body" style="padding:20px;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                            <h4 style="font-size:.9rem;margin:0;color:var(--text-primary);"><?= $q ?></h4>
                            <i class="fas fa-plus" style="color:var(--secondary);flex-shrink:0;margin-top:2px;"></i>
                        </div>
                        <div class="faq-answer" style="display:none;margin-top:12px;">
                            <p style="font-size:.875rem;color:var(--text-secondary);margin:0;"><?= $r ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
