<?php
/**
 * CityZen - CategorieService
 * Logique métier extraite de CategorieModel
 */

require_once APP_PATH . 'models/CategorieModel.php';

class CategorieService
{
    private CategorieModel $model;

    public function __construct()
    {
        $this->model = new CategorieModel();
    }

    public function getModel(): CategorieModel
    {
        return $this->model;
    }

    /** Récupère toutes les catégories avec le nombre de signalements associés */
    public function findAllWithCount(): array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("
            SELECT c.*, COUNT(s.id) as count_signalements
            FROM interv_categories c
            LEFT JOIN interv_signalements s ON s.categorie_id = c.id
            GROUP BY c.id
            ORDER BY c.nom ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
