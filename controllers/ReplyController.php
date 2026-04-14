<?php
/**
 * Contrôleur des Replies
 * Gestion des actions liées aux replies
 */

require_once __DIR__ . '/../models/Reply.php';
require_once __DIR__ . '/../models/Post.php';

class ReplyController {
    private $replyModel;
    private $postModel;

    public function __construct() {
        $this->replyModel = new Reply();
        $this->postModel = new Post();
    }

    // Traiter la création d'une reply (front office)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }

        $data = [
            'post_id' => (int)($_POST['post_id'] ?? 0),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => 'En attente' // Les replies soumises sont en attente
        ];

        if (empty($data['content']) || empty($data['author']) || !$data['post_id']) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: /index.php?action=show&id=' . $data['post_id']);
            exit;
        }

        $this->replyModel->create($data);
        $_SESSION['success'] = 'Réponse soumise avec succès ! Elle sera publiée après validation.';
        header('Location: /index.php?action=show&id=' . $data['post_id']);
        exit;
    }

    // Administration - Liste des replies
    public function adminIndex() {
        $replies = $this->replyModel->getAll();
        $stats = $this->replyModel->getStats();
        require_once __DIR__ . '/../views/admin/replies.php';
    }

    // Administration - Créer une reply
    public function adminCreate() {
        $posts = $this->postModel->getAll();
        require_once __DIR__ . '/../views/admin/reply_form.php';
    }

    // Administration - Stocker une reply
    public function adminStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin.php?action=replies');
            exit;
        }

        $data = [
            'post_id' => (int)($_POST['post_id'] ?? 0),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => $_POST['status'] ?? 'En attente'
        ];

        if (empty($data['content']) || empty($data['author']) || !$data['post_id']) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: /admin.php?action=reply_create');
            exit;
        }

        $this->replyModel->create($data);
        $_SESSION['success'] = 'Reply créée avec succès !';
        header('Location: /admin.php?action=replies');
        exit;
    }

    // Administration - Éditer une reply
    public function adminEdit($id) {
        $reply = $this->replyModel->getById($id);
        $posts = $this->postModel->getAll();
        if (!$reply) {
            header('Location: /admin.php?action=replies');
            exit;
        }
        require_once __DIR__ . '/../views/admin/reply_form.php';
    }

    // Administration - Mettre à jour une reply
    public function adminUpdate($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin.php?action=replies');
            exit;
        }

        $data = [
            'post_id' => (int)($_POST['post_id'] ?? 0),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => $_POST['status'] ?? 'En attente'
        ];

        if (empty($data['content']) || empty($data['author']) || !$data['post_id']) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: /admin.php?action=reply_edit&id=' . $id);
            exit;
        }

        $this->replyModel->update($id, $data);
        $_SESSION['success'] = 'Reply mise à jour avec succès !';
        header('Location: /admin.php?action=replies');
        exit;
    }

    // Administration - Supprimer une reply
    public function adminDelete($id) {
        $this->replyModel->delete($id);
        $_SESSION['success'] = 'Reply supprimée avec succès !';
        header('Location: /admin.php?action=replies');
        exit;
    }
}
?>