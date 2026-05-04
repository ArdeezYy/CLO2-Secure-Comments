<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (!is_post()) {
    redirect('/');
}

require_valid_csrf_token();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Strict',
    ]);
}

session_destroy();
redirect('/');
