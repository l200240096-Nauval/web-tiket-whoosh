<?php
require 'config.php';

$id = $_GET['schedule_id'] ?? $_POST['schedule_id'] ?? null;
$tanggalBerangkat = $_GET['tanggal'] ?? $_POST['tanggal_berangkat'] ?? date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT s.*, t.nama AS kereta, t.kelas, a.nama AS asal, j.nama AS tujuan
    FROM schedules s
    JOIN trains t ON t.id = s.train_id
    JOIN stations a ON a.id = s.asal_id
    JOIN stations j ON j.id = s.tujuan_id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    flash('error', 'Jadwal tidak ditemukan.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_penumpang']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $identitas = trim($_POST['no_identitas']);
    $jumlah = max(1, (int) $_POST['jumlah_tiket']);
    $total = $jumlah * (int) $schedule['harga'];
    $kode = 'RK' . date('ymdHis') . random_int(10, 99);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $password = password_hash($identitas, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "user")');
        $stmt->execute([$nama, $email, $password]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO bookings (kode_booking, user_id, schedule_id, tanggal_berangkat, nama_penumpang, no_identitas, email, telepon, jumlah_tiket, total_harga) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$kode, $userId, $schedule['id'], $tanggalBerangkat, $nama, $identitas, $email, $telepon, $jumlah, $total]);

    $pdo->commit();
    flash('success', 'Booking berhasil. Silakan lanjutkan pembayaran.');
    header('Location: payment.php?kode=' . urlencode($kode));
    exit;
}

$title = 'Pembelian Tiket Whoosh';
require 'header.php';
?>
<section class="panel">
    <h1>Pembelian Tiket Whoosh</h1>
    <p><strong><?= e($schedule['kereta']) ?></strong> <?= e($schedule['asal']) ?> ke <?= e($schedule['tujuan']) ?>, <?= e(date('d M Y', strtotime($tanggalBerangkat))) ?> pukul <?= e(substr($schedule['jam_berangkat'], 0, 5)) ?></p>
    <form method="post" class="grid two">
        <input type="hidden" name="schedule_id" value="<?= e($schedule['id']) ?>">
        <input type="hidden" name="tanggal_berangkat" value="<?= e($tanggalBerangkat) ?>">
        <div class="field">
            <label>Nama Penumpang</label>
            <input type="text" name="nama_penumpang" required>
        </div>
        <div class="field">
            <label>No Identitas</label>
            <input type="text" name="no_identitas" required>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="field">
            <label>No Telepon</label>
            <input type="text" name="telepon" required>
        </div>
        <div class="field">
            <label>Jumlah Tiket</label>
            <input type="number" name="jumlah_tiket" min="1" value="1" required>
        </div>
        <div class="field">
            <label>Harga Per Tiket</label>
            <input type="text" value="<?= e(rupiah($schedule['harga'])) ?>" readonly>
        </div>
        <button class="btn green" type="submit">Pesan Whoosh</button>
        <a class="btn" href="index.php">Kembali</a>
    </form>
</section>
<?php require 'footer.php'; ?>
