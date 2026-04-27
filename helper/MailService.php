<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

/**
 * Facade service for SMTP sending + metrics + detailed logs.
 */
final class MailService
{
    private MailConfig $config;
    private SmtpMailer $smtpMailer;
    private MailDeliveryStatsRepository $statsRepository;

    public function __construct(
        ?MailConfig $config = null,
        ?SmtpMailer $smtpMailer = null,
        ?MailDeliveryStatsRepository $statsRepository = null
    ) {
        $this->config = $config ?? MailConfig::fromEnv();
        $this->smtpMailer = $smtpMailer ?? new SmtpMailer();
        $this->statsRepository = $statsRepository ?? new MailDeliveryStatsRepository();
    }

    /**
     * @return array{ok: bool, error: string}
     */
    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): array
    {
        $startedAt = microtime(true);
        $error = '';

        try {
            if (!$this->config->enabled) {
                throw new RuntimeException('SMTP desactive (CITYZEN_SMTP_ENABLED=0).');
            }

            if ($this->config->host === '') {
                throw new RuntimeException('Host SMTP manquant.');
            }

            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Destinataire email invalide.');
            }

            $message = $this->buildMessage($to, $subject, $htmlBody, $textBody);
            $this->smtpMailer->send($this->config, $message);

            $durationMs = round((microtime(true) - $startedAt) * 1000, 2);
            $this->statsRepository->record([
                'status' => 'success',
                'to' => $to,
                'subject' => $subject,
                'duration_ms' => $durationMs,
                'error' => '',
            ]);

            return ['ok' => true, 'error' => ''];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $durationMs = round((microtime(true) - $startedAt) * 1000, 2);
            $this->statsRepository->record([
                'status' => 'failed',
                'to' => $to,
                'subject' => $subject,
                'duration_ms' => $durationMs,
                'error' => $error,
            ]);

            return ['ok' => false, 'error' => $error];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return $this->statsRepository->getSummary();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentLogs(int $limit = 50): array
    {
        return $this->statsRepository->recentLogs($limit);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMessage(string $to, string $subject, string $htmlBody, string $textBody): array
    {
        $boundary = 'b_' . bin2hex(random_bytes(12));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $safeFromName = $this->sanitizeHeader($this->config->fromName);
        $fromEmail = $this->sanitizeHeader($this->config->fromEmail);
        $toSafe = $this->sanitizeHeader($to);

        if ($textBody === '') {
            $textBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        }

        $headers = [
            'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
            'From: ' . $safeFromName . ' <' . $fromEmail . '>',
            'To: <' . $toSafe . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = [];
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/plain; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: 8bit';
        $body[] = '';
        $body[] = $textBody;
        $body[] = '';
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/html; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: 8bit';
        $body[] = '';
        $body[] = $htmlBody;
        $body[] = '';
        $body[] = '--' . $boundary . '--';

        return [
            'from_email' => $fromEmail,
            'to' => $toSafe,
            'mime' => implode("\r\n", array_merge($headers, [''], $body)),
        ];
    }

    private function sanitizeHeader(string $value): string
    {
        $value = str_replace(["\r", "\n"], '', trim($value));

        return $value;
    }
}
