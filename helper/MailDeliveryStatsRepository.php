<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

/**
 * Stores delivery metrics and detailed logs in storage files.
 */
final class MailDeliveryStatsRepository
{
    private string $statsFile;
    private string $logFile;
    private bool $storageEnabled = true;

    public function __construct(?string $statsFile = null, ?string $logFile = null)
    {
        $defaultStats = $statsFile ?: __DIR__ . '/../storage/mail_stats.json';
        $defaultLog = $logFile ?: __DIR__ . '/../storage/logs/mail_delivery.log';

        $this->statsFile = $this->resolveWritablePath($defaultStats, 'mail_stats.json');
        $this->logFile = $this->resolveWritablePath($defaultLog, 'mail_delivery.log');
        if ($this->statsFile === '' || $this->logFile === '') {
            $this->storageEnabled = false;
        }
    }

    /**
     * @param array<string, mixed> $event
     */
    public function record(array $event): void
    {
        if (!$this->storageEnabled) {
            return;
        }
        $event['timestamp'] = gmdate('c');
        try {
            $this->appendLog($event);
            $this->updateStats($event);
        } catch (RuntimeException) {
            // Le suivi email ne doit jamais casser le flux principal (inscription/login/etc).
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        if (!$this->storageEnabled) {
            return $this->defaultStats();
        }
        $this->ensureDir(dirname($this->statsFile));
        if (!is_file($this->statsFile)) {
            return $this->defaultStats();
        }

        $raw = (string) file_get_contents($this->statsFile);
        $data = json_decode($raw, true);

        return is_array($data) ? $data : $this->defaultStats();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentLogs(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        if (!$this->storageEnabled) {
            return [];
        }
        $this->ensureDir(dirname($this->logFile));
        if (!is_file($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines) || $lines === []) {
            return [];
        }

        $slice = array_slice($lines, -$limit);
        $out = [];
        foreach ($slice as $line) {
            $decoded = json_decode((string) $line, true);
            if (is_array($decoded)) {
                $out[] = $decoded;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $event
     */
    private function updateStats(array $event): void
    {
        $this->ensureDir(dirname($this->statsFile));
        $fp = fopen($this->statsFile, 'c+');
        if ($fp === false) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier des stats email.');
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Impossible de verrouiller le fichier des stats email.');
            }

            $raw = stream_get_contents($fp);
            $stats = json_decode((string) $raw, true);
            if (!is_array($stats)) {
                $stats = $this->defaultStats();
            }

            $duration = max(0.0, (float) ($event['duration_ms'] ?? 0.0));
            $status = (string) ($event['status'] ?? 'failed');

            $stats['total_attempts'] = (int) ($stats['total_attempts'] ?? 0) + 1;
            if ($status === 'success') {
                $stats['total_success'] = (int) ($stats['total_success'] ?? 0) + 1;
            } else {
                $stats['total_failed'] = (int) ($stats['total_failed'] ?? 0) + 1;
            }

            $stats['total_duration_ms'] = (float) ($stats['total_duration_ms'] ?? 0.0) + $duration;
            $attempts = max(1, (int) $stats['total_attempts']);
            $success = (int) $stats['total_success'];
            $failed = (int) $stats['total_failed'];

            $stats['average_duration_ms'] = round(((float) $stats['total_duration_ms']) / $attempts, 2);
            $stats['success_rate_percent'] = round(($success / $attempts) * 100, 2);
            $stats['failure_rate_percent'] = round(($failed / $attempts) * 100, 2);
            $stats['last_status'] = $status;
            $stats['last_error'] = (string) ($event['error'] ?? '');
            $stats['last_updated_at'] = gmdate('c');

            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
    }

    /**
     * @param array<string, mixed> $event
     */
    private function appendLog(array $event): void
    {
        $this->ensureDir(dirname($this->logFile));
        $json = json_encode($event, JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            $json = '{"status":"failed","error":"log_encode_error"}';
        }

        file_put_contents($this->logFile, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function ensureDir(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException('Impossible de creer le dossier: ' . $dir);
        }
    }

    private function resolveWritablePath(string $preferredPath, string $fileName): string
    {
        $preferredDir = dirname($preferredPath);
        try {
            $this->ensureDir($preferredDir);
            if (is_writable($preferredDir)) {
                return $preferredPath;
            }
        } catch (RuntimeException) {
            // fallback below
        }

        $fallbackDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cityzen_logs';
        try {
            $this->ensureDir($fallbackDir);
            if (is_writable($fallbackDir)) {
                return $fallbackDir . DIRECTORY_SEPARATOR . $fileName;
            }
        } catch (RuntimeException) {
            // ignore and disable storage
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultStats(): array
    {
        return [
            'total_attempts' => 0,
            'total_success' => 0,
            'total_failed' => 0,
            'total_duration_ms' => 0.0,
            'average_duration_ms' => 0.0,
            'success_rate_percent' => 0.0,
            'failure_rate_percent' => 0.0,
            'last_status' => '',
            'last_error' => '',
            'last_updated_at' => null,
        ];
    }
}
