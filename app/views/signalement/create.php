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
                    <form action="<?= APP_URL ?>/signalement/creer" method="POST" enctype="multipart/form-data" id="signalementForm" novalidate>

                        <!-- Error Messages -->
                        <div id="errorMessages" style="display:none; background-color:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px; border-radius:4px; margin-bottom:20px;">
                            <strong>Erreurs de validation :</strong>
                            <ul id="errorList" style="margin:8px 0 0 20px; padding:0;"></ul>
                        </div>

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

                            <!-- Géolocalisation automatique -->
                            <div class="form-group">
                                <button type="button" id="geolocBtn" class="btn btn-outline" style="width:100%; margin-bottom:12px;">
                                    <i class="fas fa-crosshairs"></i> Me localiser automatiquement
                                </button>
                                <div id="geolocStatus" style="display:none; background:#E3F2FD; color:#1976D2; padding:10px; border-radius:4px; font-size:0.85rem; margin-bottom:12px;"></div>
                            </div>

                            <!-- Adresse avec suggestions -->
                            <div class="form-group">
                                <label class="form-label" for="adresse">Adresse / Localisation <span class="required">*</span></label>
                                <div style="position:relative;">
                                    <div class="input-icon-wrap">
                                        <i class="input-icon fas fa-map-marker-alt"></i>
                                        <input type="text" id="adresse" name="adresse" class="form-control" required
                                            placeholder="Ex: 15 Avenue Habib Bourguiba, Tunis" autocomplete="off">
                                    </div>
                                    <div id="addressSuggestions" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-top:none; border-radius:0 0 4px 4px; z-index:10; max-height:200px; overflow-y:auto; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                                    </div>
                                </div>
                                <div class="form-hint"><i class="fas fa-crosshairs"></i> Soyez le plus précis possible (rue, numéro, quartier)</div>
                            </div>

                            <!-- Mini-carte -->
                            <div id="miniMapContainer" style="display:none; margin-bottom:16px;">
                                <label class="form-label">Prévisualisation</label>
                                <div id="miniMap" style="height:200px; border:1px solid var(--border-color); border-radius:8px;"></div>
                                <p id="coordsDisplay" style="margin:8px 0 0; font-size:0.85rem; color:var(--text-muted);"></p>
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
    console.log('Form submit event triggered');
    // Validation des champs
    let isValid = true;
    const errors = [];

    // Titre
    const titre = document.getElementById('titre').value.trim();
    if (titre === '') {
        errors.push('Le titre est obligatoire.');
        isValid = false;
    } else if (titre.length > 200) {
        errors.push('Le titre ne doit pas dépasser 200 caractères.');
        isValid = false;
    }

    // Description
    const description = document.getElementById('description').value.trim();
    if (description === '') {
        errors.push('La description est obligatoire.');
        isValid = false;
    }

    // Catégorie
    const categorie = document.getElementById('categorie_id').value;
    if (categorie === '') {
        errors.push('Veuillez sélectionner une catégorie.');
        isValid = false;
    }

    // Priorité
    const priorite = document.getElementById('priorite').value;
    if (priorite === '') {
        errors.push('Veuillez sélectionner un niveau de priorité.');
        isValid = false;
    }

    // Date
    const dateIncident = document.getElementById('date_incident').value;
    const today = new Date().toISOString().split('T')[0];
    if (dateIncident === '') {
        errors.push('La date du problème est obligatoire.');
        isValid = false;
    } else if (dateIncident > today) {
        errors.push('La date ne peut pas être dans le futur.');
        isValid = false;
    }

    // Adresse
    const adresse = document.getElementById('adresse').value.trim();
    if (adresse === '') {
        errors.push('L\'adresse est obligatoire.');
        isValid = false;
    }

    // Image (optionnel, mais vérifier taille si présente)
    const imageInput = document.getElementById('imageInput');
    if (imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5 Mo
        if (file.size > maxSize) {
            errors.push('La taille de l\'image ne doit pas dépasser 5 Mo.');
            isValid = false;
        }
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            errors.push('Le format de l\'image doit être JPG, PNG, GIF ou WebP.');
            isValid = false;
        }
    }

    if (!isValid) {
        e.preventDefault();
        const errorDiv = document.getElementById('errorMessages');
        const errorList = document.getElementById('errorList');
        errorList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        errorDiv.style.display = 'block';
        // Scroll to top of form
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return false;
    } else {
        // Hide errors if valid
        document.getElementById('errorMessages').style.display = 'none';
    }

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

