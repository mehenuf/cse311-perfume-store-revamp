<?php
/**
 * Copy this file to  config/env.php  on your server and fill in the real
 * values from your hosting control panel.
 *
 *   cp config/env.example.php config/env.php
 *
 * config/env.php contains a live password. Never commit it, and never share
 * a screenshot of it. It is already listed in .gitignore.
 */

return [
    // --- InfinityFree (values come from vPanel > MySQL Databases) ----------
    // 'DB_HOST' => 'sql123.infinityfree.com',
    // 'DB_PORT' => '3306',
    // 'DB_USER' => 'if0_12345678',
    // 'DB_PASS' => 'your-database-password',
    // 'DB_NAME' => 'if0_12345678_perfumestore',
    // 'DB_SSL'  => '0',            // InfinityFree does not use TLS for MySQL

    // --- Aiven / TiDB Cloud / Clever Cloud --------------------------------
    // 'DB_HOST' => 'mysql-xxxx.aivencloud.com',
    // 'DB_PORT' => '12345',
    // 'DB_USER' => 'avnadmin',
    // 'DB_PASS' => 'your-database-password',
    // 'DB_NAME' => 'perfumestore',
    // 'DB_SSL'  => '1',            // these providers require TLS

    // --- Local XAMPP ------------------------------------------------------
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'perfumestore',

    // Set to '1' only while debugging. It prints the real connection error,
    // which can leak the hostname and username, so turn it off again after.
    'APP_DEBUG' => '0',

    // --- Outgoing email, for "Forgot your password?" ----------------------
    // Leave all of these unset to use PHP's built-in mail() instead. That
    // works on a properly configured VPS, Render/Koyeb, or XAMPP with a
    // local mail setup -- but NOT on InfinityFree, which blocks mail()
    // entirely to stop spam abuse. Set SMTP_HOST (and the rest below) to
    // send through a real relay instead; any of them work the same way:
    //
    //   Brevo (recommended -- free, 300 emails/day, no credit card):
    //     'SMTP_HOST' => 'smtp-relay.brevo.com',
    //     'SMTP_PORT' => '587',
    //     'SMTP_USER' => 'your-brevo-login-email@example.com',
    //     'SMTP_PASS' => 'your-brevo-smtp-key',      // NOT your account password -- see DEPLOYMENT.md
    //     'SMTP_FROM' => 'your-brevo-login-email@example.com',
    //
    //   Gmail (a personal account, low volume only):
    //     'SMTP_HOST' => 'smtp.gmail.com',
    //     'SMTP_PORT' => '587',
    //     'SMTP_USER' => 'you@gmail.com',
    //     'SMTP_PASS' => 'your-16-character-app-password',   // not your normal password
    //     'SMTP_FROM' => 'you@gmail.com',
    //
    // 'SMTP_SECURE' defaults to 'tls' (STARTTLS on port 587, what every
    // provider above uses). Only set it to 'ssl' if your relay's docs
    // specifically say to connect on port 465 instead.
];
