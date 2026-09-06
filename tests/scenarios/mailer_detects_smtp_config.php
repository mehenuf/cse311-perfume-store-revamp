<?php
/**
 * Setting SMTP_HOST (via an environment variable here -- the same
 * resolution order config/env.php goes through) must be detected, so
 * sendAppEmail() takes the SMTP path instead of falling back to mail().
 * This only tests the configuration decision, not a live SMTP session --
 * there is no real relay to connect to in this environment.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$con = new stdClass();
putenv('SMTP_HOST=smtp.example.test');
putenv('SMTP_PORT=587');
putenv('SMTP_USER=someone@example.test');

require $ROOT . '/includes/mailer.php';

shim_report(function () {
    return [
        'smtp_configured' => smtpIsConfigured() === true,
        'reads_configured_host' => appEnv('SMTP_HOST', '') === 'smtp.example.test',
        'reads_configured_port' => appEnv('SMTP_PORT', '587') === '587',
    ];
});
