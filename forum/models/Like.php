<?php
/**
 * Classe Like - Modèle pour les likes du forum
 */

require_once __DIR__ . '/../config/Database.php';

class Like
{
    private int $id;
    private int $postId;
    private int $replyId;
    private int $userId;
    private string $createdAt;
    private ?PDO $db;

    /**
     * Constructeur
     * 
     * @param int $postId ID du post (0 si c'est un like de réponse)
     * @param int $replyId ID de la réponse (0 si c'est un like de post)
     * @param int $userId ID de l'utilisateur
     */
    public function __construct(
        int $postId = 0,
        int $replyId = 0,
        int $userId = 0
    ) {
        $this->postId = $postId;
        $this->replyId = $replyId;
        $this->userId = $userId;
        $this->createdAt = date('Y-m-d H:i:s');
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
     * Récupère l'ID du like
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
     * Récupère l'ID de la réponse
     * @return int
     */
    public function getReplyId(): int
    {
        return $this->replyId;
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
     * Récupère la date de création
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    // ====== SETTERS ======

    /**
     * Définit l'ID du like
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
     * Définit l'ID de la réponse
     * @param int $replyId
     */
    public function setReplyId(int $replyId): void
    {
        $this->replyId = $replyId;
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
     * Définit la date de création
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    // ====== MÉTHODES MÉTIER ======

    /**
     * Ajoute un like
     * 
     * @return bool
     */
    public function save(): bool
    {
        try {
            // Vérifier si le like existe déjà
            if ($this->exists()) {
                return false; // Le like existe déjà
            }

            $sql = "INSERT INTO likes (post_id, reply_id, user_id, created_at)
                    VALUES (:post_id, :reply_id, :user_id, :created_at)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':post_id' => $this->postId > 0 ? $this->postId : null,
                ':reply_id' => $this->replyId > 0 ? $this->replyId : null,
                ':user_id' => $this->userId,
                ':created_at' => $this->createdAt,
            ]);

            if ($result) {
                $this->id = (int) $this->db->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'ajout du like : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un like
     * 
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM likes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du like : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un like existe déjà
     * 
     * @return bool
     */
    public function exists(): bool
    {
        try {
            if ($this->postId <= 0 && $this->replyId <= 0) {
                return false;
            }

            if ($this->postId > 0) {
                $sql = "SELECT COUNT(*) as count FROM likes 
                        WHERE post_id = :post_id AND user_id = :user_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':post_id' => $this->postId, ':user_id' => $this->userId]);
            } elseif ($this->replyId > 0) {
                $sql = "SELECT COUNT(*) as count FROM likes 
                        WHERE reply_id = :reply_id AND user_id = :user_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':reply_id' => $this->replyId, ':user_id' => $this->userId]);
            }

            $result = $stmt->fetch();
            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification du like : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie l'existence d'un like sur un post par utilisateur.
     */
    public static function existsByPostIdAndUserId(int $postId, int $userId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':post_id' => $postId,
                ':user_id' => $userId
            ]);

            $result = $stmt->fetch();
            return ((int)($result['count'] ?? 0)) > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification du like post : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie l'existence d'un like sur une réponse par utilisateur.
     */
    public static function existsByReplyIdAndUserId(int $replyId, int $userId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as count FROM likes WHERE reply_id = :reply_id AND user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':reply_id' => $replyId,
                ':user_id' => $userId
            ]);

            $result = $stmt->fetch();
            return ((int)($result['count'] ?? 0)) > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification du like réponse : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un like par son ID
     * 
     * @param int $id
     * @return Like|null
     */
    public static function findById(int $id): ?Like
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM likes WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }

            $like = new self(
                $data['post_id'] ?? 0,
                $data['reply_id'] ?? 0,
                $data['user_id']
            );
            $like->id = $data['id'];
            $like->createdAt = $data['created_at'];

            return $like;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du like : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les likes d'un post
     * 
     * @param int $postId
     * @return array
     */
    public static function findByPostId(int $postId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM likes WHERE post_id = :post_id ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
            
            $likes = [];
            while ($data = $stmt->fetch()) {
                $like = new self($data['post_id'], $data['reply_id'], $data['user_id']);
                $like->id = $data['id'];
                $like->createdAt = $data['created_at'];
                $likes[] = $like;
            }

            return $likes;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des likes : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les likes d'une réponse
     * 
     * @param int $replyId
     * @return array
     */
    public static function findByReplyId(int $replyId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM likes WHERE reply_id = :reply_id ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':reply_id' => $replyId]);
            
            $likes = [];
            while ($data = $stmt->fetch()) {
                $like = new self($data['post_id'], $data['reply_id'], $data['user_id']);
                $like->id = $data['id'];
                $like->createdAt = $data['created_at'];
                $likes[] = $like;
            }

            return $likes;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des likes : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre de likes pour un post
     * 
     * @param int $postId
     * @return int
     */
    public static function countByPostId(int $postId): int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
            
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('Erreur lors du comptage des likes : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compte le nombre de likes pour une réponse
     * 
     * @param int $replyId
     * @return int
     */
    public static function countByReplyId(int $replyId): int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as count FROM likes WHERE reply_id = :reply_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':reply_id' => $replyId]);
            
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('Erreur lors du comptage des likes : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Supprime un like par post_id et user_id
     * 
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public static function deleteByPostIdAndUserId(int $postId, int $userId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du like : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un like par reply_id et user_id
     * 
     * @param int $replyId
     * @param int $userId
     * @return bool
     */
    public static function deleteByReplyIdAndUserId(int $replyId, int $userId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "DELETE FROM likes WHERE reply_id = :reply_id AND user_id = :user_id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([':reply_id' => $replyId, ':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du like : ' . $e->getMessage());
            return false;
        }
    }
}
