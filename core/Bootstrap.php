<?php

declare(strict_types=1);

namespace App\Core;

final class Bootstrap
{
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::loadLocalEnv();
        self::configureErrorHandling();

        spl_autoload_register(static function (string $class): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }

            $prefixMap = [
                'App\\Core\\' => __DIR__ . '/',
                'App\\Helpers\\' => __DIR__ . '/../helper/',
                'App\\Controllers\\' => __DIR__ . '/../controller/',
                'App\\Models\\' => __DIR__ . '/../model/',
            ];

            foreach ($prefixMap as $prefix => $baseDir) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }

                $relative = substr($class, strlen($prefix));
                $relativePath = str_replace('\\\\', DIRECTORY_SEPARATOR, $relative) . '.php';
                $path = $baseDir . $relativePath;
                if (is_file($path)) {
                    require_once $path;
                }
                return;
            }
        });

        require_once __DIR__ . '/layout.php';
        require_once __DIR__ . '/../model/users_store.php';

        self::$initialized = true;
    }

    private static function configureErrorHandling(): void
    {
        $debug = getenv('CITYZEN_DEBUG');
        $isDebug = $debug === '1' || strtolower((string) $debug) === 'true';
        if (!$isDebug) {
            $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
            $isDebug = $host === 'localhost' || str_starts_with($host, 'localhost:') || str_starts_with($host, '127.0.0.1');
        }

        error_reporting(E_ALL);
        ini_set('display_errors', $isDebug ? '1' : '0');
        ini_set('log_errors', '1');

        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        if (is_dir($logDir)) {
            ini_set('error_log', $logDir . '/php-error.log');
        }

        set_exception_handler(static function (\Throwable $e) use ($isDebug): void {
            $msg = sprintf(
                "[%s] %s: %s in %s:%d\n%s\n",
                date('c'),
                $e::class,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            error_log($msg);

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }

            if ($isDebug) {
                echo '<pre style="white-space:pre-wrap;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace">';
                echo htmlspecialchars($e::class . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString());
                echo '</pre>';
                return;
            }

            echo '<h1>Erreur interne</h1><p>Une erreur est survenue. Consultez les logs serveur.</p>';
        });

        register_shutdown_function(static function () use ($isDebug): void {
            $err = error_get_last();
            if (!is_array($err)) {
                return;
            }

            $type = (int) ($err['type'] ?? 0);
            $isFatal = in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
            if (!$isFatal) {
                return;
            }

            $message = (string) ($err['message'] ?? 'Fatal error');
            $file = (string) ($err['file'] ?? '');
            $line = (int) ($err['line'] ?? 0);
            error_log(sprintf("[%s] Fatal: %s in %s:%d\n", date('c'), $message, $file, $line));

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }

            if ($isDebug) {
                echo '<pre style="white-space:pre-wrap;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace">';
                echo htmlspecialchars("Fatal error: {$message}\n{$file}:{$line}");
                echo '</pre>';
                return;
            }
        });
    }

    /**
     * Loads local environment variables from storage/local.env (KEY=VALUE).
     * This avoids hardcoding sensitive SMTP credentials in source code.
     */
    private static function loadLocalEnv(): void
    {
        $envFile = __DIR__ . '/../storage/local.env';
        if (!is_file($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim((string) $parts[0]);
            $value = trim((string) $parts[1]);
            if ($key === '') {
                continue;
            }

            // Remove optional quotes.
            $value = trim($value, "\"'");

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
