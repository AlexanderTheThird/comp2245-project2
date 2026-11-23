<?php
// M5: the ticket page (details, notes, status, assign to me, delete part).
require __DIR__ . '/includes/bootstrap.php';
require_login();

$currentUser = current_user();

// Read the id from the URL; FILTER_VALIDATE_INT returns false for junk like "abc".
$ticketId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$ticket = $ticketId ? find_ticket($pdo, $ticketId) : null;

if ($ticket === null) {
    http_response_code(404);
    $pageTitle = 'Ticket not found';
    require __DIR__ . '/includes/header.php';
    ?>
    <h1>Ticket not found</h1>
    <p>That ticket doesn't exist. <a href="/index.php">Go back to all tickets</a>.</p>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Notes oldest first, joined with users to get each author's name.
$stmt = $pdo->prepare(
    'SELECT notes.id, notes.body, notes.created_at,
            CONCAT(users.firstname, " ", users.lastname) AS author_name
     FROM notes
     JOIN users ON users.id = notes.created_by
     WHERE notes.ticket_id = ?
     ORDER BY notes.created_at ASC, notes.id ASC'
);
$stmt->execute([$ticket['id']]);
$notes = $stmt->fetchAll();

$statuses = ['Open', 'In progress', 'Closed'];
$assignedToMe = (int) $ticket['assigned_to'] === (int) $currentUser['id'];
$canDelete = can_delete_ticket($ticket);

$pageTitle = 'Ticket #' . $ticket['id'];
// Only load the delete script if the delete part is on the page.
$pageScripts = ['/js/ticket-page.js'];
if ($canDelete) {
    $pageScripts[] = '/js/delete-confirm.js';
}
require __DIR__ . '/includes/header.php';
?>
<div id="ticket-page" data-ticket-id="<?= e($ticket['id']) ?>">
  <p><a href="/index.php">&larr; All tickets</a></p>

  <div class="ticket-header">
    <h1>#<?= e($ticket['id']) ?> <?= e($ticket['title']) ?></h1>
    <div class="ticket-actions">
      <a class="btn btn-secondary" href="/ticket-edit.php?id=<?= e($ticket['id']) ?>">Edit</a>
      <?php if ($canDelete): ?>
        <?php require __DIR__ . '/includes/delete-confirm.php'; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="ticket-layout">
    <div class="ticket-main">
      <div class="card">
        <p class="ticket-description"><?= e($ticket['description']) ?></p>
      </div>

      <section aria-labelledby="notes-heading">
        <h2 id="notes-heading">Notes</h2>
        <div id="note-list" class="note-list">
          <?php if (count($notes) === 0): ?>
            <p id="no-notes">No notes yet.</p>
          <?php endif; ?>
          <?php foreach ($notes as $note): ?>
            <article class="note">
              <p class="note-meta"><?= e($note['author_name']) ?> &middot; <?= e(format_datetime($note['created_at'])) ?></p>
              <p class="note-body"><?= e($note['body']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <form id="note-form" class="card" novalidate>
        <div class="form-field">
          <label for="note-body">Add a note</label>
          <textarea id="note-body" name="body" rows="4"></textarea>
        </div>
        <p id="note-message" class="alert alert-error" role="alert" hidden></p>
        <button type="submit" class="btn btn-primary">Add note</button>
      </form>
    </div>

    <aside class="ticket-side card">
      <dl class="ticket-details">
        <dt>Status</dt>
        <dd><span id="ticket-status" class="badge badge-<?= e(slug($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></dd>
        <dt>Priority</dt>
        <dd><span class="badge badge-<?= e(slug($ticket['priority'])) ?>"><?= e($ticket['priority']) ?></span></dd>
        <dt>Assigned to</dt>
        <dd id="ticket-assigned"><?= e($ticket['assigned_to_name'] ?? 'Nobody') ?></dd>
        <dt>Created by</dt>
        <dd><?= e($ticket['created_by_name']) ?></dd>
        <dt>Created</dt>
        <dd><?= e(format_datetime($ticket['created_at'])) ?></dd>
        <dt>Last updated</dt>
        <dd id="ticket-updated"><?= e(format_datetime($ticket['updated_at'])) ?></dd>
      </dl>

      <div class="form-field">
        <label for="status-select">Change status</label>
        <select id="status-select" name="status">
          <?php foreach ($statuses as $status): ?>
            <option value="<?= e($status) ?>"<?= $status === $ticket['status'] ? ' selected' : '' ?>><?= e($status) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (!$assignedToMe): ?>
        <button type="button" id="assign-me" class="btn btn-secondary">Assign to me</button>
      <?php endif; ?>

      <p id="ticket-message" class="ticket-message" role="status"></p>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
