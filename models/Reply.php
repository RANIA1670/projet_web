<?php
/**
 * Classe Reply - Modèle pour les réponses du forum
 */

require_once __DIR__ . '/../config/Database.php';

class Reply
{
    private int $id;
    private int $postId;
    private int $userId;
    private string $content;
    private string $createdAt;
    private string $updatedAt;
    private ?PDO $db;

    /**
     * Constructeur
     * 
     * @param int $postId ID du post
     * @param int $userId ID de l'utilisateur
     * @param string $content Contenu de la réponse
     */
    public function __construct(
        int $postId = 0,
        int $userId = 0,
        string $content = ''
    ) {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->content = $content;
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
     * Récupère l'ID de la réponse
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * Récupère l'ID du post
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
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

    // ====== SETTERS ======

    /**
     * Définit l'ID de la réponse
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Définit l'ID du post
     * @param int $postId
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
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

    // ====== MÉTHODES MÉTIER ======

    /**
     * Sauvegarde la réponse dans la base de données
     * 
     * @return bool
     */
    public function save(): bool
    {
        try {
            $sql = "INSERT INTO replies (post_id, user_id, content, created_at, updated_at)
                    VALUES (:post_id, :user_id, :content, :created_at, :updated_at)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':post_id' => $this->postId,
                ':user_id' => $this->userId,
                ':content' => $this->content,
                ':created_at' => $this->createdAt,
                ':updated_at' => $this->updatedAt,
            ]);

            if ($result) {
                $this->id = (int) $this->db->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de la sauvegarde de la réponse : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour la réponse
     * 
     * @return bool
     */
    public function update(): bool
    {
        try {
            $this->updatedAt = date('Y-m-d H:i:s');
            
            $sql = "UPDATE replies 
                    SET post_id = :post_id, user_id = :user_id, content = :content, updated_at = :updated_at
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':post_id' => $this->postId,
                ':user_id' => $this->userId,
                ':content' => $this->content,
                ':updated_at' => $this->updatedAt,
                ':id' => $this->id,
            ]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour de la réponse : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime la réponse
     * 
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM replies WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression de la réponse : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une réponse par son ID
     * 
     * @param int $id
     * @return Reply|null
     */
    public static function findById(int $id): ?Reply
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM replies WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }

            $reply = new self($data['post_id'], $data['user_id'], $data['content']);
            $reply->id = $data['id'];
            $reply->createdAt = $data['created_at'];
            $reply->updatedAt = $data['updated_at'];

            return $reply;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de la réponse : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les réponses d'un post
     * 
     * @param int $postId
     * @return array
     */
    public static function findByPostId(int $postId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM replies WHERE post_id = :post_id ORDER BY created_at ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
            
            $replies = [];
            while ($data = $stmt->fetch()) {
                $reply = new self($data['post_id'], $data['user_id'], $data['content']);
                $reply->id = $data['id'];
                $reply->createdAt = $data['created_at'];
                $reply->updatedAt = $data['updated_at'];
                $replies[] = $reply;
            }

            return $replies;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des réponses : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les réponses d'un utilisateur
     * 
     * @param int $userId
     * @return array
     */
    public static function findByUserId(int $userId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM replies WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $replies = [];
            while ($data = $stmt->fetch()) {
                $reply = new self($data['post_id'], $data['user_id'], $data['content']);
                $reply->id = $data['id'];
                $reply->createdAt = $data['created_at'];
                $reply->updatedAt = $data['updated_at'];
                $replies[] = $reply;
            }

            return $replies;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des réponses de l\'utilisateur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le nombre de réponses pour un post
     * 
     * @param int $postId
     * @return int
     */
    public static function countByPostId(int $postId): int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as count FROM replies WHERE post_id = :post_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
            
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('Erreur lors du comptage des réponses : ' . $e->getMessage());
            return 0;
        }
    }
}
