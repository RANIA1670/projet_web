<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Reply.php';

class BackController {
    private $postModel;
    private $replyModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->replyModel = new Reply();
    }

    public function dashboard() {
        $posts = $this->postModel->getAll();
        $replies = $this->replyModel->getAll();
        $stats = $this->postModel->getStats();
        $pendingReplies = $this->replyModel->getPending();

        require_once __DIR__ . '/../views/back/dashboard.php';
    }

    // Gestion des posts
    public function posts() {
        $posts = $this->postModel->getAll();
        require_once __DIR__ . '/../views/back/posts.php';
    }

    public function createPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'author' => $_POST['author'],
                'status' => $_POST['status']
            ];

            $this->postModel->create($data);
            header('Location: /admin/posts');
            exit;
        }
        require_once __DIR__ . '/../views/back/post_form.php';
    }

    public function editPost($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            header('Location: /admin/posts');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'author' => $_POST['author'],
                'status' => $_POST['status']
            ];

            $this->postModel->update($id, $data);
            header('Location: /admin/posts');
            exit;
        }

        require_once __DIR__ . '/../views/back/post_form.php';
    }

    public function deletePost($id) {
        $this->postModel->delete($id);
        header('Location: /admin/posts');
        exit;
    }

    // Gestion des replies
    public function replies() {
        $replies = $this->replyModel->getAll();
        require_once __DIR__ . '/../views/back/replies.php';
    }

    public function createReply() {
        $posts = $this->postModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'post_id' => $_POST['post_id'],
                'content' => $_POST['content'],
                'author' => $_POST['author'],
                'status' => $_POST['status']
            ];

            $this->replyModel->create($data);
            header('Location: /admin/replies');
            exit;
        }

        require_once __DIR__ . '/../views/back/reply_form.php';
    }

    public function editReply($id) {
        $reply = $this->replyModel->getById($id);
        $posts = $this->postModel->getAll();

        if (!$reply) {
            header('Location: /admin/replies');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'post_id' => $_POST['post_id'],
                'content' => $_POST['content'],
                'author' => $_POST['author'],
                'status' => $_POST['status']
            ];

            $this->replyModel->update($id, $data);
            header('Location: /admin/replies');
            exit;
        }

        require_once __DIR__ . '/../views/back/reply_form.php';
    }

    public function deleteReply($id) {
        $this->replyModel->delete($id);
        header('Location: /admin/replies');
        exit;
    }

    public function approveReply($id) {
        $this->replyModel->approve($id);
        header('Location: /admin/replies');
        exit;
    }

    public function rejectReply($id) {
        $this->replyModel->reject($id);
        header('Location: /admin/replies');
        exit;
    }
}
?>