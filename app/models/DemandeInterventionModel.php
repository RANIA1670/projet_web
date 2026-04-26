<?php
/**
 * CityZen - DemandeIntervention Model
 */

require_once APP_PATH . 'core/Model.php';

class DemandeInterventionModel extends Model
{
    protected string $table = 'demandes_intervention';

    public function findAllWithDetails(int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT d.*, s.titre AS signalement_titre
            FROM demandes_intervention d
            LEFT JOIN signalements s ON d.signalement_id = s.id
            ORDER BY d.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
