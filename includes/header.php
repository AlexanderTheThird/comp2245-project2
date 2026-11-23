//naaryah naraine header.php
<?php
$user = current_user();
$flash = take_flash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">

    <title><?= e($pageTitle ?? 'Fix-It Desk') ?></title>

    <link rel="stylesheet" href="/styles.css">
</head>

<body>

<header class="site-header">
    <div class="container">
        <a href="/index.php" class="logo">
            Fix-It Desk
        </a>

        <?php if ($user !== null): ?
            <nav class="navigation">
                <a href="/index.php">Tickets</a>
                <a href="/ticket-new.php">New ticket</a>

                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/users.php">Users</a>
                <?php endif; ?>

            </nav>
            <div class="user-menu">
                <span>
                    <?= e($user['firstname'] . ' ' . $user['lastname']) ?>
                </span>

                <form action="/logout.php" method="POST">
                    <?= csrf_field() ?>

                    <button type="submit" class="btn btn-secondary">
                        Log out
                    </button>
                </form>

            </div>

        <?php endif; ?>

    </div>
</header>

<main id="main" class="container">

    <?php if ($flash !== null): ?>

        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>

    <?php endif; ?>