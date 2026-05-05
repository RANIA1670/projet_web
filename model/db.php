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

    // Créer la table pour les codes de réinitialisation
    cityzen_db_ensure_password_reset_table($pdo);
}

function cityzen_db_ensure_password_reset_table(PDO $pdo): void
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS password_reset_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            method VARCHAR(10) NOT NULL, -- \'email\' or \'sms\'
            contact VARCHAR(255) NOT NULL, -- email or phone number
            code VARCHAR(6) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ');
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

function cityzen_detailed_stats(?string $dateFilter = null, ?string $categoryFilter = null): array
{
    try {
        $pdo = cityzen_db();
        
        // Date filter clause
        $dateClause = '';
        if ($dateFilter) {
            switch ($dateFilter) {
                case 'today':
                    $dateClause = "AND DATE(created_at) = CURDATE()";
                    break;
                case 'week':
                    $dateClause = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $dateClause = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'year':
                    $dateClause = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // Basic stats
        $basicStats = $pdo->query(
            "SELECT COUNT(*) AS total_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS total_admins,
                    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) AS total_regular_users,
                    SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) AS total_blocked,
                    SUM(CASE WHEN profile_photo IS NOT NULL AND profile_photo != '' THEN 1 ELSE 0 END) AS with_photos,
                    SUM(CASE WHEN qr_token IS NOT NULL THEN 1 ELSE 0 END) AS with_qr_codes
             FROM users 
             WHERE 1=1 $dateClause"
        )->fetch();
        
        // Daily registrations (last 30 days)
        $dailyRegistrations = $pdo->query(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM users 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date DESC
             LIMIT 30"
        )->fetchAll();
        
        // Weekly registrations (last 12 weeks)
        $weeklyRegistrations = $pdo->query(
            "SELECT YEARWEEK(created_at) as week, COUNT(*) as count,
                    MIN(DATE(created_at)) as week_start
             FROM users 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
             GROUP BY YEARWEEK(created_at)
             ORDER BY week DESC
             LIMIT 12"
        )->fetchAll();
        
        // Monthly registrations (last 12 months)
        $monthlyRegistrations = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count,
                    MONTHNAME(created_at) as month_name
             FROM users 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m'), MONTHNAME(created_at)
             ORDER BY month DESC
             LIMIT 12"
        )->fetchAll();
        
        // User distribution by role
        $roleDistribution = $pdo->query(
            "SELECT role, COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users), 2) as percentage
             FROM users 
             WHERE 1=1 $dateClause
             GROUP BY role
             ORDER BY count DESC"
        )->fetchAll();
        
        // User distribution by city (top 10)
        $cityDistribution = $pdo->query(
            "SELECT city, COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users WHERE city IS NOT NULL), 2) as percentage
             FROM users 
             WHERE city IS NOT NULL AND city != '' $dateClause
             GROUP BY city
             ORDER BY count DESC
             LIMIT 10"
        )->fetchAll();
        
        // Recent registrations (last 7 days)
        $recentRegistrations = $pdo->query(
            "SELECT id, username, full_name, email, city, created_at, role
             FROM users 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY created_at DESC
             LIMIT 10"
        )->fetchAll();
        
        // Registration trends (growth rate)
        $growthStats = $pdo->query(
            "SELECT 
                COUNT(*) as current_period,
                (SELECT COUNT(*) FROM users 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND 
                       created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)) as previous_period,
                ROUND(((COUNT(*) - 
                 (SELECT COUNT(*) FROM users 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND 
                        created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY))) * 100.0 / 
                 (SELECT COUNT(*) FROM users 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND 
                        created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY))), 2) as growth_rate
             FROM users 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )->fetch();
        
        return [
            'basic_stats' => [
                'total_users' => (int) ($basicStats['total_users'] ?? 0),
                'total_admins' => (int) ($basicStats['total_admins'] ?? 0),
                'total_regular_users' => (int) ($basicStats['total_regular_users'] ?? 0),
                'total_blocked' => (int) ($basicStats['total_blocked'] ?? 0),
                'with_photos' => (int) ($basicStats['with_photos'] ?? 0),
                'with_qr_codes' => (int) ($basicStats['with_qr_codes'] ?? 0),
            ],
            'daily_registrations' => $dailyRegistrations,
            'weekly_registrations' => $weeklyRegistrations,
            'monthly_registrations' => $monthlyRegistrations,
            'role_distribution' => $roleDistribution,
            'city_distribution' => $cityDistribution,
            'recent_registrations' => $recentRegistrations,
            'growth_stats' => [
                'current_period' => (int) ($growthStats['current_period'] ?? 0),
                'previous_period' => (int) ($growthStats['previous_period'] ?? 0),
                'growth_rate' => (float) ($growthStats['growth_rate'] ?? 0),
            ],
            'date_filter' => $dateFilter,
        ];
    } catch (Throwable $e) {
        return [
            'basic_stats' => ['total_users' => 0, 'total_admins' => 0, 'total_regular_users' => 0, 'total_blocked' => 0, 'with_photos' => 0, 'with_qr_codes' => 0],
            'daily_registrations' => [],
            'weekly_registrations' => [],
            'monthly_registrations' => [],
            'role_distribution' => [],
            'city_distribution' => [],
            'recent_registrations' => [],
            'growth_stats' => ['current_period' => 0, 'previous_period' => 0, 'growth_rate' => 0],
            'date_filter' => $dateFilter,
            'error' => $e->getMessage(),
        ];
    }
}

