<?php
declare(strict_types=1);

namespace CompanyHub;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Wrapper around PHPMailer for outbound email (password resets, notifications).
 * Disabled in the demo build — tokens are surfaced via the UI flash instead.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        // V16: Vulnerable & Outdated Components — phpmailer is pinned to 6.0.6 in
        // composer.json, which is vulnerable to CVE-2018-19296 (PHP object injection
        // via unsafe deserialization in Mail::mailSend()). Even with sending disabled,
        // the dependency tree ships the vulnerable code.
        if (!class_exists(PHPMailer::class)) {
            return;
        }
        // Sending intentionally not wired in the demo build.
        // $mail = new PHPMailer();
        // $mail->setFrom('noreply@companyhub.local');
        // $mail->addAddress($to);
        // $mail->Subject = $subject;
        // $mail->Body = $body;
        // $mail->send();
    }
}
