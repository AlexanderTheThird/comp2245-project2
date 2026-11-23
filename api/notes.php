<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login_api();      // JSON 401 if not logged in
require_method('POST');   // JSON 405 for any other method
verify_csrf_api();        // JSON 403 if the X-CSRF-Token header is wrong

$input = read_json_body();

// Validate input. is_string() stops arrays/objects sneaking in.
$ticketId = filter_var($input['ticket_id'] ?? null, FILTER_VALIDATE_INT);
$body = isset($input['body']) && is_string($input['body']) ? trim($input['body']) : '';

if (!$ticketId) {
    json_response(['error' => 'Choose a ticket to add the note to.'], 422);
}
if ($body === '') {
    json_response(['error' => 'Write a note before adding it.'], 422);
}
if (mb_strlen($body) > 5000) {
    json_response(['error' => 'Keep the note under 5000 characters.'], 422);
}
if (find_ticket($pdo, $ticketId) === null) {
    json_response(['error' => 'That ticket no longer exists.'], 404);
}

// The author comes from the session, never from the request.
$stmt = $pdo->prepare('INSERT INTO notes (ticket_id, body, created_by) VALUES (?, ?, ?)');
$stmt->execute([$ticketId, $body, current_user()['id']]);
$noteId = (int) $pdo->lastInsertId();

// Read the note back so we return the real saved values (author name, timestamp).
$stmt = $pdo->prepare(
    'SELECT notes.id, notes.body, notes.created_at,
            CONCAT(users.firstname, " ", users.lastname) AS author_name
     FROM notes
     JOIN users ON users.id = notes.created_by
     WHERE notes.id = ?'
);
$stmt->execute([$noteId]);
$note = $stmt->fetch();

json_response([
    'note' => [
        'id' => (int) $note['id'],
        'body' => $note['body'],
        'author_name' => $note['author_name'],
        'created_at' => format_datetime($note['created_at']),
    ],
], 201);