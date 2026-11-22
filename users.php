<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$errors = [];
$old = ['firstname' => '', 'lastname' => '', 'email' => '', 'role' => 'member'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach (['firstname', 'lastname', 'email'] as $field) {
        $old[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $old['role'] = trim((string) ($_POST['role'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($old['firstname'] === '') {
        $errors['firstname'] = 'Enter a first name.';
    } elseif (strlen($old['firstname']) > 50) {
        $errors['firstname'] = 'Use 50 characters or fewer.';
    }
    if ($old['lastname'] === '') {
        $errors['lastname'] = 'Enter a last name.';
    } elseif (strlen($old['lastname']) > 50) {
        $errors['lastname'] = 'Use 50 characters or fewer.';
    }
    if ($old['email'] === '') {
        $errors['email'] = 'Enter an email address.';
    } elseif (strlen($old['email']) > 255 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($password === '') {
        $errors['password'] = 'Enter a password.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Use at least 8 characters, including a letter and a number.';
    }
    if (!in_array($old['role'], ['admin', 'member'], true)) {
        $errors['role'] = 'Choose a valid role.';
    }

    if (!isset($errors['email'])) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $old['email']]);
        if ($check->fetch()) {
            $errors['email'] = 'That email is already in use.';
        }
    }

    if ($errors === []) {
        try {
            $insert = $pdo->prepare('INSERT INTO users (firstname, lastname, email, password, role)
                                     VALUES (:firstname, :lastname, :email, :password, :role)');
            $insert->execute([
                'firstname' => $old['firstname'],
                'lastname' => $old['lastname'],
                'email' => $old['email'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $old['role'],
            ]);
            set_flash('success', 'User added.');
            redirect('/users.php');
        } catch (PDOException $exception) {
            // Duplicate emails can also happen if another admin submits concurrently.
            if ($exception->getCode() === '23000') {
                $errors['email'] = 'That email is already in use.';
            } else {
                error_log('Unable to add user: ' . $exception->getMessage());
                $errors['form'] = 'Could not add the user. Please try again.';
            }
        }
    }
}

// Deliberately exclude the password column from the query.
$users = $pdo->query('SELECT firstname, lastname, email, role, created_at
                      FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
$pageTitle = 'Users';
$pageScripts = [];
require __DIR__ . '/includes/header.php';
?>
<h1>Users</h1>
<div class="card">
    <h2>Staff accounts</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Date added</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $staff): ?>
                    <tr>
                        <td><?= e($staff['firstname'] . ' ' . $staff['lastname']) ?></td>
                        <td><?= e($staff['email']) ?></td>
                        <td><?= e(ucfirst($staff['role'])) ?></td>
                        <td><?= e(format_datetime($staff['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<section class="card" aria-labelledby="add-user-title">
    <h2 id="add-user-title">Add a user</h2>
    <?php if (isset($errors['form'])): ?>
        <p class="alert alert-error" role="alert"><?= e($errors['form']) ?></p>
    <?php endif; ?>
    <form action="/users.php" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="firstname">First name</label>
            <input id="firstname" name="firstname" type="text" maxlength="50" autocomplete="given-name" value="<?= e($old['firstname']) ?>" required>
            <?php field_error($errors, 'firstname'); ?>
        </div>
        <div class="form-field">
            <label for="lastname">Last name</label>
            <input id="lastname" name="lastname" type="text" maxlength="50" autocomplete="family-name" value="<?= e($old['lastname']) ?>" required>
            <?php field_error($errors, 'lastname'); ?>
        </div>
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="255" autocomplete="off" value="<?= e($old['email']) ?>" required>
            <?php field_error($errors, 'email'); ?>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" aria-describedby="password-help" required>
            <p id="password-help">At least 8 characters, with a letter and a number.</p>
            <?php field_error($errors, 'password'); ?>
        </div>
        <div class="form-field">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="member" <?= $old['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php field_error($errors, 'role'); ?>
        </div>
        <button class="btn btn-primary" type="submit">Add user</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
