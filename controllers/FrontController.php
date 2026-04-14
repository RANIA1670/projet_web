<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Reply.php';

class FrontController {
    private $postModel;
    private $replyModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->replyModel = new Reply();
    }

    public function index() {
        $posts = $this->postModel->getRecent(10);
        require_once __DIR__ . '/../views/front/index.php';
    }

    public function showPost($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            header('Location: /');
            exit;
        }
        $replies = $this->replyModel->getByPostId($id);
        require_once __DIR__ . '/../views/front/post.php';
    }

    public function addReply() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'post_id' => $_POST['post_id'],
                'content' => $_POST['content'],
                'author' => $_POST['author']
            ];

            $this->replyModel->create($data);
            header('Location: /post/' . $_POST['post_id']);
            exit;
        }
    }
}
?>