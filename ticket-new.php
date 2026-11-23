<?php
// New ticket page where any logged in user can create a ticket.
require __DIR__ . '/includes/bootstrap.php';
require_login();

$priorities = ['Low', 'Medium', 'High'];

// Lists everyone who can be assigned a ticket
$users = $pdo->query('SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname')
->fetchAll(PDO::FETCH_ASSOC);

// $old holds what the user typed, so we can put it back in the form
// $errors holds one message per field that has a problem
$old = ['title' => '', 'description' => '', 'priority' => '', 'assigned_to' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority'    => $_POST['priority'] ?? '',
        'assigned_to' => $_POST['assigned_to'] ?? '',
    ];

    // Checks every field before saving
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

    // "" means Nobody. Anything else must be the id of a real user
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
            'INSERT INTO tickets (title, description, priority, assigned_to, created_by)
        VALUES (:title, :description, :priority, :assigned_to, :created_by)'
        );
        $stmt->execute([
            'title'       => $old['title'],
            'description' => $old['description'],
            'priority'    => $old['priority'],
            'assigned_to' => $assignedTo,
            'created_by'  => current_user()['id'],
        ]);
        $newId = (int) $pdo->lastInsertId();

        set_flash('success', 'Ticket created.');
        redirect('/ticket.php?id=' . $newId);
    }
}

$pageTitle = 'New ticket';
require __DIR__ . '/includes/header.php';
?>

<h1>New ticket</h1>

<?php if ($errors): ?>
<div class="alert alert-error" role="alert">Please fix the problems below.</div>
<?php endif; ?>

<!-- novalidate turns off the browser's own checks so that our PHP messages can show -->
<form class="card" method="post" action="/ticket-new.php" novalidate>
<?= csrf_field() ?>

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
<button type="submit" class="btn btn-primary">Create ticket</button>
<a class="btn btn-secondary" href="/index.php">Cancel</a>
</div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
