<!-- ========== CARTE INTERACTIVE DES SIGNALEMENTS ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Carte Interactive</span>
        </div>
        <h1><i class="fas fa-map" style="color:var(--secondary);"></i> Carte des Signalements</h1>
        <p>Visualisez tous les signalements actifs sur la carte interactive de la ville.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 350px; gap:24px; margin-bottom:24px;">
            <!-- Filtres -->
            <div style="grid-column:2; display:flex; flex-direction:column; gap:12px;">
                <div style="background:#fff; border:1px solid var(--border-color); border-radius:8px; padding:16px;">
                    <h4 style="margin:0 0 16px; font-size:0.95rem;">Filtrer</h4>
                    
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Statut</label>
                            <select id="filterStatut" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem;">
                                <option value="">Tous les statuts</option>
                                <option value="nouveau">🔴 Nouveau</option>
                                <option value="en_attente">🟡 En attente</option>
                                <option value="en_cours">🔵 En cours</option>
                                <option value="resolu">🟢 Résolu</option>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Priorité</label>
                            <select id="filterPriorite" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem;">
                                <option value="">Toutes les priorités</option>
                                <option value="urgente">🔴 Urgente</option>
                                <option value="haute">🟠 Haute</option>
                                <option value="moyenne">🟡 Moyenne</option>
                                <option value="faible">🟢 Faible</option>
                            </select>
                        </div>

                        <button onclick="updateMap()" style="background:var(--secondary); color:white; border:none; padding:10px; border-radius:4px; cursor:pointer; font-weight:600;">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>

                <!-- Statistiques -->
                <div style="background:var(--gray-50); border:1px solid var(--border-color); border-radius:8px; padding:16px;">
                    <h4 style="margin:0 0 12px; font-size:0.95rem;">Résumé</h4>
                    <div id="statsBox" style="font-size:0.9rem; line-height:1.8;">
                        <p>Chargement...</p>
                    </div>
                </div>
            </div>

            <!-- Carte -->
            <div id="map" style="height:700px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); grid-row:1;"></div>
        </div>

        <!-- Légende -->
        <div style="background:#fff; border:1px solid var(--border-color); border-radius:8px; padding:20px; margin-top:24px;">
            <h3 style="margin:0 0 16px; font-size:1rem;">Légende des statuts</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:16px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:20px; height:20px; background:#E74C3C; border-radius:50%; border:2px solid #C0392B;"></div>
                    <span>Nouveau</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:20px; height:20px; background:#F39C12; border-radius:50%; border:2px solid #D68910;"></div>
                    <span>En attente</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:20px; height:20px; background:#3498DB; border-radius:50%; border:2px solid #2980B9;"></div>
                    <span>En cours</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:20px; height:20px; background:#27AE60; border-radius:50%; border:2px solid #229954;"></div>
                    <span>Résolu</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leaflet & Leaflet Clustering -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<!-- Leaflet MarkerCluster -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.Default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/leaflet.markercluster.min.js"></script>

<script>
// Initialiser la carte (centré sur Tunis par défaut)
const map = L.map('map').setView([36.8065, 10.1686], 12);

// Ajouter la tuile OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

let markersLayer = null;
let markers = [];

// Récupérer et afficher les signalements
async function updateMap() {
    const statut = document.getElementById('filterStatut').value;
    const priorite = document.getElementById('filterPriorite').value;

    const url = new URL('<?= APP_URL ?>/api/signalements/carte', window.location.origin);
    if (statut) url.searchParams.append('statut', statut);
    if (priorite) url.searchParams.append('priorite', priorite);

    try {
        const response = await fetch(url);
        const geojson = await response.json();

        // Supprimer l'ancienne couche
        if (markersLayer) {
            map.removeLayer(markersLayer);
        }

        markersLayer = L.markerClusterGroup();
        markers = [];
        let stats = { total: 0, resolu: 0, urgent: 0 };

        // Créer les marqueurs
        geojson.features.forEach(feature => {
            const props = feature.properties;
            const coords = feature.geometry.coordinates;

            stats.total++;
            if (props.statut === 'resolu') stats.resolu++;
            if (props.priorite === 'urgente') stats.urgent++;

            // Créer une icône avec couleur
            const color = props.couleur;
            const markerHtml = `
                <div style="background:${color}; width:30px; height:30px; border-radius:50%; 
                            display:flex; align-items:center; justify-content:center; 
                            color:white; font-weight:bold; border:2px solid white;
                            box-shadow:0 2px 6px rgba(0,0,0,0.3);">
                    <i class="fas fa-map-pin"></i>
                </div>
            `;

            const marker = L.marker([coords[1], coords[0]], {
                icon: L.divIcon({
                    html: markerHtml,
                    className: 'custom-marker',
                    iconSize: [30, 30],
                    popupAnchor: [0, -15]
                })
            });

            // Popup au clic
            const popupContent = `
                <div style="max-width:280px;">
                    <h4 style="margin:0 0 8px; font-size:0.95rem;">${props.titre}</h4>
                    <p style="margin:0 0 8px; font-size:0.85rem; color:var(--text-muted);">${props.description}</p>
                    <p style="margin:0 0 8px; font-size:0.85rem;"><strong>📍 </strong>${props.adresse}</p>
                    <p style="margin:0 0 8px; font-size:0.85rem;"><strong>Statut:</strong> ${props.statut}</p>
                    <p style="margin:0 0 8px; font-size:0.85rem;"><strong>Priorité:</strong> ${props.priorite}</p>
                    <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">${props.created_at}</p>
                    <a href="<?= APP_URL ?>/signalement/${props.id}" style="display:inline-block; margin-top:8px; 
                       background:var(--secondary); color:white; padding:6px 12px; 
                       border-radius:4px; text-decoration:none; font-size:0.85rem;">
                        Voir détails
                    </a>
                </div>
            `;

            marker.bindPopup(popupContent);
            markersLayer.addLayer(marker);
            markers.push(marker);
        });

        map.addLayer(markersLayer);

        // Mettre à jour les stats
        document.getElementById('statsBox').innerHTML = `
            <p><strong>${stats.total}</strong> signalement${stats.total > 1 ? 's' : ''}</p>
            <p><strong>${stats.resolu}</strong> résolu${stats.resolu > 1 ? 's' : ''}</p>
            <p><strong>${stats.urgent}</strong> urgent${stats.urgent > 1 ? 's' : ''}</p>
        `;

    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des données');
    }
}

// Charger au démarrage
document.addEventListener('DOMContentLoaded', updateMap);

// Gestion des filtres
document.getElementById('filterStatut').addEventListener('change', updateMap);
document.getElementById('filterPriorite').addEventListener('change', updateMap);
</script>
