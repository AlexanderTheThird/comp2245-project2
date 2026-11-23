<?php
// Edit ticket page. Any logged-in user can edit a ticket's title,
// description, priority and assignee. Uses the same form and checks
// as ticket-new.php.
require __DIR__ . '/includes/bootstrap.php';
require_login();

$priorities = ['Low', 'Medium', 'High'];

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($isPost) {
    verify_csrf();
}

// The id is in the URL when the page opens (?id=3)
// and in a hidden field when the form is sent
$ticketId = filter_var($isPost ? ($_POST['id'] ?? '') : ($_GET['id'] ?? ''), FILTER_VALIDATE_INT);

$ticket = false;
if ($ticketId !== false) {
    $stmt = $pdo->prepare('SELECT id, title, description, priority, assigned_to FROM tickets WHERE id = :id');
    $stmt->execute(['id' => $ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$ticket) {
    http_response_code(404);
    $pageTitle = 'Ticket not found';
    require __DIR__ . '/includes/header.php';
    echo '<h1>Ticket not found</h1>';
    echo "<p>We couldn't find that ticket. It may have been deleted.</p>";
    echo '<p><a class="btn btn-secondary" href="/index.php">Back to tickets</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Everyone who can be assigned a ticket, for the "Assigned to" list
$users = $pdo->query('SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname')
->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($isPost) {
    $old = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority'    => $_POST['priority'] ?? '',
        'assigned_to' => $_POST['assigned_to'] ?? '',
    ];

    // Same checks as the New ticket page
    if ($old['title'] === '') {
        $errors['title'] = 'Enter a title.';
    } elseif (mb_strlen($old['title']) > 150) {
        $errors['title'] = 'Keep the title to 150 characters or fewer.';
    }

    if ($old['description'] === '') {
        $errors['description'] = 'Describe the problem.';
    }

    if (!in_array($old['priority'], $priorities, true)) {
        $errors['priority'] = 'Choose a priority.';
    }

    $assignedTo = null;
    if ($old['assigned_to'] !== '') {
        $assignedTo = filter_var($old['assigned_to'], FILTER_VALIDATE_INT);
        $userIds = array_map('intval', array_column($users, 'id'));
        if ($assignedTo === false || !in_array($assignedTo, $userIds, true)) {
            $errors['assigned_to'] = 'Choose someone from the list.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE tickets
            SET title = :title, description = :description,
            priority = :priority, assigned_to = :assigned_to
            WHERE id = :id'
        );
        $stmt->execute([
            'title'       => $old['title'],
            'description' => $old['description'],
            'priority'    => $old['priority'],
            'assigned_to' => $assignedTo,
            'id'          => $ticketId,
        ]);

        set_flash('success', 'Ticket updated.');
        redirect('/ticket.php?id=' . $ticketId);
    }
} else {
    // First time the page opens: fill the form with the ticket's current values
    $old = [
        'title'       => $ticket['title'],
        'description' => $ticket['description'],
        'priority'    => $ticket['priority'],
        'assigned_to' => $ticket['assigned_to'] === null ? '' : (string) $ticket['assigned_to'],
    ];
}

$pageTitle = 'Edit ticket #' . $ticketId;
require __DIR__ . '/includes/header.php';
?>

<h1>Edit ticket #<?= e($ticketId) ?></h1>

<?php if ($errors): ?>
<div class="alert alert-error" role="alert">Please fix the problems below.</div>
<?php endif; ?>

<!-- novalidate turns off the browser's own checks, so our PHP messages can show -->
<form class="card" method="post" action="/ticket-edit.php" novalidate>
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= e($ticketId) ?>">

<div class="form-field">
<label for="title">Title</label>
<input type="text" id="title" name="title" maxlength="150" value="<?= e($old['title']) ?>">
<?php if (isset($errors['title'])): ?>
<p class="field-error"><?= e($errors['title']) ?></p>
<?php endif; ?>
</div>

<div class="form-field">
<label for="description">Description</label>
<textarea id="description" name="description" rows="6"><?= e($old['description']) ?></textarea>
<?php if (isset($errors['description'])): ?>
<p class="field-error"><?= e($errors['description']) ?></p>
<?php endif; ?>
</div>

<div class="form-field">
<label for="priority">Priority</label>
<select id="priority" name="priority">
<option value="">Choose a priority</option>
<?php foreach ($priorities as $priority): ?>
<option value="<?= e($priority) ?>" <?= $old['priority'] === $priority ? 'selected' : '' ?>>
<?= e($priority) ?>
</option>
<?php endforeach; ?>
</select>
<?php if (isset($errors['priority'])): ?>
<p class="field-error"><?= e($errors['priority']) ?></p>
<?php endif; ?>
</div>

<div class="form-field">
<label for="assigned_to">Assigned to</label>
<select id="assigned_to" name="assigned_to">
<option value="">Nobody</option>
<?php foreach ($users as $person): ?>
<option value="<?= e($person['id']) ?>"
<?= (string) $old['assigned_to'] === (string) $person['id'] ? 'selected' : '' ?>>
<?= e($person['firstname'] . ' ' . $person['lastname']) ?>
</option>
<?php endforeach; ?>
</select>
<?php if (isset($errors['assigned_to'])): ?>
<p class="field-error"><?= e($errors['assigned_to']) ?></p>
<?php endif; ?>
</div>

<div class="form-actions">
<button type="submit" class="btn btn-primary">Save changes</button>
<a class="btn btn-secondary" href="/ticket.php?id=<?= e($ticketId) ?>">Cancel</a>
</div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
