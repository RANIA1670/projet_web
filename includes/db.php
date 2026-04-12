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
    $name = getenv('CITYZEN_DB_NAME') ?: 'cityzen';
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

    cityzen_db_seed_if_empty($pdo);

    return $pdo;
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
        'INSERT INTO users (username, password_hash, role, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())'
    );
    $stmt->execute([$agentUser, $hash, 'admin']);
}

/**
 * @param  list<array<string, mixed>>  $users
 */
function cityzen_db_import_users_from_json(PDO $pdo, array $users): void
{
    $ins = $pdo->prepare(
        'INSERT INTO users (id, username, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)'
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
            $ins->execute([$id, $username, $hash, $role, $createdAt]);
            $maxId = max($maxId, $id);
        } catch (PDOException) {
            // ignorer les doublons ou lignes invalides
        }
    }

    if ($maxId > 0) {
        $pdo->exec('ALTER TABLE users AUTO_INCREMENT = ' . ($maxId + 1));
    }
}
