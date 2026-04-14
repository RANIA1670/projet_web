<?php
/**
 * Contrôleur des Posts
 * Gestion des actions liées aux posts
 */

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Reply.php';

class PostController {
    private $postModel;
    private $replyModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->replyModel = new Reply();
    }

    // Afficher la liste des posts (front office)
    public function index() {
        $posts = $this->postModel->getAll('Publié');
        require_once __DIR__ . '/../views/front/posts.php';
    }

    // Afficher un post avec ses replies (front office)
    public function show($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            header('Location: /index.php?action=404');
            exit;
        }

        $this->postModel->incrementViews($id);
        $replies = $this->replyModel->getByPostId($id);

        require_once __DIR__ . '/../views/front/post_detail.php';
    }

    // Afficher le formulaire de création (front office)
    public function create() {
        require_once __DIR__ . '/../views/front/post_create.php';
    }

    // Traiter la création d'un post (front office)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => 'En révision' // Les posts soumis sont en révision
        ];

        if (empty($data['title']) || empty($data['author'])) {
            $_SESSION['error'] = 'Titre et auteur requis';
            header('Location: /index.php?action=create');
            exit;
        }

        $this->postModel->create($data);
        $_SESSION['success'] = 'Post soumis avec succès ! Il sera publié après validation.';
        header('Location: /index.php');
        exit;
    }

    // Administration - Liste des posts
    public function adminIndex() {
        $posts = $this->postModel->getAll();
        $stats = $this->postModel->getStats();
        require_once __DIR__ . '/../views/admin/posts.php';
    }

    // Administration - Créer un post
    public function adminCreate() {
        require_once __DIR__ . '/../views/admin/post_form.php';
    }

    // Administration - Stocker un post
    public function adminStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin.php?action=posts');
            exit;
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => $_POST['status'] ?? 'Brouillon'
        ];

        if (empty($data['title']) || empty($data['author'])) {
            $_SESSION['error'] = 'Titre et auteur requis';
            header('Location: /admin.php?action=post_create');
            exit;
        }

        $this->postModel->create($data);
        $_SESSION['success'] = 'Post créé avec succès !';
        header('Location: /admin.php?action=posts');
        exit;
    }

    // Administration - Éditer un post
    public function adminEdit($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            header('Location: /admin.php?action=posts');
            exit;
        }
        require_once __DIR__ . '/../views/admin/post_form.php';
    }

    // Administration - Mettre à jour un post
    public function adminUpdate($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin.php?action=posts');
            exit;
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'status' => $_POST['status'] ?? 'Brouillon'
        ];

        if (empty($data['title']) || empty($data['author'])) {
            $_SESSION['error'] = 'Titre et auteur requis';
            header('Location: /admin.php?action=post_edit&id=' . $id);
            exit;
        }

        $this->postModel->update($id, $data);
        $_SESSION['success'] = 'Post mis à jour avec succès !';
        header('Location: /admin.php?action=posts');
        exit;
    }

    // Administration - Supprimer un post
    public function adminDelete($id) {
        $this->postModel->delete($id);
        $_SESSION['success'] = 'Post supprimé avec succès !';
        header('Location: /admin.php?action=posts');
        exit;
    }
}
?>