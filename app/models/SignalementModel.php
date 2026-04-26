<?php
/**
 * CityZen - Signalement Model
 */

require_once APP_PATH . 'core/Model.php';

class SignalementModel extends Model
{
    protected string $table = 'signalements';

    public function findAllWithDetails(int $page = 1, int $perPage = ITEMS_PER_PAGE, array $filters = []): array
    {
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
            $params[':search']  = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        // [MÉTIER] : Récupération filtrée et paginée des signalements
        // [JOINTURE] : Jointure entre les entités signalements, categories et users
        $sql = "SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur,
                       CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
                FROM signalements s
                LEFT JOIN categories c ON s.categorie_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE $whereStr
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['statut']))      { $where[] = 's.statut = :statut';           $params[':statut']      = $filters['statut']; }
        if (!empty($filters['priorite']))    { $where[] = 's.priorite = :priorite';       $params[':priorite']    = $filters['priorite']; }
        if (!empty($filters['categorie_id'])){ $where[] = 's.categorie_id = :categorie_id'; $params[':categorie_id']= $filters['categorie_id']; }
        if (!empty($filters['search']))      { $where[] = '(s.titre LIKE :search OR s.adresse LIKE :search2)'; $params[':search'] = '%'.$filters['search'].'%'; $params[':search2'] = '%'.$filters['search'].'%'; }
        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM signalements s LEFT JOIN categories c ON s.categorie_id = c.id WHERE $whereStr");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur,
                   CONCAT(u.prenom, ' ', u.nom) AS auteur_nom, u.email AS auteur_email
            FROM signalements s
            LEFT JOIN categories c ON s.categorie_id = c.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getStatsByStatut(): array
    {
        $stmt = $this->db->prepare("SELECT statut, COUNT(*) as total FROM signalements GROUP BY statut");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // [MÉTIER] : Calcul des statistiques par catégorie
    // [JOINTURE] : Jointure entre categories et signalements pour le comptage
    public function getStatsByCategorie(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.nom, c.couleur, c.icone, COUNT(s.id) as total
            FROM categories c
            LEFT JOIN signalements s ON s.categorie_id = c.id
            GROUP BY c.id ORDER BY total DESC LIMIT 6
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nom AS categorie_nom, c.icone AS categorie_icone, c.couleur AS categorie_couleur
            FROM signalements s
            LEFT JOIN categories c ON s.categorie_id = c.id
            ORDER BY s.created_at DESC LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
