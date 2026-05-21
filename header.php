<?php $title = $title ?? 'Tiket Whoosh'; ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><span>Whoosh</span><small>Ticket</small></a>
    <nav>
        <a href="index.php">Beli Tiket Whoosh</a>
        <?php if (is_login() && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="dashboard.php">Admin</a>
            <a href="bookings.php">Data Booking</a>
            <a href="users.php">User</a>
        <?php endif; ?>
        <?php if (is_login()): ?>
            <span><?= e($_SESSION['user']['nama']) ?></span>
            <a class="btn small" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="btn small" href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php if ($success = flash('success')): ?>
    <div class="alert success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = flash('error')): ?>
    <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
