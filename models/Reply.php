<?php
require_once __DIR__ . '/../config/Database.php';

class Reply {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT r.*, p.title as post_title
            FROM replies r
            LEFT JOIN posts p ON r.post_id = p.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, p.title as post_title
            FROM replies r
            LEFT JOIN posts p ON r.post_id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByPostId($postId) {
        $stmt = $this->db->prepare("
            SELECT r.*, p.title as post_title
            FROM replies r
            LEFT JOIN posts p ON r.post_id = p.id
            WHERE r.post_id = ? AND r.status = 'approved'
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO replies (post_id, content, author, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $data['post_id'],
            $data['content'],
            $data['author'],
            $data['status'] ?? 'pending'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE replies
            SET post_id = ?, content = ?, author = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['post_id'],
            $data['content'],
            $data['author'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM replies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPending() {
        $stmt = $this->db->prepare("
            SELECT r.*, p.title as post_title
            FROM replies r
            LEFT JOIN posts p ON r.post_id = p.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approve($id) {
        $stmt = $this->db->prepare("UPDATE replies SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function reject($id) {
        $stmt = $this->db->prepare("UPDATE replies SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>