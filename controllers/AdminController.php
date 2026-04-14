<?php
/**
 * Contrôleur d'administration
 * Gestion des actions générales d'administration
 */

class AdminController {
    // Page d'accueil de l'administration
    public function dashboard() {
        require_once __DIR__ . '/../models/Post.php';
        require_once __DIR__ . '/../models/Reply.php';

        $postModel = new Post();
        $replyModel = new Reply();

        $postStats = $postModel->getStats();
        $replyStats = $replyModel->getStats();

        $stats = [
            'total_posts' => $postStats['total'],
            'recent_posts' => $postStats['recent'],
            'pending_posts' => $postStats['pending'],
            'total_replies' => $replyStats['total'],
            'pending_replies' => $replyStats['pending']
        ];

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // Vérifier l'authentification (basique pour l'exemple)
    public static function checkAuth() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /admin.php?action=login');
            exit;
        }
    }

    // Page de connexion
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Authentification basique (à remplacer par une vraie vérification)
            if ($username === 'admin' && $password === 'password') {
                session_start();
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: /admin.php');
                exit;
            } else {
                $error = 'Identifiants incorrects';
            }
        }

        require_once __DIR__ . '/../views/admin/login.php';
    }

    // Déconnexion
    public function logout() {
        session_start();
        session_destroy();
        header('Location: /admin.php?action=login');
        exit;
    }
}
?>