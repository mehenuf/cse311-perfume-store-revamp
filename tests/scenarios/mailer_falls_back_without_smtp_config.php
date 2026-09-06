<?php
/**
 * With no SMTP_HOST configured (env.php or environment), the mailer must
 * report itself unconfigured -- sendAppEmail() takes that as the signal to
 * fall back to mail() instead of trying to open an SMTP socket at all.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$con = new stdClass();
putenv('SMTP_HOST'); // unset regardless of whatever host runs this suite

require $ROOT . '/includes/mailer.php';

shim_report(function () {
    return ['smtp_not_configured' => smtpIsConfigured() === false];
});
