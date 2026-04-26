<?php
/**
 * CityZen - User Model
 */

require_once APP_PATH . 'core/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function register(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->insert($data);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function getTechniciens(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'technicien' ORDER BY nom ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
