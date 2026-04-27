<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * SMTP settings read from environment variables.
 */
final class MailConfig
{
    public bool $enabled;
    public string $host;
    public int $port;
    public string $encryption;
    public string $username;
    public string $password;
    public string $fromEmail;
    public string $fromName;
    public int $timeout;

    public static function fromEnv(): self
    {
        $self = new self();

        $enabledRaw = (string) (getenv('CITYZEN_SMTP_ENABLED') ?: '0');
        $self->enabled = in_array(strtolower($enabledRaw), ['1', 'true', 'yes', 'on'], true);
        $self->host = (string) (getenv('CITYZEN_SMTP_HOST') ?: '');
        $self->port = (int) (getenv('CITYZEN_SMTP_PORT') ?: 587);
        $self->encryption = strtolower((string) (getenv('CITYZEN_SMTP_ENCRYPTION') ?: 'tls'));
        $self->username = (string) (getenv('CITYZEN_SMTP_USER') ?: '');
        $self->password = (string) (getenv('CITYZEN_SMTP_PASS') ?: '');
        $self->fromEmail = (string) (getenv('CITYZEN_MAIL_FROM') ?: 'no-reply@cityzen.local');
        $self->fromName = (string) (getenv('CITYZEN_MAIL_FROM_NAME') ?: 'CityZen');
        $self->timeout = max(3, (int) (getenv('CITYZEN_SMTP_TIMEOUT') ?: 15));

        if (!in_array($self->encryption, ['none', 'tls', 'ssl'], true)) {
            $self->encryption = 'tls';
        }

        return $self;
    }
}
