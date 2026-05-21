<?php
require 'config.php';
require_admin();

$counts = [
    'users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'stations' => $pdo->query('SELECT COUNT(*) FROM stations')->fetchColumn(),
    'trains' => $pdo->query('SELECT COUNT(*) FROM trains')->fetchColumn(),
    'schedules' => $pdo->query('SELECT COUNT(*) FROM schedules')->fetchColumn(),
    'bookings' => $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
];

$title = 'Dashboard Admin';
require 'header.php';
?>
<h1>Dashboard Admin</h1>
<div class="cards">
    <a class="panel" href="stations.php"><h2>Stasiun</h2><p><?= e($counts['stations']) ?> data</p></a>
    <a class="panel" href="trains.php"><h2>Layanan Whoosh</h2><p><?= e($counts['trains']) ?> data</p></a>
    <a class="panel" href="schedules.php"><h2>Jadwal</h2><p><?= e($counts['schedules']) ?> data</p></a>
    <a class="panel" href="bookings.php"><h2>Booking</h2><p><?= e($counts['bookings']) ?> data</p></a>
    <a class="panel" href="users.php"><h2>User</h2><p><?= e($counts['users']) ?> data</p></a>
</div>
<?php require 'footer.php'; ?>
