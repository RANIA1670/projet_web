<!-- ========== SIGNALEMENT - CREATE FORM ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Signaler un problème</span>
        </div>
        <h1><i class="fas fa-plus-circle" style="color:var(--secondary);"></i> Signaler un Problème</h1>
        <p>Décrivez le problème rencontré avec le maximum de détails pour une prise en charge rapide.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="max-width:860px;margin:0 auto;">
            <div class="card" style="border-radius:var(--radius-xl);overflow:visible;">
                <!-- Progress steps -->
                <div style="background:var(--gray-50);border-radius:var(--radius-xl) var(--radius-xl) 0 0;padding:28px 36px;border-bottom:1px solid var(--border-color);">
                    <div style="display:flex;align-items:center;gap:0;justify-content:center;" class="form-steps">
                        <div class="form-step active">
                            <div class="step-num">1</div>
                            <span>Informations</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="form-step">
                            <div class="step-num">2</div>
                            <span>Localisation</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="form-step">
                            <div class="step-num">3</div>
                            <span>Média</span>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding:40px;">
                    <form action="<?= APP_URL ?>/signalement/creer" method="POST" enctype="multipart/form-data" id="signalementForm">

                        <!-- Section 1: Infos -->
                        <div class="form-section" id="section1">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
                                <div style="width:40px;height:40px;background:rgba(39,174,96,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--secondary);">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h3 style="font-size:1.1rem;margin:0;">Informations générales</h3>
                                    <p style="margin:0;font-size:.82rem;color:var(--text-muted);">Décrivez le problème</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="titre">Titre du signalement <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-heading"></i>
                                    <input type="text" id="titre" name="titre" class="form-control" required
                                        placeholder="Ex: Nid de poule dangereux avenue Principale" maxlength="200">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="description">Description détaillée <span class="required">*</span></label>
                                <textarea id="description" name="description" class="form-control" required rows="5"
                                    placeholder="Décrivez le problème en détail : localisation précise, gravité, dangers potentiels..."></textarea>
                                <div class="form-hint"><i class="fas fa-info-circle"></i> Plus la description est précise, plus vite le problème sera traité.</div>
                            </div>

                            <div class="form-row form-row-2">
                                <div class="form-group">
                                    <label class="form-label" for="categorie_id">Catégorie <span class="required">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="input-icon fas fa-tag"></i>
                                        <select id="categorie_id" name="categorie_id" class="form-control" required>
                                            <option value="">-- Choisir une catégorie --</option>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="priorite">Niveau de priorité <span class="required">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="input-icon fas fa-flag"></i>
                                        <select id="priorite" name="priorite" class="form-control" required>
                                            <option value="faible">🟢 Faible</option>
                                            <option value="moyenne" selected>🟡 Moyenne</option>
                                            <option value="haute">🟠 Haute</option>
                                            <option value="urgente">🔴 Urgente</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-row-2">
                                <div class="form-group">
                                    <label class="form-label" for="date_incident">Date du problème <span class="required">*</span></label>
                                    <div class="input-icon-wrap">
                                        <i class="input-icon fas fa-calendar"></i>
                                        <input type="date" id="date_incident" name="date_incident" class="form-control"
                                            value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr style="border:none;border-top:1px solid var(--border-color);margin:32px 0;">

                        <!-- Section 2: Localisation -->
                        <div class="form-section" id="section2">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
                                <div style="width:40px;height:40px;background:rgba(230,126,34,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 style="font-size:1.1rem;margin:0;">Localisation</h3>
                                    <p style="margin:0;font-size:.82rem;color:var(--text-muted);">Où se trouve le problème ?</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="adresse">Adresse / Localisation <span class="required">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-map-marker-alt"></i>
                                    <input type="text" id="adresse" name="adresse" class="form-control" required
                                        placeholder="Ex: 15 Avenue Habib Bourguiba, Tunis">
                                </div>
                                <div class="form-hint"><i class="fas fa-crosshairs"></i> Soyez le plus précis possible (rue, numéro, quartier)</div>
                            </div>
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                        </div>

                        <hr style="border:none;border-top:1px solid var(--border-color);margin:32px 0;">

                        <!-- Section 3: Image -->
                        <div class="form-section" id="section3">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
                                <div style="width:40px;height:40px;background:rgba(44,62,80,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--primary);">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <div>
                                    <h3 style="font-size:1.1rem;margin:0;">Photo du problème</h3>
                                    <p style="margin:0;font-size:.82rem;color:var(--text-muted);">Optionnel mais fortement recommandé</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="file-upload" id="fileDropZone">
                                    <input type="file" name="image" id="imageInput" accept="image/*">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p><strong>Cliquez ou glissez-déposez</strong> une image ici</p>
                                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">JPG, PNG, GIF, WebP • Max 5 Mo</p>
                                </div>
                                <div id="imagePreview"></div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div style="display:flex;gap:16px;justify-content:flex-end;padding-top:24px;border-top:1px solid var(--border-color);">
                            <a href="<?= APP_URL ?>/signalements" class="btn btn-outline">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Soumettre le signalement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.form-steps { display:flex; align-items:center; gap:0; justify-content:center; }
.form-step { display:flex; align-items:center; gap:8px; flex-direction:column; font-family:var(--font-main); font-size:.75rem; font-weight:600; color:var(--text-muted); }
.step-num { width:32px;height:32px;border-radius:50%;background:var(--border-color);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;transition:var(--transition); }
.form-step.active .step-num { background:var(--secondary); color:var(--white); box-shadow:0 4px 12px rgba(39,174,96,.35); }
.form-step.active { color:var(--secondary); }
.step-line { width:60px; height:2px; background:var(--border-color); margin:0 8px; margin-bottom:20px; }
</style>

<script>
document.getElementById('signalementForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:8px;"></span> Envoi en cours...';
    btn.disabled = true;
});
// File drop zone
const drop = document.getElementById('fileDropZone');
if (drop) {
    drop.addEventListener('click', () => document.getElementById('imageInput').click());
}
</script>
