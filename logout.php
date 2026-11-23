// naaryah naraine logout.php
<?php

require __DIR__ . '/includes/bootstrap.php';

//this is a POST requests only zone
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('405 Method Not Allowed');
}
// check security token
verify_csrf();
// empty the session
$_SESSION = [];

// this deletes the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax'
        ]
    );
}
// terminate the session (bye bye)
session_destroy();

// return to homebase (the login page)
redirect('/login.php');