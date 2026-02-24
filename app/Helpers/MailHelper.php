<?php

namespace App\Helpers;

/**
 * Send email via SMTP (e.g. Gmail). Use when PHP mail() is not reliable (e.g. on Render).
 * Set env: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS; optional SMTP_SECURE=tls for 587.
 */
class MailHelper
{
    /**
     * Send email via SMTP if configured, otherwise return false (caller can use mail()).
     *
     * @param string $to Recipient email
     * @param string $subject Subject
     * @param string $body Plain text body
     * @param string $fromAddress From email address
     * @param string $fromName From name (optional)
     * @param string $replyTo Reply-To header (e.g. "Name <email@example.com>")
     * @return bool True if sent via SMTP, false if SMTP not configured or failed
     */
    public static function sendSmtp(
        string $to,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName = '',
        string $replyTo = ''
    ): bool {
        $host = trim((string) (getenv('SMTP_HOST') ?: $_ENV['SMTP_HOST'] ?? $_SERVER['SMTP_HOST'] ?? ''));
        $port = (int) (getenv('SMTP_PORT') ?: $_ENV['SMTP_PORT'] ?? $_SERVER['SMTP_PORT'] ?? 587);
        $user = trim((string) (getenv('SMTP_USER') ?: $_ENV['SMTP_USER'] ?? $_SERVER['SMTP_USER'] ?? ''));
        $pass = getenv('SMTP_PASS') ?: $_ENV['SMTP_PASS'] ?? $_SERVER['SMTP_PASS'] ?? '';

        if ($host === '' || $user === '' || $pass === '') {
            return false;
        }

        $secure = strtolower(trim((string) (getenv('SMTP_SECURE') ?: $_ENV['SMTP_SECURE'] ?? $_SERVER['SMTP_SECURE'] ?? 'tls')));
        $from = $fromName !== '' ? $fromName . ' <' . $fromAddress . '>' : $fromAddress;

        try {
            if ($port === 465) {
                return self::sendSmtpSsl($host, $port, $user, $pass, $from, $to, $replyTo, $subject, $body);
            }
            return self::sendSmtpStartTls($host, $port, $user, $pass, $from, $to, $replyTo, $subject, $body);
        } catch (\Throwable $e) {
            error_log('MailHelper SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private static function readResponse($socket): string
    {
        $line = '';
        while (($ch = fgets($socket, 515)) !== false) {
            $line .= $ch;
            if (strlen($ch) >= 4 && $ch[3] === ' ') {
                break;
            }
        }
        return $line;
    }

    private static function cmd($socket, string $cmd): string
    {
        fwrite($socket, $cmd . "\r\n");
        return self::readResponse($socket);
    }

    private static function sendSmtpSsl(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $from,
        string $to,
        string $replyTo,
        string $subject,
        string $body
    ): bool {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $socket = @stream_socket_client(
            'ssl://' . $host . ':' . $port,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if (!$socket) {
            error_log("SMTP connect failed: $errstr ($errno)");
            return false;
        }
        stream_set_timeout($socket, 15);

        self::readResponse($socket);
        self::cmd($socket, 'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        self::cmd($socket, 'AUTH LOGIN');
        self::cmd($socket, base64_encode($user));
        $r = self::cmd($socket, base64_encode($pass));
        if (strpos($r, '235') === false) {
            fclose($socket);
            return false;
        }
        self::cmd($socket, 'MAIL FROM:<' . self::extractEmail($from) . '>');
        self::cmd($socket, 'RCPT TO:<' . $to . '>');
        self::cmd($socket, 'DATA');
        $headers = 'From: ' . $from . "\r\n";
        if ($replyTo !== '') {
            $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        }
        $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $headers .= 'Subject: ' . self::encodeSubject($subject) . "\r\n";
        self::cmd($socket, $headers . "\r\n" . $body . "\r\n.");
        self::cmd($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    private static function sendSmtpStartTls(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $from,
        string $to,
        string $replyTo,
        string $subject,
        string $body
    ): bool {
        $socket = @stream_socket_client(
            'tcp://' . $host . ':' . $port,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT
        );
        if (!$socket) {
            error_log("SMTP connect failed: $errstr ($errno)");
            return false;
        }
        stream_set_timeout($socket, 15);

        self::readResponse($socket);
        self::cmd($socket, 'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $r = self::cmd($socket, 'STARTTLS');
        if (strpos($r, '220') === false && strpos($r, '250') === false) {
            fclose($socket);
            return false;
        }
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }
        self::cmd($socket, 'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        self::cmd($socket, 'AUTH LOGIN');
        self::cmd($socket, base64_encode($user));
        $r = self::cmd($socket, base64_encode($pass));
        if (strpos($r, '235') === false) {
            fclose($socket);
            return false;
        }
        self::cmd($socket, 'MAIL FROM:<' . self::extractEmail($from) . '>');
        self::cmd($socket, 'RCPT TO:<' . $to . '>');
        self::cmd($socket, 'DATA');
        $headers = 'From: ' . $from . "\r\n";
        if ($replyTo !== '') {
            $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        }
        $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $headers .= 'Subject: ' . self::encodeSubject($subject) . "\r\n";
        self::cmd($socket, $headers . "\r\n" . $body . "\r\n.");
        self::cmd($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    private static function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return trim($m[1]);
        }
        return trim($from);
    }

    private static function encodeSubject(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }
}
