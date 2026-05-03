<?php
/**
 * Classe Post - Modèle pour les publications du forum
 */

require_once __DIR__ . '/../config/Database.php';

class Post
{
    private int $id;
    private int $userId;
    private string $title;
    private string $content;
    private string $createdAt;
    private string $updatedAt;
    private int $viewCount;
    private ?PDO $db;

    /**
     * Constructeur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $title Titre du post
     * @param string $content Contenu du post
     */
    public function __construct(
        int $userId = 0,
        string $title = '',
        string $content = ''
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->viewCount = 0;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Destructeur
     */
    public function __destruct()
    {
        $this->db = null;
    }

    // ====== GETTERS ======

    /**
     * Récupère l'ID du post
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * Récupère l'ID de l'utilisateur
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Récupère le titre
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Récupère le contenu
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Récupère la date de création
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Récupère la date de modification
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Récupère le nombre de vues
     * @return int
     */
    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    // ====== SETTERS ======

    /**
     * Définit l'ID du post
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Définit l'ID de l'utilisateur
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Définit le titre
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    /**
     * Définit le contenu
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = trim($content);
    }

    /**
     * Définit la date de création
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Définit la date de modification
     * @param string $updatedAt
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Définit le nombre de vues
     * @param int $viewCount
     */
    public function setViewCount(int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    // ====== MÉTHODES MÉTIER ======

    /**
     * Sauvegarde le post dans la base de données
     * 
     * @return bool
     */
    public function save(): bool
    {
        try {
            $sql = "INSERT INTO posts (user_id, title, content, created_at, updated_at, view_count)
                    VALUES (:user_id, :title, :content, :created_at, :updated_at, :view_count)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':user_id' => $this->userId,
                ':title' => $this->title,
                ':content' => $this->content,
                ':created_at' => $this->createdAt,
                ':updated_at' => $this->updatedAt,
                ':view_count' => $this->viewCount,
            ]);

            if ($result) {
                $this->id = (int) $this->db->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de la sauvegarde du post : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le post
     * 
     * @return bool
     */
    public function update(): bool
    {
        try {
            $this->updatedAt = date('Y-m-d H:i:s');
            
            $sql = "UPDATE posts 
                    SET user_id = :user_id, title = :title, content = :content, 
                        updated_at = :updated_at, view_count = :view_count
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $this->userId,
                ':title' => $this->title,
                ':content' => $this->content,
                ':updated_at' => $this->updatedAt,
                ':view_count' => $this->viewCount,
                ':id' => $this->id,
            ]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du post : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime le post
     * 
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM posts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du post : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un post par son ID
     * 
     * @param int $id
     * @return Post|null
     */
    public static function findById(int $id): ?Post
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM posts WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }

            $post = new self($data['user_id'], $data['title'], $data['content']);
            $post->id = $data['id'];
            $post->createdAt = $data['created_at'];
            $post->updatedAt = $data['updated_at'];
            $post->viewCount = $data['view_count'];

            return $post;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du post : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère tous les posts
     * 
     * @return array
     */
    public static function findAll(): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM posts ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $posts = [];
            while ($data = $stmt->fetch()) {
                $post = new self($data['user_id'], $data['title'], $data['content']);
                $post->id = $data['id'];
                $post->createdAt = $data['created_at'];
                $post->updatedAt = $data['updated_at'];
                $post->viewCount = $data['view_count'];
                $posts[] = $post;
            }

            return $posts;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des posts : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Incrémente le nombre de vues
     * 
     * @return bool
     */
    public function incrementViewCount(): bool
    {
        try {
            $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $this->id]);
            
            if ($result) {
                $this->viewCount++;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'incrémentation des vues : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les posts par l'ID d'un utilisateur
     * 
     * @param int $userId
     * @return array
     */
    public static function findByUserId(int $userId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $posts = [];
            while ($data = $stmt->fetch()) {
                $post = new self($data['user_id'], $data['title'], $data['content']);
                $post->id = $data['id'];
                $post->createdAt = $data['created_at'];
                $post->updatedAt = $data['updated_at'];
                $post->viewCount = $data['view_count'];
                $posts[] = $post;
            }

            return $posts;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des posts de l\'utilisateur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Recherche des posts par mot-clé dans le titre ou le contenu
     *
     * @param string $keyword Mot-clé à rechercher
     * @param string $sortBy  Colonne de tri : 'created_at' | 'view_count' | 'title'
     * @param string $order   'DESC' | 'ASC'
     * @return array
     */
    public static function search(string $keyword, string $sortBy = 'created_at', string $order = 'DESC'): array
    {
        $allowedSort  = ['created_at', 'view_count', 'title'];
        $allowedOrder = ['ASC', 'DESC'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'created_at';
        $order  = in_array(strtoupper($order), $allowedOrder) ? strtoupper($order) : 'DESC';

        try {
            $db  = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM posts
                    WHERE title LIKE :kw OR content LIKE :kw
                    ORDER BY {$sortBy} {$order}";
            $stmt = $db->prepare($sql);
            $stmt->execute([':kw' => '%' . $keyword . '%']);

            $posts = [];
            while ($data = $stmt->fetch()) {
                $post = new self($data['user_id'], $data['title'], $data['content']);
                $post->id        = $data['id'];
                $post->createdAt = $data['created_at'];
                $post->updatedAt = $data['updated_at'];
                $post->viewCount = $data['view_count'];
                $posts[] = $post;
            }
            return $posts;
        } catch (PDOException $e) {
            error_log('Erreur recherche posts : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Filtre les posts par plage de dates et/ou tri
     *
     * @param string|null $dateFrom  Date de début (Y-m-d)
     * @param string|null $dateTo    Date de fin  (Y-m-d)
     * @param string      $sortBy    Colonne de tri
     * @param string      $order     Direction
     * @return array
     */
    public static function findByDateRange(
        ?string $dateFrom,
        ?string $dateTo,
        string $sortBy = 'created_at',
        string $order  = 'DESC'
    ): array {
        $allowedSort  = ['created_at', 'view_count', 'title'];
        $allowedOrder = ['ASC', 'DESC'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'created_at';
        $order  = in_array(strtoupper($order), $allowedOrder) ? strtoupper($order) : 'DESC';

        try {
            $db     = Database::getInstance()->getConnection();
            $where  = [];
            $params = [];

            if ($dateFrom) {
                $where[]          = 'created_at >= :date_from';
                $params[':date_from'] = $dateFrom . ' 00:00:00';
            }
            if ($dateTo) {
                $where[]        = 'created_at <= :date_to';
                $params[':date_to'] = $dateTo . ' 23:59:59';
            }

            $sql  = 'SELECT * FROM posts';
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= " ORDER BY {$sortBy} {$order}";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $posts = [];
            while ($data = $stmt->fetch()) {
                $post = new self($data['user_id'], $data['title'], $data['content']);
                $post->id        = $data['id'];
                $post->createdAt = $data['created_at'];
                $post->updatedAt = $data['updated_at'];
                $post->viewCount = $data['view_count'];
                $posts[] = $post;
            }
            return $posts;
        } catch (PDOException $e) {
            error_log('Erreur filtre plage de dates : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Statistiques agrégées du forum (counts par mois, top posts, etc.)
     *
     * @return array  ['monthly' => [...], 'topViewed' => [...], 'topLiked' => [...]]
     */
    public static function getAggregatedStats(): array
    {
        try {
            $db = Database::getInstance()->getConnection();

            // Posts par mois (12 derniers mois)
            $sqlMonthly = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                                  COUNT(*) AS post_count
                           FROM posts
                           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                           GROUP BY month
                           ORDER BY month ASC";
            $stmtM = $db->prepare($sqlMonthly);
            $stmtM->execute();
            $monthly = $stmtM->fetchAll();

            // Top 5 posts les plus vus
            $sqlTopViewed = "SELECT id, title, view_count FROM posts ORDER BY view_count DESC LIMIT 5";
            $stmtTV = $db->prepare($sqlTopViewed);
            $stmtTV->execute();
            $topViewed = $stmtTV->fetchAll();

            // Top 5 posts les plus likés
            $sqlTopLiked = "SELECT p.id, p.title, COUNT(l.id) AS like_count
                            FROM posts p
                            LEFT JOIN likes l ON l.post_id = p.id
                            GROUP BY p.id
                            ORDER BY like_count DESC
                            LIMIT 5";
            $stmtTL = $db->prepare($sqlTopLiked);
            $stmtTL->execute();
            $topLiked = $stmtTL->fetchAll();

            // Total global
            $totalPosts   = (int)$db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
            $totalReplies = (int)$db->query('SELECT COUNT(*) FROM replies')->fetchColumn();
            $totalLikes   = (int)$db->query('SELECT COUNT(*) FROM likes')->fetchColumn();
            $totalViews   = (int)($db->query('SELECT COALESCE(SUM(view_count),0) FROM posts')->fetchColumn());

            return compact('monthly', 'topViewed', 'topLiked', 'totalPosts', 'totalReplies', 'totalLikes', 'totalViews');
        } catch (PDOException $e) {
            error_log('Erreur stats agrégées : ' . $e->getMessage());
            return [
                'monthly'      => [],
                'topViewed'    => [],
                'topLiked'     => [],
                'totalPosts'   => 0,
                'totalReplies' => 0,
                'totalLikes'   => 0,
                'totalViews'   => 0,
            ];
        }
    }
}
