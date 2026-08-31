<?php
/**
 * Single database connection for the whole application.
 *
 * Reads credentials from environment variables so the same code runs
 * against local XAMPP and against a hosted cloud database. Nothing
 * secret is committed - set the variables on your host (or in a local
 * .env consumed by your web server).
 *
 *   DB_HOST     hostname                      (default: localhost)
 *   DB_PORT     port                          (default: 3306)
 *   DB_USER     username                      (default: root)
 *   DB_PASS     password                      (default: empty)
 *   DB_NAME     database name                 (default: perfumestore)
 *   DB_SSL      "1" to require TLS - most managed MySQL hosts need this
 *   DB_SSL_CA   optional path to a CA bundle supplied by the host
 *
 * With no environment set, the defaults reproduce the original XAMPP
 * setup, so local development keeps working unchanged.
 */

if (!isset($con)) {

    $env = function ($key, $default = '') {
        $value = getenv($key);
        if ($value === false || $value === '') {
            $value = isset($_ENV[$key]) ? $_ENV[$key] : $default;
        }
        return $value;
    };

    $host     = $env('DB_HOST', 'localhost');
    $port     = (int) $env('DB_PORT', '3306');
    $username = $env('DB_USER', 'root');
    $password = $env('DB_PASS', '');
    $database = $env('DB_NAME', 'perfumestore');

    $con = mysqli_init();

    // Managed MySQL providers (Aiven, TiDB Cloud, Clever Cloud, PlanetScale)
    // only accept TLS connections.
    if ($env('DB_SSL', '0') === '1') {
        $ca = $env('DB_SSL_CA', '');
        mysqli_ssl_set($con, null, null, $ca !== '' ? $ca : null, null, null);
        $flags = MYSQLI_CLIENT_SSL;
        if ($ca === '') {
            $flags |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }
        @mysqli_real_connect($con, $host, $username, $password, $database, $port, null, $flags);
    } else {
        @mysqli_real_connect($con, $host, $username, $password, $database, $port);
    }

    if (mysqli_connect_errno()) {
        die('Unable to connect! Reason: ' . mysqli_connect_error());
    }

    mysqli_set_charset($con, 'utf8mb4');
}
