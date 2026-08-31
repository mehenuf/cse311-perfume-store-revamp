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
];
