<?php
require 'config.php';

$booking = null;
$kode = trim($_GET['kode'] ?? '');

if ($kode !== '') {
    $stmt = $pdo->prepare("
        SELECT b.*, s.jam_berangkat, s.jam_tiba, t.nama AS kereta,
               a.nama AS asal, j.nama AS tujuan
        FROM bookings b
        JOIN schedules s ON s.id = b.schedule_id
        JOIN trains t ON t.id = s.train_id
        JOIN stations a ON a.id = s.asal_id
        JOIN stations j ON j.id = s.tujuan_id
        WHERE b.kode_booking = ?
    ");
    $stmt->execute([$kode]);
    $booking = $stmt->fetch();

    if (!$booking) {
        flash('error', 'Kode booking tidak ditemukan.');
    }
}

$title = 'Cek Tiket Whoosh';
require 'header.php';
?>
<section class="panel" style="max-width: 760px; margin: auto;">
    <h1>Cek Tiket Whoosh</h1>
    <form method="get" class="grid two">
        <div class="field">
            <label>Kode Booking</label>
            <input type="text" name="kode" value="<?= e($kode) ?>" placeholder="Contoh: RK26052112363349" required>
        </div>
        <button class="btn orange" type="submit">Cek Status</button>
    </form>

    <?php if ($booking): ?>
        <?php
        $statusText = [
            'Booked' => 'Tiket sudah dipesan, tetapi belum dibayar.',
            'Paid' => 'Tiket sudah dibayar dan aktif.',
            'Cancelled' => 'Tiket sudah dibatalkan.',
        ][$booking['status']] ?? 'Status tidak diketahui.';
        ?>
        <div class="alert <?= $booking['status'] === 'Paid' ? 'success' : 'error' ?>" style="margin-top: 18px;">
            <?= e($statusText) ?>
        </div>
        <table style="margin-top: 12px;">
            <tr><th>Kode Booking</th><td><?= e($booking['kode_booking']) ?></td></tr>
            <tr><th>Penumpang</th><td><?= e($booking['nama_penumpang']) ?></td></tr>
            <tr><th>Layanan</th><td><?= e($booking['kereta']) ?></td></tr>
            <tr><th>Rute</th><td><?= e($booking['asal']) ?> ke <?= e($booking['tujuan']) ?></td></tr>
            <tr><th>Berangkat</th><td><?= e(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?>, <?= e(substr($booking['jam_berangkat'], 0, 5)) ?></td></tr>
            <tr><th>Total</th><td><?= e(rupiah($booking['total_harga'])) ?></td></tr>
            <tr><th>Status</th><td><?= e($booking['status']) ?></td></tr>
        </table>
        <p class="actions">
            <?php if ($booking['status'] !== 'Paid'): ?>
                <a class="btn orange" href="payment.php?kode=<?= e($booking['kode_booking']) ?>">Bayar Sekarang</a>
            <?php else: ?>
                <a class="btn green" href="receipt.php?kode=<?= e($booking['kode_booking']) ?>">Lihat E-Tiket</a>
            <?php endif; ?>
            <a class="btn" href="index.php">Pesan Tiket Baru</a>
        </p>
    <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
