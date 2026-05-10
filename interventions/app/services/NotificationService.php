<?php

declare(strict_types=1);

require_once APP_PATH . 'core/Database.php';

/**
 * Notifications liées aux utilisateurs CityZen (`users.id`).
 */
class NotificationService
{
    private PDO $db;

    private string $table = 'interv_notifications';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(
        int $userId,
        string $type,
        string $titre,
        ?string $message = null,
        ?int $signalementId = null,
        ?int $interventionId = null
    ): bool {
        $query = "INSERT INTO {$this->table} (user_id, type, titre, message, signalement_id, intervention_id)
                  VALUES (:user_id, :type, :titre, :message, :signalement_id, :intervention_id)";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':titre' => $titre,
            ':message' => $message,
            ':signalement_id' => $signalementId,
            ':intervention_id' => $interventionId,
        ]);
    }

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

    public function countUnread(int $userId): int
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}
                  WHERE user_id = :user_id AND lue = 0";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['count'] ?? 0);
    }

    public function markAsRead(int $notificationId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET lue = 1, read_at = NOW() WHERE id = :id"
        );

        return $stmt->execute([':id' => $notificationId]);
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET lue = 1, read_at = NOW()
             WHERE user_id = :user_id AND lue = 0"
        );

        return $stmt->execute([':user_id' => $userId]);
    }

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

    /** Techniciens ne sont plus des `users` CityZen : désactivé dans l’intégration. */
    public function notifyTechnicians(int $signalementId, string $priorite): void
    {
    }

    public function notifyStatusChange(int $signalementId, string $ancienStatut, string $nouveauStatut): void
    {
        $query = 'SELECT user_id, titre FROM interv_signalements WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $signalementId]);
        $signal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$signal || empty($signal['user_id'])) {
            return;
        }

        $messages = [
            'nouveau' => 'Votre signalement a été reçu',
            'en_attente' => 'Votre signalement est en attente de traitement',
            'en_cours' => 'Une intervention est en cours',
            'resolu' => 'Votre signalement a été résolu ✓',
            'ferme' => 'Votre signalement a été fermé',
        ];

        $this->create(
            (int) $signal['user_id'],
            'status_change',
            'Mise à jour : ' . $signal['titre'],
            $messages[$nouveauStatut] ?? 'Votre signalement a été mis à jour',
            $signalementId
        );
    }
}
