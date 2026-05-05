<?php
/**
 * CityZen - Base Controller
 */

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'default'): void
    {
        extract($data);
        $viewFile = APP_PATH . 'views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("Vue introuvable : $view");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        $layoutFile = APP_PATH . 'views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/connexion');
        }
    }

    protected function currentUser(): ?array
    {
        if ($this->isLoggedIn()) {
            return [
                'id'    => $_SESSION['user_id'],
                'nom'   => $_SESSION['user_nom'] ?? '',
                'prenom'=> $_SESSION['user_prenom'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'role'  => $_SESSION['user_role'] ?? 'citoyen',
            ];
        }
        return null;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