// Fonctions pour la gestion des codes de réinitialisation de mot de passe
function cityzen_generate_reset_code(): string
{
    return sprintf('%06d', mt_rand(0, 999999));
}

function cityzen_create_password_reset_code(int $userId, string $type, string $contact): array
{
    try {
        $pdo = cityzen_db();
        
        // Nettoyer les anciens codes non utilisés pour cet utilisateur
        $pdo->prepare('DELETE FROM password_reset_codes WHERE user_id = ? AND used_at IS NULL AND expires_at < NOW()')->execute([$userId]);
        
        $code = cityzen_generate_reset_code();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $pdo->prepare('
            INSERT INTO password_reset_codes (user_id, code, type, contact, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        if ($stmt->execute([$userId, $code, $type, $contact, $expiresAt])) {
            return ['ok' => true, 'code' => $code, 'expires_at' => $expiresAt];
        }
        
        return ['ok' => false, 'error' => 'Erreur lors de la création du code.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()];
    }
}

function cityzen_verify_reset_code(string $code): array
{
    try {
        $pdo = cityzen_db();
        
        $stmt = $pdo->prepare('
            SELECT prc.*, u.username, u.email, u.phone 
            FROM password_reset_codes prc
            JOIN users u ON prc.user_id = u.id
            WHERE prc.code = ? AND prc.used_at IS NULL AND prc.expires_at > NOW()
            ORDER BY prc.created_at DESC
            LIMIT 1
        ');
        
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        
        if ($result) {
            return ['ok' => true, 'reset' => $result];
        }
        
        return ['ok' => false, 'error' => 'Code invalide ou expiré.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()];
    }
}

function cityzen_mark_reset_code_used(string $code): bool
{
    try {
        $pdo = cityzen_db();
        $stmt = $pdo->prepare('UPDATE password_reset_codes SET used_at = NOW() WHERE code = ?');
        return $stmt->execute([$code]);
    } catch (Throwable) {
        return false;
    }
}

function cityzen_normalize_phone_input(string $value): string
{
    $raw = trim($value);
    if ($raw === '') {
        return '';
    }

    $raw = str_replace(["\t", "\r", "\n"], '', $raw);
    if (str_starts_with($raw, '00')) {
        $raw = '+' . substr($raw, 2);
    }

    $hasPlus = str_starts_with($raw, '+');
    $digits = preg_replace('/\D+/', '', $raw);
    if (!is_string($digits) || $digits === '') {
        return '';
    }

    return $hasPlus ? ('+' . $digits) : $digits;
}

/**
 * @return string[]
 */
function cityzen_phone_lookup_keys(string $value): array
{
    $normalized = cityzen_normalize_phone_input($value);
    if ($normalized === '') {
        return [];
    }

    $digits = ltrim($normalized, '+');
    $keys = [$digits];

    // FR: 06.. <-> +336.. compatibility
    if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
        $keys[] = '33' . substr($digits, 1);
    }
    if (strlen($digits) === 11 && str_starts_with($digits, '33')) {
        $keys[] = '0' . substr($digits, 2);
    }

    return array_values(array_unique($keys));
}

function cityzen_find_user_by_email_or_phone(string $identifier): array
{
    try {
        $pdo = cityzen_db();
        $identifier = trim($identifier);
        if ($identifier === '') {
            return ['ok' => false, 'error' => 'Identifiant vide.'];
        }
        
        $stmt = $pdo->prepare('
            SELECT id, username, email, phone, full_name 
            FROM users 
            WHERE email = ? OR username = ?
            LIMIT 1
        ');
        
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        
        if ($user) {
            return ['ok' => true, 'user' => $user];
        }

        $wantedKeys = cityzen_phone_lookup_keys($identifier);
        if ($wantedKeys !== []) {
            $usersStmt = $pdo->query('
                SELECT id, username, email, phone, full_name
                FROM users
                WHERE phone IS NOT NULL AND phone <> ""
            ');

            foreach ($usersStmt as $row) {
                $rowPhone = (string) ($row['phone'] ?? '');
                $rowKeys = cityzen_phone_lookup_keys($rowPhone);
                if ($rowKeys === []) {
                    continue;
                }

                if (array_intersect($wantedKeys, $rowKeys) !== []) {
                    return ['ok' => true, 'user' => $row];
                }
            }
        }
        
        return ['ok' => false, 'error' => 'Aucun utilisateur trouvé avec cet identifiant.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()];
    }
}

function cityzen_update_user_password(int $userId, string $newPassword): array
{
    try {
        $pdo = cityzen_db();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        
        if ($stmt->execute([$hash, $userId])) {
            return ['ok' => true];
        }
        
        return ['ok' => false, 'error' => 'Erreur lors de la mise à jour du mot de passe.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()];
    }
}

// Fonctions pour la gestion des codes 2FA
function cityzen_ensure_2fa_table(PDO $pdo): void
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS two_factor_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            qr_token VARCHAR(32) NOT NULL UNIQUE,
            code VARCHAR(6) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            session_id VARCHAR(255) NULL,
            INDEX idx_qr_token (qr_token),
            INDEX idx_code (code),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ');
}

function cityzen_generate_2fa_code(string $qrToken): array
{
    try {
        $pdo = cityzen_db();
        
        // S'assurer que la table existe
        cityzen_ensure_2fa_table($pdo);
        
        // Nettoyer les anciens codes expirés
        $stmt = $pdo->prepare('DELETE FROM two_factor_codes WHERE expires_at < NOW()');
        $stmt->execute();
        
        // Vérifier si un code existe déjà pour ce qr_token
        $stmt = $pdo->prepare('
            SELECT code, expires_at FROM two_factor_codes 
            WHERE qr_token = ? AND used_at IS NULL AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ');
        $stmt->execute([$qrToken]);
        $existingCode = $stmt->fetch();
        
        if ($existingCode) {
            // Un code valide existe déjà, le retourner
            return ['ok' => true, 'code' => $existingCode['code']];
        }
        
        // Générer un nouveau code à 6 chiffres
        $code = sprintf('%06d', random_int(0, 999999));
        
        // Insérer le nouveau code
        $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 minutes
        $stmt = $pdo->prepare('
            INSERT INTO two_factor_codes (qr_token, code, expires_at, session_id) 
            VALUES (?, ?, ?, ?)
        ');
        
        $sessionId = session_id();
        $stmt->execute([$qrToken, $code, $expiresAt, $sessionId]);
        
        return ['ok' => true, 'code' => $code];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur lors de la génération du code 2FA: ' . $e->getMessage()];
    }
}

function cityzen_verify_2fa_code(string $qrToken, string $code): array
{
    try {
        $pdo = cityzen_db();
        
        $stmt = $pdo->prepare('
            SELECT id, qr_token, code, expires_at, used_at, session_id 
            FROM two_factor_codes 
            WHERE qr_token = ? AND code = ? AND used_at IS NULL AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ');
        $stmt->execute([$qrToken, $code]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return ['ok' => false, 'error' => 'Code 2FA invalide ou expiré.'];
        }
        
        // Marquer le code comme utilisé
        $stmt = $pdo->prepare('UPDATE two_factor_codes SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$result['id']]);
        
        return ['ok' => true, 'verified' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur lors de la vérification du code 2FA: ' . $e->getMessage()];
    }
}

function cityzen_get_2fa_code(string $qrToken): array
{
    try {
        $pdo = cityzen_db();
        
        $stmt = $pdo->prepare('
            SELECT code, created_at, expires_at, used_at 
            FROM two_factor_codes 
            WHERE qr_token = ? AND used_at IS NULL AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ');
        $stmt->execute([$qrToken]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return ['ok' => false, 'error' => 'Aucun code 2FA valide trouvé.'];
        }
        
        return ['ok' => true, 'code' => $result['code'], 'expires_at' => $result['expires_at']];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur lors de la récupération du code 2FA: ' . $e->getMessage()];
    }
}
