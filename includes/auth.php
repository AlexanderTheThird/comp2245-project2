//naaryah naraine auth.php
<?php

//get the user thats logged in at the moment
function current_user()
{
    return $_SESSION['user'] ?? null;
}
// check to see if someone is logged in
function require_login()
{
    if (current_user() === null) {
        redirect('/login.php');
    }
}
// this sees if there are API requests
function require_login_api()
{
    if (current_user() === null) {
        json_response(['error' => 'Please log in first.'], 401);
    }
}
// check if the logged-in user is an admin.
function require_admin()
{
    if (current_user() === null) {
        redirect('/login.php');
    }
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('403 Forbidden: You do not have permission to access this page.');
    }
}
// this is to make sure an API request uses the correct HTTP method.
function require_method($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_response(['error' => 'Method not allowed.'], 405);
    }
}
// CSRF token
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
// print the hidden CSRF field for HTML forms
function csrf_field()
{
    echo '<input type="hidden" name="csrf_token" value="' .
        e(csrf_token()) . '">';
}
// see who submitted CSRF token
function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (
        empty($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        http_response_code(403);
        exit('403 Forbidden: Invalid security token.');
    }
}
function verify_csrf_api()
{
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? '';
    if (
        empty($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        json_response(['error' => 'Invalid security token.'], 403);
    }
}
// can a user can delete a ticket?
function can_delete_ticket($ticket)
{
    $user = current_user();

    if ($user === null) {
        return false;
    }
    return $user['role'] === 'admin'
        || (int) $user['id'] === (int) $ticket['created_by'];
}