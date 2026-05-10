<?php

declare(strict_types=1);

require_once APP_PATH . 'models/SignalementModel.php';

class SignalementService
{
    private SignalementModel $model;

    public function __construct()
    {
        $this->model = new SignalementModel();
    }

    public function getModel(): SignalementModel
    {
        return $this->model;
    }

    public function findAllWithDetails(int $page = 1, int $perPage = ITEMS_PER_PAGE, array $filters = []): array
    {
        $db = $this->model->getDb();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['statut'])) {
            $where[] = 's.statut = :statut';
            $params[':statut'] = $filters['statut'];
        }
        if (!empty($filters['priorite'])) {
            $where[] = 's.priorite = :priorite';
            $params[':priorite'] = $filters['priorite'];
        }
        if (!empty($filters['categorie_id'])) {
            $where[] = 's.categorie_id = :categorie_id';
            $params[':categorie_id'] = $filters['categorie_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(s.titre LIKE :search OR s.adresse LIKE :search2)';
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur,
                       COALESCE(NULLIF(TRIM(u.full_name), ''), u.username, 'Citoyen') AS auteur_nom
                FROM interv_signalements s
                LEFT JOIN interv_categories c ON s.categorie_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE $whereStr
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters = []): int
    {
        $db = $this->model->getDb();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['statut'])) {
            $where[] = 's.statut = :statut';
            $params[':statut'] = $filters['statut'];
        }
        if (!empty($filters['priorite'])) {
            $where[] = 's.priorite = :priorite';
            $params[':priorite'] = $filters['priorite'];
        }
        if (!empty($filters['categorie_id'])) {
            $where[] = 's.categorie_id = :categorie_id';
            $params[':categorie_id'] = $filters['categorie_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(s.titre LIKE :search OR s.adresse LIKE :search2)';
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM interv_signalements s LEFT JOIN interv_categories c ON s.categorie_id = c.id WHERE $whereStr"
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findByIdWithDetails(int $id): ?array
    {
        $db = $this->model->getDb();
        $stmt = $db->prepare(
            "SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur,
                   COALESCE(NULLIF(TRIM(u.full_name), ''), u.username, 'Citoyen') AS auteur_nom,
                   u.email AS auteur_email
            FROM interv_signalements s
            LEFT JOIN interv_categories c ON s.categorie_id = c.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getStatsByStatut(): array
    {
        $db = $this->model->getDb();
        $stmt = $db->query('SELECT statut, COUNT(*) as total FROM interv_signalements GROUP BY statut');

        return $stmt->fetchAll();
    }

    public function getStatsByCategorie(): array
    {
        $db = $this->model->getDb();
        $stmt = $db->prepare(
            "SELECT c.nom, c.couleur, c.icone, COUNT(s.id) as total
            FROM interv_categories c
            LEFT JOIN interv_signalements s ON s.categorie_id = c.id
            GROUP BY c.id ORDER BY total DESC LIMIT 6"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTrendByDay(int $days = 7): array
    {
        $days = max(1, $days);
        $interval = $days - 1;
        $db = $this->model->getDb();
        $stmt = $db->prepare(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total FROM interv_signalements
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $interval DAY) GROUP BY day ORDER BY day ASC"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['day']] = (int) $row['total'];
        }

        $result = [];
        for ($i = $interval; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = [
                'day' => $date,
                'label' => date('d/m', strtotime($date)),
                'total' => $map[$date] ?? 0,
            ];
        }

        return $result;
    }

    public function getRecent(int $limit = 5): array
    {
        $db = $this->model->getDb();
        $stmt = $db->prepare(
            "SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur
            FROM interv_signalements s
            LEFT JOIN interv_categories c ON s.categorie_id = c.id
            ORDER BY s.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
