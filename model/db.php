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
    $name = getenv('CITYZEN_DB_NAME') ?: (getenv('DB_NAME') ?: 'cityzen');
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

function cityzen_db_table_exists(PDO $pdo, string $table): bool
{
    $name = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
    );
    $stmt->execute([$name, $table]);

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

    if (cityzen_db_table_exists($pdo, 'equipment')) {
        if (!cityzen_db_column_exists($pdo, 'equipment', 'price_per_day')) {
            $pdo->exec('ALTER TABLE equipment ADD COLUMN price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER type_id');
        }
        $pdo->exec(
            'UPDATE equipment e
             INNER JOIN type_equipment t ON t.id = e.type_id
             SET e.price_per_day = t.daily_cost
             WHERE e.price_per_day IS NULL OR e.price_per_day <= 0'
        );
    }

    if (cityzen_db_table_exists($pdo, 'reservation')) {
        if (!cityzen_db_column_exists($pdo, 'reservation', 'price_days')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN price_days INT UNSIGNED NOT NULL DEFAULT 1 AFTER end_date');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'price_per_day')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price_days');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'price_subtotal')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN price_subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price_per_day');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'discount_code')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN discount_code VARCHAR(64) NULL AFTER price_subtotal');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'discount_percent')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN discount_percent TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER discount_code');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'discount_amount')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER discount_percent');
        }
        if (!cityzen_db_column_exists($pdo, 'reservation', 'price_total')) {
            $pdo->exec('ALTER TABLE reservation ADD COLUMN price_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER discount_amount');
        }
    }

    if (!cityzen_db_table_exists($pdo, 'equipment_discount_code')) {
        $pdo->exec(
            "CREATE TABLE equipment_discount_code (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(64) NOT NULL,
                user_id INT UNSIGNED NULL,
                discount_percent TINYINT UNSIGNED NOT NULL,
                status ENUM('active','used','expired') NOT NULL DEFAULT 'active',
                generated_from ENUM('lucky_spin','manual') NOT NULL DEFAULT 'lucky_spin',
                valid_from DATETIME NOT NULL,
                valid_until DATETIME NOT NULL,
                used_at DATETIME NULL,
                used_reservation_id INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_equipment_discount_code (code),
                KEY idx_equipment_discount_user_status (user_id, status),
                KEY idx_equipment_discount_validity (valid_from, valid_until),
                CONSTRAINT fk_equipment_discount_user
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    if (!cityzen_db_table_exists($pdo, 'equipment_lucky_spin')) {
        $pdo->exec(
            "CREATE TABLE equipment_lucky_spin (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                spin_date DATE NOT NULL,
                outcome ENUM('no_win','discount') NOT NULL DEFAULT 'no_win',
                discount_code_id INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lucky_spin_user_day (user_id, spin_date),
                KEY idx_lucky_spin_date (spin_date),
                CONSTRAINT fk_lucky_spin_user
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_lucky_spin_code
                    FOREIGN KEY (discount_code_id) REFERENCES equipment_discount_code(id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    // ─── Module événements (schéma branche ibrahim : tables `sponsor`, `event`, `participation`, `avis`) ───
    if (!cityzen_db_table_exists($pdo, 'sponsor')) {
        $pdo->exec(
            'CREATE TABLE sponsor (
                id_sponsor INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(150) NOT NULL,
                email VARCHAR(190) NOT NULL,
                telephone VARCHAR(50) NOT NULL DEFAULT \'\',
                KEY idx_sponsor_nom (nom)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    if (!cityzen_db_table_exists($pdo, 'event')) {
        $pdo->exec(
            'CREATE TABLE `event` (
                id_event INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(200) NOT NULL,
                description TEXT NULL,
                date_event DATE NOT NULL,
                lieu VARCHAR(255) NOT NULL,
                id_sponsor INT UNSIGNED NOT NULL,
                KEY idx_event_date (date_event),
                KEY idx_event_sponsor (id_sponsor),
                CONSTRAINT fk_cz_ev_event_sponsor
                    FOREIGN KEY (id_sponsor) REFERENCES sponsor (id_sponsor)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    if (!cityzen_db_table_exists($pdo, 'participation')) {
        $pdo->exec(
            'CREATE TABLE participation (
                id_participation INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                nom_participant VARCHAR(120) NOT NULL,
                email_participant VARCHAR(190) NOT NULL,
                numero_participant VARCHAR(40) NOT NULL DEFAULT \'\',
                age_participant INT UNSIGNED NOT NULL DEFAULT 0,
                sexe_participant VARCHAR(20) NOT NULL DEFAULT \'\',
                id_event INT UNSIGNED NOT NULL,
                KEY idx_participation_event (id_event),
                KEY idx_participation_email (email_participant),
                CONSTRAINT fk_cz_ev_participation_event
                    FOREIGN KEY (id_event) REFERENCES `event` (id_event)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    if (!cityzen_db_table_exists($pdo, 'avis')) {
        $pdo->exec(
            'CREATE TABLE avis (
                id_avis INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_event INT UNSIGNED NOT NULL,
                id_participation INT UNSIGNED NOT NULL,
                note TINYINT UNSIGNED NOT NULL DEFAULT 5,
                commentaire TEXT NULL,
                date_avis DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                approuve TINYINT(1) NOT NULL DEFAULT 0,
                UNIQUE KEY uq_avis_event_participation (id_event, id_participation),
                CONSTRAINT fk_cz_ev_avis_event
                    FOREIGN KEY (id_event) REFERENCES `event` (id_event)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_cz_ev_avis_participation
                    FOREIGN KEY (id_participation) REFERENCES participation (id_participation)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    if (
        cityzen_db_table_exists($pdo, 'sponsor')
        && (int) $pdo->query('SELECT COUNT(*) FROM sponsor')->fetchColumn() === 0
    ) {
        try {
            $pdo->exec(
                "INSERT INTO sponsor (nom, email, telephone) VALUES ('Sponsor démo', 'sponsor-demo@cityzen.local', '')"
            );
        } catch (PDOException) {
            /* ignore si concurrence ou droits */
        }
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
