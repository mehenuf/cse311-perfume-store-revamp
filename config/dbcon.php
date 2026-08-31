<?php
/**
 * Single database connection for the whole application.
 *
 * Settings are resolved in this order:
 *
 *   1. config/env.php      a file you create on the server (see env.example.php).
 *                          This is the route for shared hosts like InfinityFree,
 *                          which do not let you set environment variables.
 *   2. environment vars    DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME,
 *                          DB_SSL, DB_SSL_CA. Use these on hosts that support
 *                          them (Render, Koyeb, Fly).
 *   3. built-in defaults   localhost / root / no password / perfumestore,
 *                          which reproduce the original XAMPP setup so local
 *                          development keeps working with no config at all.
 *
 * config/env.php holds a live password, so keep it off GitHub.
 */

if (!isset($con)) {

    $localConfig = [];
    if (is_file(__DIR__ . '/env.php')) {
        $loaded = require __DIR__ . '/env.php';
        if (is_array($loaded)) {
            $localConfig = $loaded;
        }
    }

    $env = function ($key, $default = '') use ($localConfig) {
        if (isset($localConfig[$key]) && $localConfig[$key] !== '') {
            return $localConfig[$key];
        }
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        return $default;
    };

    $host     = $env('DB_HOST', 'localhost');
    $port     = (int) $env('DB_PORT', '3306');
    $username = $env('DB_USER', 'root');
    $password = $env('DB_PASS', '');
    $database = $env('DB_NAME', 'perfumestore');

    $con = mysqli_init();

    // Managed MySQL providers (Aiven, TiDB Cloud, Clever Cloud) require TLS.
    // Shared hosts such as InfinityFree do not: leave DB_SSL unset there.
    if ($env('DB_SSL', '0') === '1') {
        $ca    = $env('DB_SSL_CA', '');
        $flags = MYSQLI_CLIENT_SSL;
        mysqli_ssl_set($con, null, null, $ca !== '' ? $ca : null, null, null);
        if ($ca === '') {
            $flags |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }
        @mysqli_real_connect($con, $host, $username, $password, $database, $port, null, $flags);
    } else {
        @mysqli_real_connect($con, $host, $username, $password, $database, $port);
    }

    if (mysqli_connect_errno()) {
        // Never print credentials or the raw driver error to visitors.
        if ($env('APP_DEBUG', '0') === '1') {
            die('Database connection failed: ' . mysqli_connect_error());
        }
        http_response_code(503);
        die('The store is temporarily unavailable. Please try again shortly.');
    }

    mysqli_set_charset($con, 'utf8mb4');
}
