// naaryah naraine login.php
<?php

require __DIR__ . '/includes/bootstrap.php';

//redirect users who logged in already
if (current_user() !== null) {
    redirect('/index.php');
}

$error = '';
$email = '';

// this processes the login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // finds the user using their email
    $stmt = $pdo->prepare(
        'SELECT id, firstname, lastname, email, password, role
         FROM users
         WHERE email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //check da password
    if (
        $user === false ||
        !password_verify($password, $user['password'])
    ) {

        $error = "That email and password don't match an account.";

    } else {

        // make a new session ID after successful login
        session_regenerate_id(true);

        // store user information, not password
        $_SESSION['user'] = [
            'id' => $user['id'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        //take a trip to the ticket list
        redirect('/index.php');
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';

?>

<section class="card">

    <h1>Log in to Fix-It Desk</h1>

    <p>Please enter your account details to continue.</p>

    <?php if ($error !== ''): ?>

        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login.php">
        <?= csrf_field() ?>
        <div class="form-field">

            <label for="email">Email address</label>



            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                required
            >
        </div>
        <div class="form-field">

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>
        <button type="submit" class="btn btn-primary">
            Log in
        </button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>