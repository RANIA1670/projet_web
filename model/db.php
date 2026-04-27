<?php

declare(strict_types=1);

/**
 * Connexion PDO MySQL (base `cityzen` par defaut, XAMPP).
 * Surcharge via variables d'environnement : CITYZEN_DB_HOST, CITYZEN_DB_NAME, CITYZEN_DB_USER, CITYZEN_DB_PASS
 */
function cityzen_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('CITYZEN_DB_HOST') ?: '127.0.0.1';
    $name = getenv('CITYZEN_DB_NAME') ?: 'projet';
    $user = getenv('CITYZEN_DB_USER') ?: 'root';
    $dbPass = getenv('CITYZEN_DB_PASS');
    $pass = $dbPass === false ? '' : (string) $dbPass;
    $charset = 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    cityzen_db_ensure_schema($pdo);
    cityzen_db_seed_if_empty($pdo);

    return $pdo;
}

function cityzen_db_column_exists(PDO $pdo, string $table, string $column): bool
{
    $name = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$name, $table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

function cityzen_db_index_exists(PDO $pdo, string $table, string $index): bool
{
    $name = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $stmt->execute([$name, $table, $index]);

    return (int) $stmt->fetchColumn() > 0;
}

function cityzen_db_ensure_schema(PDO $pdo): void
{
    if (!cityzen_db_column_exists($pdo, 'users', 'full_name')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN full_name VARCHAR(120) NULL AFTER username');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'email')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email VARCHAR(190) NULL AFTER full_name');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'blocked')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
        $pdo->exec('ALTER TABLE users ADD KEY idx_users_blocked (blocked)');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'birth_date')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN birth_date DATE NULL AFTER email');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'postal_code')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN postal_code VARCHAR(20) NULL AFTER birth_date');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'city')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN city VARCHAR(120) NULL AFTER postal_code');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'phone')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL AFTER city');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'profile_photo')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER phone');
    }

    if (!cityzen_db_column_exists($pdo, 'users', 'qr_token')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN qr_token CHAR(64) NULL AFTER profile_photo');
    }

    if (!cityzen_db_index_exists($pdo, 'users', 'uq_users_email')) {
        $pdo->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_email (email)');
    }

    if (!cityzen_db_index_exists($pdo, 'users', 'uq_users_qr_token')) {
        $pdo->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_qr_token (qr_token)');
    }
}

function cityzen_db_seed_if_empty(PDO $pdo): void
{
    $n = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($n > 0) {
        return;
    }

    // Ne pas re-importer automatiquement depuis users.json : une base vide resterait "vide"
    // pour l'utilisateur, mais se remplirait au premier chargement et ferait croire a des doublons.
    // Migration ponctuelle : variable d'environnement CITYZEN_IMPORT_USERS_JSON=1 (puis la retirer).
    if (getenv('CITYZEN_IMPORT_USERS_JSON') === '1') {
        $jsonPath = __DIR__ . '/../storage/users.json';
        if (is_file($jsonPath)) {
            $raw = file_get_contents($jsonPath);
            $data = json_decode((string) $raw, true);
            if (is_array($data) && isset($data['users']) && is_array($data['users']) && $data['users'] !== []) {
                cityzen_db_import_users_from_json($pdo, $data['users']);
            }
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
        return;
    }

    $agentUser = getenv('CITYZEN_AGENT_USER') ?: 'agent';
    $agentPass = getenv('CITYZEN_AGENT_PASS') ?: 'cityzen';
    $hash = password_hash($agentPass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash, role, blocked, created_at) VALUES (?, ?, ?, 0, UTC_TIMESTAMP())'
    );
    $stmt->execute([$agentUser, $hash, 'admin']);
}

/**
 * @param  list<array<string, mixed>>  $users
 */
function cityzen_db_import_users_from_json(PDO $pdo, array $users): void
{
    $ins = $pdo->prepare(
        'INSERT INTO users (id, username, full_name, email, password_hash, role, blocked, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $maxId = 0;
    foreach ($users as $row) {
        if (!is_array($row)) {
            continue;
        }

        $id = (int) ($row['id'] ?? 0);
        $username = trim((string) ($row['username'] ?? ''));
        $hash = (string) ($row['password_hash'] ?? '');
        $role = (string) ($row['role'] ?? 'user');
        $fullName = trim((string) ($row['full_name'] ?? ''));
        $email = trim((string) ($row['email'] ?? ''));
        $blocked = (int) ($row['blocked'] ?? 0) === 1 ? 1 : 0;
        if ($role !== 'admin' && $role !== 'user') {
            $role = 'user';
        }

        $createdRaw = (string) ($row['created_at'] ?? '');
        $ts = strtotime($createdRaw);
        $createdAt = $ts !== false ? gmdate('Y-m-d H:i:s', $ts) : gmdate('Y-m-d H:i:s');

        if ($username === '' || $hash === '' || $id < 1) {
            continue;
        }

        try {
            $ins->execute([$id, $username, $fullName !== '' ? $fullName : null, $email !== '' ? $email : null, $hash, $role, $blocked, $createdAt]);
            $maxId = max($maxId, $id);
        } catch (PDOException) {
            // ignorer les doublons ou lignes invalides
        }
    }

    if ($maxId > 0) {
        $pdo->exec('ALTER TABLE users AUTO_INCREMENT = ' . ($maxId + 1));
    }
}

function cityzen_register_user_with_email(string $email, string $password, string $fullName): array
{
    if ($email === '' || $password === '' || $fullName === '') {
        return ['ok' => false, 'error' => 'Email, mot de passe et nom complet requis.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Format d\'email invalide.'];
    }

    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Le mot de passe doit avoir au moins 8 caracteres.'];
    }

    try {
        $pdo = cityzen_db();

        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) {
            return ['ok' => false, 'error' => 'Cet email est deja utilise.'];
        }

        // Generate username from email
        $username = explode('@', $email)[0];
        $baseUsername = $username;
        $counter = 1;

        // Check if username exists and make it unique
        while (true) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            if ($stmt->fetch() === false) {
                break;
            }
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, full_name, password_hash, role, blocked, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
        );
        $stmt->execute([$username, $email, $fullName, $hash, 'user']);

        // Fetch and return the new user data
        $stmt = $pdo->prepare(
            'SELECT id, username, full_name, email, birth_date, postal_code, city, phone, profile_photo, password_hash, role, blocked, created_at, updated_at FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (is_array($user)) {
            return ['ok' => true, 'user' => $user];
        }

        return ['ok' => false, 'error' => 'Erreur lors de la creation du compte.'];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => 'Erreur base de donnees : ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => 'Erreur : ' . $e->getMessage()];
    }
}

function cityzen_user_stats(): array
{
    try {
        $pdo = cityzen_db();
        $row = $pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins,
                    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) AS users_count,
                    SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) AS blocked_count
             FROM users"
        )->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'admins' => (int) ($row['admins'] ?? 0),
            'users' => (int) ($row['users_count'] ?? 0),
            'blocked' => (int) ($row['blocked_count'] ?? 0),
        ];
    } catch (Throwable) {
        return ['total' => 0, 'admins' => 0, 'users' => 0, 'blocked' => 0];
    }
}
