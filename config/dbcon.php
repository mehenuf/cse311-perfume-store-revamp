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

if (!function_exists('appEnv')) {
    /**
     * The same env.php > getenv() > $_ENV resolution the database connection
     * uses, shared with anything else that reads config/env.php (currently
     * includes/mailer.php, for SMTP settings). config/env.php is only ever
     * read from disk once per request, regardless of how many keys are asked
     * for or how many files call this.
     */
    function appEnv($key, $default = '')
    {
        static $localConfig = null;
        if ($localConfig === null) {
            $localConfig = [];
            if (is_file(__DIR__ . '/env.php')) {
                $loaded = require __DIR__ . '/env.php';
                if (is_array($loaded)) {
                    $localConfig = $loaded;
                }
            }
        }

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
    }
}

if (!isset($con)) {

    $host     = appEnv('DB_HOST', 'localhost');
    $port     = (int) appEnv('DB_PORT', '3306');
    $username = appEnv('DB_USER', 'root');
    $password = appEnv('DB_PASS', '');
    $database = appEnv('DB_NAME', 'perfumestore');

    $con = mysqli_init();

    // Managed MySQL providers (Aiven, TiDB Cloud, Clever Cloud) require TLS.
    // Shared hosts such as InfinityFree do not: leave DB_SSL unset there.
    if (appEnv('DB_SSL', '0') === '1') {
        $ca    = appEnv('DB_SSL_CA', '');
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
        if (appEnv('APP_DEBUG', '0') === '1') {
            die('Database connection failed: ' . mysqli_connect_error());
        }
        http_response_code(503);
        die('The store is temporarily unavailable. Please try again shortly.');
    }

    mysqli_set_charset($con, 'utf8mb4');
}
