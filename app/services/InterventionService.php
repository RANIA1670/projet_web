<?php
/**
 * CityZen - InterventionService
 * Logique métier extraite de InterventionModel
 */

require_once APP_PATH . 'models/InterventionModel.php';

class InterventionService
{
    private InterventionModel $model;

    public function __construct()
    {
        $this->model = new InterventionModel();
    }

    public function getModel(): InterventionModel
    {
        return $this->model;
    }

    /** Récupère toutes les interventions avec détails, paginées */
    public function findAllWithDetails(int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        return $this->searchWithDetails([], 'created_at', 'DESC', $page, $perPage);
    }

    /** Recherche filtrée et triée des interventions avec jointures */
    public function searchWithDetails(array $filters = [], string $orderBy = 'created_at', string $direction = 'DESC', int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        $db     = $this->model->getDb();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = '(i.titre LIKE :q OR i.description LIKE :q OR s.titre LIKE :q OR s.adresse LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['statut'])) {
            $where[]           = 'i.statut = :statut';
            $params[':statut']  = $filters['statut'];
        }
        if (!empty($filters['technicien_id'])) {
            $where[]                   = 'i.technicien_id = :technicien_id';
            $params[':technicien_id']   = $filters['technicien_id'];
        }

        $orderMap = [
            'id'             => 'i.id',
            'created_at'     => 'i.created_at',
            'date_planifiee' => 'i.date_planifiee',
            'statut'         => 'i.statut',
            'technicien_nom' => 'technicien_nom',
        ];
        $orderBy   = $orderMap[$orderBy] ?? 'i.created_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset    = ($page - 1) * $perPage;

        $sql = "SELECT i.*, s.titre AS signalement_titre, s.adresse, s.priorite,
                       CONCAT(u.prenom, ' ', u.nom) AS technicien_nom
                FROM interventions i
                LEFT JOIN signalements s ON i.signalement_id = s.id
                LEFT JOIN users u ON i.technicien_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy $direction
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Compte les interventions correspondant aux filtres */
    public function countFiltered(array $filters = []): int
    {
        $db     = $this->model->getDb();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]      = '(i.titre LIKE :q OR i.description LIKE :q OR s.titre LIKE :q OR s.adresse LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['statut'])) {
            $where[]           = 'i.statut = :statut';
            $params[':statut']  = $filters['statut'];
        }
        if (!empty($filters['technicien_id'])) {
            $where[]                  = 'i.technicien_id = :technicien_id';
            $params[':technicien_id'] = $filters['technicien_id'];
        }

        $sql  = "SELECT COUNT(*) FROM interventions i
                 LEFT JOIN signalements s ON i.signalement_id = s.id
                 LEFT JOIN users u ON i.technicien_id = u.id
                 WHERE " . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Récupère une intervention avec tous ses détails (jointures complètes) */
    public function findByIdWithDetails(int $id): ?array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("
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

    /** Récupère l'historique de suivi d'une intervention */
    public function getSuivi(int $interventionId): array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("
            SELECT sv.*, CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
            FROM suivi_interventions sv
            LEFT JOIN users u ON sv.created_by = u.id
            WHERE sv.intervention_id = :id
            ORDER BY sv.created_at ASC
        ");
        $stmt->execute([':id' => $interventionId]);
        return $stmt->fetchAll();
    }

    /** Ajoute une entrée dans l'historique de suivi */
    public function addSuivi(int $interventionId, string $statut, string $commentaire, ?int $userId): int
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("
            INSERT INTO suivi_interventions (intervention_id, statut, commentaire, created_by)
            VALUES (:intervention_id, :statut, :commentaire, :created_by)
        ");
        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':statut'          => $statut,
            ':commentaire'     => $commentaire,
            ':created_by'      => $userId,
        ]);
        return (int)$db->lastInsertId();
    }

    /** Statistiques par statut */
    public function getStatsByStatut(): array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("SELECT statut, COUNT(*) as total FROM interventions GROUP BY statut");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Statistiques par technicien */
    public function getStatsByTechnician(): array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("
            SELECT CONCAT(u.prenom, ' ', u.nom) AS technicien_nom, COUNT(i.id) as total
            FROM interventions i
            LEFT JOIN users u ON i.technicien_id = u.id
            GROUP BY i.technicien_id
            ORDER BY total DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Tendance mensuelle des interventions */
    public function getMonthlyCounts(int $months = 6): array
    {
        $months   = max(1, $months);
        $interval = $months - 1;
        $db       = $this->model->getDb();
        $stmt     = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, DATE_FORMAT(created_at, '%b %Y') AS month_label, COUNT(*) AS total FROM interventions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $interval MONTH) GROUP BY month_key ORDER BY month_key ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['month_key']] = [
                'label' => $row['month_label'],
                'total' => (int)$row['total'],
            ];
        }

        $result = [];
        for ($i = $interval; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $key  = date('Y-m', strtotime($date));
            $result[] = [
                'month_key'   => $key,
                'month_label' => date('M Y', strtotime($date)),
                'total'       => $map[$key]['total'] ?? 0,
            ];
        }

        return $result;
    }
}
