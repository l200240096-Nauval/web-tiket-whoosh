<?php
require 'config.php';
require_admin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    flash('success', 'Data booking berhasil dihapus.');
    header('Location: bookings.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = (int) $_POST['jumlah_tiket'] * (int) $_POST['harga'];
    $data = [$_POST['schedule_id'], $_POST['tanggal_berangkat'], $_POST['nama_penumpang'], $_POST['no_identitas'], $_POST['email'], $_POST['telepon'], $_POST['jumlah_tiket'], $total, $_POST['status']];
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE bookings SET schedule_id=?, tanggal_berangkat=?, nama_penumpang=?, no_identitas=?, email=?, telepon=?, jumlah_tiket=?, total_harga=?, status=? WHERE id=?');
        $stmt->execute([...$data, $_POST['id']]);
        flash('success', 'Data booking berhasil diubah.');
    }
    header('Location: bookings.php');
    exit;
}

$schedules = $pdo->query("
    SELECT s.*, t.nama AS kereta, a.nama AS asal, j.nama AS tujuan
    FROM schedules s
    JOIN trains t ON t.id = s.train_id
    JOIN stations a ON a.id = s.asal_id
    JOIN stations j ON j.id = s.tujuan_id
    ORDER BY s.jam_berangkat
")->fetchAll();
$bookings = $pdo->query("
    SELECT b.*, t.nama AS kereta, a.nama AS asal, j.nama AS tujuan
    FROM bookings b
    JOIN schedules s ON s.id = b.schedule_id
    JOIN trains t ON t.id = s.train_id
    JOIN stations a ON a.id = s.asal_id
    JOIN stations j ON j.id = s.tujuan_id
    ORDER BY b.created_at DESC
")->fetchAll();

$title = 'Data Booking';
require 'header.php';
?>
<h1>Data Booking</h1>
<?php if ($edit): ?>
<section class="panel">
    <form method="post" class="grid">
        <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
        <div class="field">
            <label>Jadwal</label>
            <select name="schedule_id" required>
                <?php foreach ($schedules as $schedule): ?>
                    <option value="<?= e($schedule['id']) ?>" <?= $edit['schedule_id'] == $schedule['id'] ? 'selected' : '' ?>>
                        <?= e($schedule['kereta']) ?> - <?= e($schedule['asal']) ?> ke <?= e($schedule['tujuan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Nama</label><input name="nama_penumpang" value="<?= e($edit['nama_penumpang']) ?>" required></div>
        <div class="field"><label>Tanggal Berangkat</label><input type="date" name="tanggal_berangkat" value="<?= e($edit['tanggal_berangkat']) ?>" required></div>
        <div class="field"><label>Identitas</label><input name="no_identitas" value="<?= e($edit['no_identitas']) ?>" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($edit['email']) ?>" required></div>
        <div class="field"><label>Telepon</label><input name="telepon" value="<?= e($edit['telepon']) ?>" required></div>
        <div class="field"><label>Jumlah</label><input type="number" name="jumlah_tiket" value="<?= e($edit['jumlah_tiket']) ?>" required></div>
        <div class="field"><label>Harga Satuan</label><input type="number" name="harga" value="<?= e($edit['total_harga'] / $edit['jumlah_tiket']) ?>" required></div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <?php foreach (['Booked', 'Paid', 'Cancelled'] as $status): ?>
                    <option <?= $edit['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn green" type="submit">Update</button>
    </form>
</section>
<?php endif; ?>
<section class="panel" style="margin-top: 18px;">
    <table>
        <tr><th>Kode</th><th>Penumpang</th><th>Jadwal</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr>
        <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><?= e($booking['kode_booking']) ?></td>
                <td><?= e($booking['nama_penumpang']) ?><br><span class="muted"><?= e($booking['email']) ?></span></td>
                <td><?= e($booking['kereta']) ?><br><?= e($booking['asal']) ?> ke <?= e($booking['tujuan']) ?><br><span class="muted"><?= e(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?></span></td>
                <td><?= e(rupiah($booking['total_harga'])) ?></td>
                <td><?= e($booking['payment_method'] ?: '-') ?><br><span class="muted"><?= e($booking['payment_code'] ?: '') ?></span></td>
                <td><?= e($booking['status']) ?></td>
                <td class="actions">
                    <a class="btn small" href="?edit=<?= e($booking['id']) ?>">Edit</a>
                    <a class="btn small red" href="?delete=<?= e($booking['id']) ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php require 'footer.php'; ?>
