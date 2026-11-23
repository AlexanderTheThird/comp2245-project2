<?php
// Deletes a ticket. Only takes POST requests from the confirm box on ticket.php
require __DIR__ . '/includes/bootstrap.php';
require_login();

// A link (GET) must never delete anything
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

verify_csrf();

$ticketId = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

$ticket = false;
if ($ticketId !== false) {
    $stmt = $pdo->prepare('SELECT id, created_by FROM tickets WHERE id = :id');
    $stmt->execute(['id' => $ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$ticket) {
    http_response_code(404);
    $pageTitle = 'Ticket not found';
    require __DIR__ . '/includes/header.php';
    echo '<h1>Ticket not found</h1>';
    echo "<p>We couldn't find that ticket. It may already have been deleted.</p>";
    echo '<p><a class="btn btn-secondary" href="/index.php">Back to tickets</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}


$me = current_user();
$isAdmin = $me['role'] === 'admin';
$isCreator = (int) $ticket['created_by'] === (int) $me['id'];

if (!$isAdmin && !$isCreator) {
    http_response_code(403);
    $pageTitle = 'Not allowed';
    require __DIR__ . '/includes/header.php';
    echo '<h1>Not allowed</h1>';
    echo '<p>Only the admin or the person who created this ticket can delete it.</p>';
    echo '<p><a class="btn btn-secondary" href="/index.php">Back to tickets</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('DELETE FROM tickets WHERE id = :id');
$stmt->execute(['id' => $ticketId]);

set_flash('success', 'Ticket #' . $ticketId . ' was deleted.');
redirect('/index.php');
