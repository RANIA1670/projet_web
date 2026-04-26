<?php
/**
 * CityZen - Intervention Model
 */

require_once APP_PATH . 'core/Model.php';

class InterventionModel extends Model
{
    protected string $table = 'interventions';

    public function findAllWithDetails(int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        $offset = ($page - 1) * $perPage;
        // [JOINTURE] : Jointure entre interventions, signalements et users
        $stmt = $this->db->prepare("
            SELECT i.*, s.titre AS signalement_titre, s.adresse, s.priorite,
                   CONCAT(u.prenom, ' ', u.nom) AS technicien_nom
            FROM interventions i
            LEFT JOIN signalements s ON i.signalement_id = s.id
            LEFT JOIN users u ON i.technicien_id = u.id
            ORDER BY i.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT i.*, s.titre AS signalement_titre, s.adresse, s.description AS signalement_desc,
                   s.priorite, s.categorie_id, c.nom AS categorie_nom, c.icone AS categorie_icone,
                   CONCAT(u.prenom, ' ', u.nom) AS technicien_nom, u.email AS technicien_email, u.telephone AS technicien_tel
            FROM interventions i
            LEFT JOIN signalements s ON i.signalement_id = s.id
            LEFT JOIN categories c ON s.categorie_id = c.id
            LEFT JOIN users u ON i.technicien_id = u.id
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getSuivi(int $interventionId): array
    {
        $stmt = $this->db->prepare("
            SELECT sv.*, CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
            FROM suivi_interventions sv
            LEFT JOIN users u ON sv.created_by = u.id
            WHERE sv.intervention_id = :id
            ORDER BY sv.created_at ASC
        ");
        $stmt->execute([':id' => $interventionId]);
        return $stmt->fetchAll();
    }

    // [MÉTIER] : Gestion de l'historique et de l'avancement d'une intervention
    public function addSuivi(int $interventionId, string $statut, string $commentaire, ?int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO suivi_interventions (intervention_id, statut, commentaire, created_by)
            VALUES (:intervention_id, :statut, :commentaire, :created_by)
        ");
        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':statut'          => $statut,
            ':commentaire'     => $commentaire,
            ':created_by'      => $userId
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getStatsByStatut(): array
    {
        $stmt = $this->db->prepare("SELECT statut, COUNT(*) as total FROM interventions GROUP BY statut");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
