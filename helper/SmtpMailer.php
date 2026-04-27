<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

/**
 * Minimal SMTP client (SSL/TLS/LOGIN auth) without third-party dependency.
 */
final class SmtpMailer
{
    /**
     * @param array<string, mixed> $message
     */
    public function send(MailConfig $config, array $message): void
    {
        $socket = $this->openSocket($config);

        try {
            $this->expectCode($this->readResponse($socket), [220]);

            $localHost = gethostname() ?: 'localhost';
            $this->sendCommand($socket, 'EHLO ' . $localHost, [250]);

            if ($config->encryption === 'tls') {
                $this->sendCommand($socket, 'STARTTLS', [220]);
                $cryptoOk = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($cryptoOk !== true) {
                    throw new RuntimeException('Echec du chiffrement TLS.');
                }
                $this->sendCommand($socket, 'EHLO ' . $localHost, [250]);
            }

            if ($config->username !== '') {
                $this->sendCommand($socket, 'AUTH LOGIN', [334]);
                $this->sendCommand($socket, base64_encode($config->username), [334]);
                $this->sendCommand($socket, base64_encode($config->password), [235]);
            }

            $from = (string) $message['from_email'];
            $to = (string) $message['to'];
            $mime = (string) $message['mime'];

            $this->sendCommand($socket, 'MAIL FROM:<' . $from . '>', [250]);
            $this->sendCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->sendCommand($socket, 'DATA', [354]);

            $payload = $this->dotStuff($mime) . "\r\n.";
            $this->sendCommand($socket, $payload, [250]);
            $this->sendCommand($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function openSocket(MailConfig $config)
    {
        $transport = $config->encryption === 'ssl' ? 'ssl://' : '';
        $remote = $transport . $config->host . ':' . $config->port;

        $socket = @stream_socket_client($remote, $errno, $errstr, $config->timeout, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            throw new RuntimeException('Connexion SMTP impossible: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($socket, $config->timeout);

        return $socket;
    }

    /**
     * @param resource $socket
     */
    private function sendCommand($socket, string $command, array $expectedCodes): string
    {
        fwrite($socket, $command . "\r\n");
        $response = $this->readResponse($socket);
        $this->expectCode($response, $expectedCodes);

        return $response;
    }

    /**
     * @param resource $socket
     */
    private function readResponse($socket): string
    {
        $response = '';

        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }

            $response .= $line;

            // SMTP multiline: 250-... then 250 ...
            if (preg_match('/^\d{3}\s/', $line) === 1) {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('Aucune reponse du serveur SMTP.');
        }

        return $response;
    }

    private function expectCode(string $response, array $expectedCodes): void
    {
        $code = (int) substr(ltrim($response), 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('SMTP error [' . $code . ']: ' . trim($response));
        }
    }

    private function dotStuff(string $payload): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $payload) ?: [];
        foreach ($lines as $i => $line) {
            if (str_starts_with((string) $line, '.')) {
                $lines[$i] = '.' . $line;
            }
        }

        return implode("\r\n", $lines);
    }
}
