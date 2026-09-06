<?php
/**
 * Outgoing email, for the one thing the store currently sends: password
 * reset links.
 *
 * Several free hosts -- InfinityFree among them -- block PHP's built-in
 * mail() outright to stop spam abuse, so it can't be the only way this
 * sends. When SMTP_HOST is set in config/env.php (or as an environment
 * variable, same resolution as the database settings), this speaks SMTP
 * directly over a socket -- no library, no Composer, just the protocol --
 * to whatever relay you configured (Brevo, Mailgun, SendGrid, Gmail all
 * work the same way). With no SMTP_HOST set, it falls back to mail(),
 * which is enough wherever that already works (a properly configured VPS,
 * Render/Koyeb, or XAMPP with a local mail setup).
 */
require_once __DIR__ . '/../config/dbcon.php';

if (!function_exists('smtpIsConfigured')) {
    function smtpIsConfigured()
    {
        return appEnv('SMTP_HOST', '') !== '';
    }
}

if (!function_exists('sendAppEmail')) {
    /**
     * Sends one plain-text email. Returns true only on confirmed delivery
     * to the relay/MTA -- never confirmation that the recipient's inbox
     * actually received it, which no server-side call can promise.
     *
     * Deliberately never throws: a delivery failure here must never surface
     * as an error page to a visitor. functions/forgotpassword.php already
     * treats "the message may not have gone out" as a normal, silent
     * outcome, by design -- see the comment there.
     */
    function sendAppEmail($to, $subject, $body)
    {
        if (smtpIsConfigured()) {
            return sendAppEmailViaSmtp($to, $subject, $body);
        }

        $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $headers = "From: no-reply@" . $host . "\r\n";
        return @mail($to, $subject, $body, $headers);
    }
}

if (!function_exists('sendAppEmailViaSmtp')) {
    function sendAppEmailViaSmtp($to, $subject, $body)
    {
        $host   = appEnv('SMTP_HOST', '');
        $port   = (int) appEnv('SMTP_PORT', '587');
        $user   = appEnv('SMTP_USER', '');
        $pass   = appEnv('SMTP_PASS', '');
        $from   = appEnv('SMTP_FROM', $user !== '' ? $user : ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));
        // 'tls' (STARTTLS, the near-universal default on port 587) or 'ssl'
        // (implicit TLS from the first byte, the older convention on 465).
        $secure = strtolower(appEnv('SMTP_SECURE', 'tls'));

        $address = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $sock = @stream_socket_client($address, $errno, $errstr, 15);
        if (!$sock) {
            return false;
        }
        stream_set_timeout($sock, 15);

        $ehloName = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $ok = smtpExpect($sock, 220)
            && smtpCommand($sock, "EHLO $ehloName", 250);

        if ($ok && $secure === 'tls') {
            $ok = smtpCommand($sock, 'STARTTLS', 220)
                && @stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
                && smtpCommand($sock, "EHLO $ehloName", 250);
        }

        if ($ok && $user !== '') {
            $ok = smtpCommand($sock, 'AUTH LOGIN', 334)
                && smtpCommand($sock, base64_encode($user), 334)
                && smtpCommand($sock, base64_encode($pass), 235);
        }

        if ($ok) {
            $message = "From: {$from}\r\n"
                . "To: {$to}\r\n"
                . "Subject: {$subject}\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "\r\n"
                . str_replace("\n.", "\n..", $body); // dot-stuff lines that would end DATA early

            $ok = smtpCommand($sock, "MAIL FROM:<{$from}>", 250)
                && smtpCommand($sock, "RCPT TO:<{$to}>", [250, 251])
                && smtpCommand($sock, 'DATA', 354)
                && smtpCommand($sock, $message . "\r\n.", 250);
        }

        smtpCommand($sock, 'QUIT', null);
        fclose($sock);
        return $ok;
    }
}

if (!function_exists('smtpCommand')) {
    /** Sends one SMTP command (or raw message body for DATA) and checks the reply code. */
    function smtpCommand($sock, $line, $expectCode)
    {
        fwrite($sock, $line . "\r\n");
        if ($expectCode === null) {
            return true; // QUIT: nothing worth waiting for
        }
        return smtpExpect($sock, $expectCode);
    }
}

if (!function_exists('smtpExpect')) {
    /** Reads one (possibly multi-line) SMTP reply and checks its code. */
    function smtpExpect($sock, $expectCode)
    {
        $wanted = is_array($expectCode) ? $expectCode : [$expectCode];
        $line = '';
        do {
            $line = fgets($sock, 515);
            if ($line === false) {
                return false;
            }
        } while (isset($line[3]) && $line[3] === '-'); // "250-" means more lines follow

        return in_array((int) substr($line, 0, 3), $wanted, true);
    }
}
