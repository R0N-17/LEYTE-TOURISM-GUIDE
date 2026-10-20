<?php
// Start of every api/*.php endpoint.
ini_set('display_errors', '0');                          // PHP warnings must never end up inside the JSON
ini_set('session.gc_maxlifetime', (string)(30 * 86400)); // keep "remember me" sessions alive
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);   // 'secure' only when the site is served over HTTPS
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
// The app uses mb_strlen / mb_strtolower (names, destinations). Without the mbstring extension PHP would die with a fatal error and the browser
// would get no JSON, so answer clearly instead. Enable it: Debian/Ubuntu `sudo apt install php-mbstring`; Windows/XAMPP: uncomment `extension=mbstring` in php.ini.
if (!extension_loaded('mbstring')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'The PHP mbstring extension is not enabled on this server. Enable it (see README.txt) and reload.']);
    exit;
}
require __DIR__ . '/config.php';
require __DIR__ . '/response.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/categories.php';
require __DIR__ . '/destinations.php';
require __DIR__ . '/reviews.php';
require __DIR__ . '/loginslides.php';