<!-- Leaflet pour la géolocalisation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
// ============ GÉOLOCALISATION ============
let miniMap = null;
let miniMapMarker = null;

// Bouton de géolocalisation
document.getElementById('geolocBtn').addEventListener('click', function() {
    if (!navigator.geolocation) {
        alert('La géolocalisation n\'est pas disponible sur votre navigateur');
        return;
    }

    const status = document.getElementById('geolocStatus');
    status.textContent = '📍 Localisation en cours...';
    status.style.display = 'block';

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Afficher mini-carte
            displayMiniMap(lat, lng);

            // Reverse geocoding pour renseigner l'adresse
            try {
                const reverseUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
                const reverseResponse = await fetch(reverseUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (reverseResponse.ok) {
                    const reverseData = await reverseResponse.json();
                    const adresseInput = document.getElementById('adresse');
                    if (reverseData.display_name) {
                        adresseInput.value = reverseData.display_name;
                    } else {
                        adresseInput.value = `Coordonnées : ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    }
                } else {
                    document.getElementById('adresse').value = `Coordonnées : ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            } catch (e) {
                document.getElementById('adresse').value = `Coordonnées : ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }

            status.innerHTML = '✓ Localisation réussie';
            status.style.background = '#E8F5E9';
            status.style.color = '#2E7D32';
        },
        (error) => {
            status.textContent = '✗ ' + (error.message || 'Erreur de localisation');
            status.style.background = '#FFEBEE';
            status.style.color = '#C62828';
        }
    );
});

// Auto-complétion des adresses
document.getElementById('adresse').addEventListener('input', async function(e) {
    const adresse = e.target.value;
    
    if (adresse.length < 3) {
        document.getElementById('addressSuggestions').style.display = 'none';
        return;
    }

    try {
        const response = await fetch('<?= APP_URL ?>/api/geocode?address=' + encodeURIComponent(adresse));
        const data = await response.json();

        const suggestionsBox = document.getElementById('addressSuggestions');
        suggestionsBox.innerHTML = '';

        if (data.results && data.results.length > 0) {
            data.results.forEach(result => {
                const div = document.createElement('div');
                div.style.cssText = 'padding:10px; border-bottom:1px solid var(--border-color); cursor:pointer; font-size:0.9rem;';
                div.innerHTML = result.display_name;
                
                div.addEventListener('mouseenter', () => {
                    div.style.background = 'var(--gray-50)';
                });
                div.addEventListener('mouseleave', () => {
                    div.style.background = 'white';
                });

                div.addEventListener('click', () => {
                    document.getElementById('adresse').value = result.display_name;
                    document.getElementById('latitude').value = result.lat;
                    document.getElementById('longitude').value = result.lon;
                    suggestionsBox.style.display = 'none';
                    displayMiniMap(result.lat, result.lon);
                });

                suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = 'block';
        } else {
            suggestionsBox.style.display = 'none';
        }
    } catch (error) {
        console.error('Erreur géocodage:', error);
    }
});

// Fermer les suggestions en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('#adresse') && !e.target.closest('#addressSuggestions')) {
        document.getElementById('addressSuggestions').style.display = 'none';
    }
});

// Afficher mini-carte
function displayMiniMap(lat, lng) {
    const container = document.getElementById('miniMapContainer');
    container.style.display = 'block';

    if (!miniMap) {
        miniMap = L.map('miniMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(miniMap);
    } else {
        miniMap.setView([lat, lng], 15);
        if (miniMapMarker) {
            miniMap.removeLayer(miniMapMarker);
        }
    }

    miniMapMarker = L.marker([lat, lng]).addTo(miniMap);
    document.getElementById('coordsDisplay').textContent = `Latitude: ${lat.toFixed(4)}, Longitude: ${lng.toFixed(4)}`;
}

// Rafraîchir la mini-carte si les coordonnées changent
document.getElementById('latitude').addEventListener('change', function() {
    const lat = parseFloat(this.value);
    const lng = parseFloat(document.getElementById('longitude').value);
    if (lat && lng) {
        displayMiniMap(lat, lng);
    }
});

document.getElementById('longitude').addEventListener('change', function() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(this.value);
    if (lat && lng) {
        displayMiniMap(lat, lng);
    }
});
</script>
