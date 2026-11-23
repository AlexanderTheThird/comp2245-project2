<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login_api();
require_method('GET'); // read only, so no CSRF check here
require_once __DIR__ . '/../includes/ticket-list.php';

// Get the filter parameter from the query string, defaulting to 'all' if not provided
$filter = isset($_GET['filter']) && is_string($_GET['filter']) ? $_GET['filter'] : 'all';
if (!in_array($filter, ['all', 'open', 'mine'], true)) {
    json_response(['error' => 'Choose all, open or mine.'], 422);
}

// Fetch tickets for the current user based on the filter
$tickets = fetch_tickets($pdo, $filter, (int) current_user()['id']);
json_response(['count' => count($tickets), 'tickets' => $tickets], 200);
