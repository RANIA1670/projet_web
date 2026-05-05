<?php
/**
 * NotificationService - Gestion des notifications
 */

require_once APP_PATH . 'core/Model.php';

class NotificationService
{
    private $db;
    private $table = 'notifications';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Créer une notification
     */
    public function create(int $userId, string $type, string $titre, ?string $message = null, ?int $signalementId = null, ?int $interventionId = null): bool
    {
        $query = "INSERT INTO {$this->table} (user_id, type, titre, message, signalement_id, intervention_id) 
                  VALUES (:user_id, :type, :titre, :message, :signalement_id, :intervention_id)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':titre' => $titre,
            ':message' => $message,
            ':signalement_id' => $signalementId,
            ':intervention_id' => $interventionId
        ]);
    }

    /**
     * Récupérer les notifications non lues d'un utilisateur
     */
    public function getUnread(int $userId, int $limit = 10): array
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = :user_id AND lue = 0 
                  ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les notifications non lues
     */
    public function countUnread(int $userId): int
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} 
                  WHERE user_id = :user_id AND lue = 0";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Marquer comme lue
     */
    public function markAsRead(int $notificationId): bool
    {
        $query = "UPDATE {$this->table} SET lue = 1, read_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $notificationId]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(int $userId): bool
    {
        $query = "UPDATE {$this->table} SET lue = 1, read_at = NOW() 
                  WHERE user_id = :user_id AND lue = 0";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Récupérer l'historique des notifications
     */
    public function getHistory(int $userId, int $limit = 50, int $offset = 0): array
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Notifier les techniciens d'un nouveau signalement urgente
     */
    public function notifyTechnicians(int $signalementId, string $priorite): void
    {
        // Récupérer tous les techniciens actifs
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'technicien'");
        $stmt->execute();
        $techniciens = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $titrePriorite = match($priorite) {
            'urgente' => '🔴 URGENT',
            'haute' => '🟠 Haute priorité',
            'moyenne' => '🟡 Moyenne priorité',
            default => '🟢 Faible priorité'
        };
        
        foreach ($techniciens as $techId) {
            $this->create(
                $techId,
                'new_signalement',
                $titrePriorite . ' - Nouveau signalement',
                'Un nouveau signalement nécessite une intervention',
                $signalementId
            );
        }
    }

    /**
     * Notifier le citoyen d'une mise à jour
     */
    public function notifyStatusChange(int $signalementId, string $ancienStatut, string $nouveauStatut): void
    {
        // Récupérer le signalement
        $query = "SELECT user_id, titre FROM signalements WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $signalementId]);
        $signal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$signal) return;
        
        $messages = [
            'nouveau' => 'Votre signalement a été reçu',
            'en_attente' => 'Votre signalement est en attente de traitement',
            'en_cours' => 'Une intervention est en cours',
            'resolu' => 'Votre signalement a été résolu ✓',
            'ferme' => 'Votre signalement a été fermé'
        ];
        
        $this->create(
            $signal['user_id'],
            'status_change',
            'Mise à jour : ' . $signal['titre'],
            $messages[$nouveauStatut] ?? 'Votre signalement a été mis à jour',
            $signalementId
        );
    }
}
