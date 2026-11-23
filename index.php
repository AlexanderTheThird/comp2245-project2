<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
require_once __DIR__ . '/includes/ticket-list.php';

// Fetch all tickets for the current user
$tickets = fetch_tickets($pdo, 'all', (int) current_user()['id']);
$count = count($tickets);

// Set page title and scripts
$pageTitle = 'Tickets';
$pageScripts = ['/js/ticket-list.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <h1>Tickets</h1>
    <a class="btn btn-primary" href="/ticket-new.php">New ticket</a>
</div>

// Filter buttons for ticket list
<div class="filters" role="group" aria-label="Filter tickets">
    <button type="button" class="filter-btn is-active" data-filter="all">All</button>
    <button type="button" class="filter-btn" data-filter="open">Open</button>
    <button type="button" class="filter-btn" data-filter="mine">My tickets</button>
</div>

// Search form for ticket list
<p id="ticket-count" class="muted" aria-live="polite">Showing <?= e($count) ?> <?= $count === 1 ? 'ticket' : 'tickets' ?></p>
<p id="ticket-error" class="alert alert-error" role="alert" hidden></p>

// Ticket table
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created by</th>
                <th>Assigned to</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody id="ticket-body">
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?= e($ticket['id']) ?></td>
                    <td><a href="/ticket.php?id=<?= e($ticket['id']) ?>"><?= e($ticket['title']) ?></a></td>
                    <td><span class="badge badge-<?= e(slug($ticket['priority'])) ?>"><?= e($ticket['priority']) ?></span></td>
                    <td><span class="badge badge-<?= e(slug($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td>
                    <td><?= e($ticket['created_by_name']) ?></td>
                    <?php if ($ticket['assigned_to_name'] === null): ?>
                        <td class="muted">Nobody</td>
                    <?php else: ?>
                        <td><?= e($ticket['assigned_to_name']) ?></td>
                    <?php endif; ?>
                    <td><?= e($ticket['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
