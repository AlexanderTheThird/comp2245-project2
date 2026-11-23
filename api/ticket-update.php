<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login_api();
require_method('POST');
verify_csrf_api();

$input = read_json_body();

$ticketId = filter_var($input['ticket_id'] ?? null, FILTER_VALIDATE_INT);
$action = isset($input['action']) && is_string($input['action']) ? $input['action'] : '';

if (!$ticketId) {
    json_response(['error' => 'Choose a ticket to update.'], 422);
}
if (find_ticket($pdo, $ticketId) === null) {
    json_response(['error' => 'That ticket no longer exists.'], 404);
}

if ($action === 'status') {
    $allowed = ['Open', 'In progress', 'Closed'];
    $status = isset($input['status']) && is_string($input['status']) ? $input['status'] : '';
    // strict in_array: the value must be exactly one of the three statuses.
    if (!in_array($status, $allowed, true)) {
        json_response(['error' => 'Choose Open, In progress or Closed.'], 422);
    }
    // updated_at = NOW() so "Last updated" moves even if the status is unchanged.
    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $ticketId]);
} elseif ($action === 'assign_me') {
    // The assignee comes from the session, never from the request.
    $stmt = $pdo->prepare('UPDATE tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([current_user()['id'], $ticketId]);
} else {
    json_response(['error' => 'That action is not supported.'], 422);
}

// Send back the saved values so the page shows what is really in the database.
$ticket = find_ticket($pdo, $ticketId);

json_response([
    'ticket' => [
        'id' => (int) $ticket['id'],
        'status' => $ticket['status'],
        'assigned_to_name' => $ticket['assigned_to_name'] ?? null, // null = nobody
        'updated_at' => format_datetime($ticket['updated_at']),
    ],
], 200);
