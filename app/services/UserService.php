<?php
/**
 * CityZen - UserService
 * Logique métier extraite de UserModel
 */

require_once APP_PATH . 'models/UserModel.php';

class UserService
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function getModel(): UserModel
    {
        return $this->model;
    }

    /** Trouve un utilisateur par son email */
    public function findByEmail(string $email): ?array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /** Crée un utilisateur avec mot de passe hashé */
    public function register(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->model->insert($data);
    }

    /** Vérifie si le mot de passe correspond au hash */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** Récupère tous les techniciens triés par nom */
    public function getTechniciens(): array
    {
        $db   = $this->model->getDb();
        $stmt = $db->prepare("SELECT * FROM users WHERE role = 'technicien' ORDER BY nom ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère les techniciens avec leur statut d'occupation
     * (occupé si une intervention planifiée ou en cours leur est assignée)
     */
    public function getTechniciensWithStatus(array $interventions): array
    {
        $techniciens = $this->getTechniciens();
        $busyTechs   = [];

        foreach ($interventions as $inv) {
            if (in_array($inv['statut'], ['planifiee', 'en_cours']) && $inv['technicien_id']) {
                $busyTechs[$inv['technicien_id']] = $inv;
            }
        }

        foreach ($techniciens as &$tech) {
            $tech['is_busy']             = isset($busyTechs[$tech['id']]);
            $tech['current_intervention'] = $busyTechs[$tech['id']] ?? null;
        }
        unset($tech);

        return $techniciens;
    }
}
