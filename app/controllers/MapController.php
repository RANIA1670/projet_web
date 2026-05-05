<?php
/**
 * MapController - Gestion de la cartographie des signalements
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/SignalementService.php';

class MapController extends Controller
{
    private SignalementService $signalementService;

    public function __construct()
    {
        $this->signalementService = new SignalementService();
    }

    /**
     * Afficher la carte interactive
     */
    public function index(array $params = []): void
    {
        $filters = [
            'statut' => $_GET['statut'] ?? null,
            'priorite' => $_GET['priorite'] ?? null,
            'categorie_id' => $_GET['categorie_id'] ?? null,
        ];

        $signalements = $this->getSignalementsWithCoordinates($filters);

        $this->render('map/index', [
            'pageTitle' => 'Carte des Signalements',
            'signalements' => $signalements,
            'filters' => $filters
        ]);
    }

    /**
     * API - Récupérer les signalements en JSON pour la carte
     */
    public function getSignalements(array $params = []): void
    {
        header('Content-Type: application/json');

        $filters = [
            'statut' => $_GET['statut'] ?? null,
            'priorite' => $_GET['priorite'] ?? null,
            'categorie_id' => $_GET['categorie_id'] ?? null,
        ];

        $signalements = $this->getSignalementsWithCoordinates($filters);

        // Formater pour Leaflet
        $features = [];
        foreach ($signalements as $sig) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$sig['longitude'], (float)$sig['latitude']]
                ],
                'properties' => [
                    'id' => $sig['id'],
                    'titre' => $sig['titre'],
                    'description' => substr($sig['description'], 0, 100) . '...',
                    'adresse' => $sig['adresse'],
                    'statut' => $sig['statut'],
                    'priorite' => $sig['priorite'],
                    'categorie' => $sig['categorie_nom'],
                    'icone' => $sig['icone'],
                    'couleur' => $this->getColorByStatut($sig['statut']),
                    'created_at' => date('d/m/Y H:i', strtotime($sig['created_at']))
                ]
            ];
        }

        echo json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
        exit;
    }

    /**
     * Obtenir les signalements avec coordonnées
     */
    private function getSignalementsWithCoordinates(array $filters = []): array
    {
        $query = "SELECT s.*, c.nom as categorie_nom, c.icone, c.couleur
                  FROM signalements s
                  LEFT JOIN categories c ON s.categorie_id = c.id
                  WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL";

        $params = [];

        if ($filters['statut']) {
            $query .= " AND s.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        if ($filters['priorite']) {
            $query .= " AND s.priorite = :priorite";
            $params[':priorite'] = $filters['priorite'];
        }

        if ($filters['categorie_id']) {
            $query .= " AND s.categorie_id = :categorie_id";
            $params[':categorie_id'] = $filters['categorie_id'];
        }

        $query .= " ORDER BY s.created_at DESC LIMIT 500";

        $db = Database::getInstance();
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir les statistiques par zone
     */
    public function getStatsByZone(array $params = []): void
    {
        header('Content-Type: application/json');

        // Récupérer les signalements groupés par premier chiffre de latitude/longitude (zones approximatives)
        $query = "SELECT 
                    ROUND(latitude, 1) as lat_zone,
                    ROUND(longitude, 1) as lng_zone,
                    COUNT(*) as count,
                    COUNT(CASE WHEN statut='resolu' THEN 1 END) as resolved,
                    COUNT(CASE WHEN priorite='urgente' THEN 1 END) as urgent
                  FROM signalements
                  WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                  GROUP BY lat_zone, lng_zone
                  ORDER BY count DESC";

        $db = Database::getInstance();
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($stats);
        exit;
    }

    /**
     * Récupérer la couleur selon le statut
     */
    private function getColorByStatut(string $statut): string
    {
        return match($statut) {
            'nouveau' => '#E74C3C',      // Rouge
            'en_attente' => '#F39C12',   // Orange
            'en_cours' => '#3498DB',     // Bleu
            'resolu' => '#27AE60',       // Vert
            'ferme' => '#95A5A6',        // Gris
            default => '#2C3E50'         // Noir
        };
    }

    /**
     * Récupérer la géolocalisation (API Nominatim OpenStreetMap)
     */
    public function geocode(array $params = []): void
    {
        header('Content-Type: application/json');

        $adresse = $_GET['address'] ?? '';

        if (strlen($adresse) < 3) {
            echo json_encode(['error' => 'Adresse trop courte']);
            exit;
        }

        try {
            // Utiliser l'API Nominatim (gratuit, pas de clé requise)
            $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($adresse) . '&format=json&limit=5';
            
            // Ajouter un user-agent obligatoire
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: CityZen/1.0'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                echo json_encode(['error' => 'Service de géolocalisation indisponible']);
                exit;
            }

            $results = json_decode($response, true);
            
            if (empty($results)) {
                echo json_encode(['results' => []]);
            } else {
                echo json_encode([
                    'results' => array_map(function($r) {
                        return [
                            'lat' => $r['lat'],
                            'lon' => $r['lon'],
                            'display_name' => $r['display_name'],
                            'address' => $r['address'] ?? []
                        ];
                    }, $results)
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
