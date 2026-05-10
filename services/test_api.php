<?php
/**
 * services/test_api.php
 * Endpoint pour tester les composants du service environnemental
 * 
 * Utilisé par test_environmental_complete.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/EnvironmentalWeatherService.php';
    require_once __DIR__ . '/../controllers/ForumController.php';

    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    switch ($action) {
        case 'geocode':
            $address = $_GET['address'] ?? null;
            if (!$address) {
                throw new Exception('Adresse manquante');
            }
            $result = EnvironmentalWeatherService::geocodeAddress($address);
            echo json_encode($result ? ['success' => true, 'data' => $result] : ['success' => false, 'error' => 'Adresse non trouvée']);
            break;

        case 'weather':
            $lat = (float)($_GET['lat'] ?? 0);
            $lng = (float)($_GET['lng'] ?? 0);
            if (!$lat || !$lng) {
                throw new Exception('Latitude/Longitude manquantes');
            }
            $result = EnvironmentalWeatherService::getWeatherCondition($lat, $lng);
            echo json_encode($result ? ['success' => true, 'data' => $result] : ['success' => false, 'error' => 'Impossible de récupérer la météo']);
            break;

        case 'ai':
            $content = $_GET['content'] ?? null;
            if (!$content) {
                throw new Exception('Contenu manquant');
            }
            $tag = EnvironmentalWeatherService::analyzeContentWithAI($content, '');
            echo json_encode([
                'success' => true,
                'data' => [
                    'content_analyzed' => substr($content, 0, 50) . '...',
                    'ai_tag' => $tag,
                    'message' => 'Tag détecté avec succès'
                ]
            ]);
            break;

        case 'logic':
            $tag = $_GET['tag'] ?? null;
            $weather = $_GET['weather'] ?? null;
            if (!$tag || !$weather) {
                throw new Exception('Tag ou météo manquante');
            }
            $status = EnvironmentalWeatherService::determinePostStatus($tag, $weather);
            echo json_encode([
                'success' => true,
                'data' => [
                    'ai_tag' => $tag,
                    'weather_main' => $weather,
                    'determined_status' => $status,
                    'rule_applied' => ($status === 'Alerte Climatique') 
                        ? "✅ Corrélation détectée! ($tag + $weather)" 
                        : "❌ Pas de corrélation"
                ]
            ]);
            break;

        case 'workflow':
            $data = json_decode(file_get_contents('php://input'), true);
            $address = $data['address'] ?? null;
            $content = $data['content'] ?? null;
            $title = $data['title'] ?? null;

            if (!$address || !$content) {
                throw new Exception('Données manquantes');
            }

            $result = EnvironmentalWeatherService::processEnvironmentalData(
                $address,
                $content,
                $title
            );

            echo json_encode([
                'success' => $result['success'] ?? false,
                'data' => $result
            ]);
            break;

        case 'create_post':
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = (int)($data['user_id'] ?? 1);
            $title = $data['title'] ?? null;
            $content = $data['content'] ?? null;
            $address = $data['address'] ?? null;

            if (!$title || !$content) {
                throw new Exception('Titre ou contenu manquant');
            }

            $controller = new ForumController();
            $success = $controller->createPost(
                $userId,
                $title,
                $content,
                null,
                null,
                $address
            );

            echo json_encode([
                'success' => $success,
                'data' => [
                    'message' => $success ? 'Post créé avec succès!' : 'Erreur lors de la création',
                    'user_id' => $userId,
                    'title' => $title,
                    'address_provided' => $address ? true : false
                ]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Action inconnue']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
