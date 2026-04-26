<?php
/**
 * CityZen - Category Model
 */

require_once APP_PATH . 'core/Model.php';

class CategorieModel extends Model
{
    protected string $table = 'categories';

    // [MÉTIER] : Récupération des catégories avec le nombre de signalements associés
    // [JOINTURE] : Jointure entre categories et signalements
    public function findAllWithCount(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(s.id) as count_signalements
            FROM categories c
            LEFT JOIN signalements s ON s.categorie_id = c.id
            GROUP BY c.id
            ORDER BY c.nom ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
