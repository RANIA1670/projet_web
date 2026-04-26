<?php
/**
 * CityZen - Router
 */

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['GET', $path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $path, $controller, $method];
    }

    public function dispatch(string $uri, string $httpMethod): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Récupérer le chemin de base du projet (ex: /fati)
        $projectBase = parse_url(APP_URL, PHP_URL_PATH);
        
        // Nettoyer l'URI en enlevant la base du projet et le dossier public si présents
        $uri = str_replace($projectBase, '', $uri);
        $uri = str_replace('/public', '', $uri);
        
        // Assurer que l'URI commence par / et est propre
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as [$method, $path, $controller, $action]) {
            if ($method !== $httpMethod) continue;

            // Convert route params like {id} to regex
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->runController($controller, $action, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        require APP_PATH . 'views/errors/404.php';
    }

    private function runController(string $controllerName, string $action, array $params = []): void
    {
        $file = APP_PATH . 'controllers/' . $controllerName . '.php';
        if (!file_exists($file)) {
            die("Controller introuvable : $controllerName");
        }
        require_once $file;
        if (!class_exists($controllerName)) {
            die("Classe introuvable : $controllerName");
        }
        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            die("Méthode introuvable : $action dans $controllerName");
        }
        $controller->$action($params);
    }
}
