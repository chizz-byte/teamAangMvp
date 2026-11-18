<?php
// Database configuration - PDO MySQL
// Adjust credentials if your WAMP MySQL uses a different user/password.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'slu_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'SLU Marketplace');
define('APP_URL', '/slu-marketplace'); // adjust if deployed elsewhere

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
