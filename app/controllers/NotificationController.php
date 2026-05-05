<?php
/**
 * NotificationController - Gestion des notifications utilisateur
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/NotificationService.php';

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->requireLogin();
        $this->notificationService = new NotificationService();
    }

    /**
     * Afficher les notifications de l'utilisateur
     */
    public function index(array $params = []): void
    {
        $user = $this->currentUser();
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $notifications = $this->notificationService->getHistory($user['id'], $limit, $offset);

        $this->render('notification/index', [
            'pageTitle' => 'Mes Notifications',
            'notifications' => $notifications,
            'page' => $page
        ]);
    }

    /**
     * API - Récupérer les notifications non lues (JSON)
     */
    public function getUnread(array $params = []): void
    {
        header('Content-Type: application/json');
        
        if (!$this->currentUser()) {
            echo json_encode(['error' => 'Non autorisé', 'count' => 0]);
            exit;
        }

        $user = $this->currentUser();
        $limit = (int)($_GET['limit'] ?? 10);
        
        $unread = $this->notificationService->getUnread($user['id'], $limit);
        $count = $this->notificationService->countUnread($user['id']);

        echo json_encode([
            'count' => $count,
            'notifications' => $unread
        ]);
        exit;
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!$this->currentUser()) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }

        $notificationId = (int)($_POST['id'] ?? 0);

        if ($this->notificationService->markAsRead($notificationId)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur']);
        }
        exit;
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!$this->currentUser()) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }

        $user = $this->currentUser();

        if ($this->notificationService->markAllAsRead($user['id'])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur']);
        }
        exit;
    }

    /**
     * Récupérer le widget de notifications (pour la navbar)
     */
    public function getWidget(array $params = []): void
    {
        header('Content-Type: application/json');

        $user = $this->currentUser();
        
        if (!$user) {
            echo json_encode(['notifications' => [], 'count' => 0]);
            exit;
        }

        $unread = $this->notificationService->getUnread($user['id'], 5);
        $count = $this->notificationService->countUnread($user['id']);

        echo json_encode([
            'count' => $count,
            'notifications' => array_map(function($n) {
                return [
                    'id' => $n['id'],
                    'titre' => $n['titre'],
                    'message' => $n['message'],
                    'type' => $n['type'],
                    'created_at' => date('d/m/Y H:i', strtotime($n['created_at'])),
                    'time_ago' => $this->timeAgo($n['created_at'])
                ];
            }, $unread)
        ]);
        exit;
    }

    /**
     * Affichage formaté du temps écoulé
     */
    private function timeAgo(string $datetime): string
    {
        $now = new DateTime();
        $then = new DateTime($datetime);
        $interval = $now->diff($then);

        if ($interval->d > 0) {
            return $interval->d . 'j';
        } elseif ($interval->h > 0) {
            return $interval->h . 'h';
        } elseif ($interval->i > 0) {
            return $interval->i . 'min';
        } else {
            return 'À l\'instant';
        }
    }
}
