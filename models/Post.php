<?php
require_once __DIR__ . '/../config/Database.php';

class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM posts ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO posts (title, content, author, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $data['title'],
            $data['content'] ?? '',
            $data['author'],
            $data['status'] ?? 'draft'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE posts
            SET title = ?, content = ?, author = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['content'] ?? '',
            $data['author'],
            $data['status'] ?? 'draft',
            $id
        ]);
    }

    public function delete($id) {
        // Supprimer d'abord les replies associées
        $stmt = $this->db->prepare("DELETE FROM replies WHERE post_id = ?");
        $stmt->execute([$id]);

        // Puis supprimer le post
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getRecent($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM posts
            WHERE status = 'published'
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getStats() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM posts");
        $total = $stmt->fetch()['total'];

        $stmt = $this->db->query("
            SELECT COUNT(*) as recent FROM posts
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $recent = $stmt->fetch()['recent'];

        return ['total' => $total, 'recent' => $recent];
    }
}
?>