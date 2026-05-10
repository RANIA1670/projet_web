<?php

declare(strict_types=1);

/**
 * Module intégré CityZen : pas d’auth séparée Fatma sur `users`.
 * Techniciens = table interv_techniciens.
 */

require_once APP_PATH . 'models/UserModel.php';
require_once APP_PATH . 'models/TechnicienModel.php';

class UserService
{
    private UserModel $model;
    private TechnicienModel $technicienModel;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->technicienModel = new TechnicienModel();
    }

    public function getModel(): UserModel
    {
        return $this->model;
    }

    /** @deprecated Auth Fatma désactivé */
    public function findByEmail(string $email): ?array
    {
        $db = $this->model->getDb();
        $stmt = $db->prepare('SELECT id, username AS email, full_name, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    /** @deprecated */
    public function register(array $data): int
    {
        $data['password'] = password_hash((string) ($data['password'] ?? ''), PASSWORD_BCRYPT);

        return $this->model->insert($data);
    }

    /** @deprecated */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function getTechniciens(): array
    {
        $db = $this->technicienModel->getDb();
        $stmt = $db->query('SELECT * FROM interv_techniciens ORDER BY nom ASC, prenom ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTechniciensWithStatus(array $interventions): array
    {
        $techniciens = $this->getTechniciens();
        $busyTechs = [];

        foreach ($interventions as $inv) {
            if (in_array($inv['statut'], ['planifiee', 'en_cours'], true) && !empty($inv['technicien_id'])) {
                $busyTechs[(int) $inv['technicien_id']] = $inv;
            }
        }

        foreach ($techniciens as &$tech) {
            $id = (int) $tech['id'];
            $tech['is_busy'] = isset($busyTechs[$id]);
            $tech['current_intervention'] = $busyTechs[$id] ?? null;
        }
        unset($tech);

        return $techniciens;
    }

    public function getTechnicienModel(): TechnicienModel
    {
        return $this->technicienModel;
    }

    public function insertTechnicien(array $data): int
    {
        unset($data['password'], $data['role']);

        return $this->technicienModel->insert($data);
    }

    public function updateTechnicienRow(int $id, array $data): bool
    {
        unset($data['password'], $data['role']);

        return $this->technicienModel->update($id, $data);
    }

    public function findTechnicienById(int $id): ?array
    {
        $row = $this->technicienModel->findById($id);

        return $row ?: null;
    }

    public function deleteTechnicienById(int $id): bool
    {
        return $this->technicienModel->delete($id);
    }
}
