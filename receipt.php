<?php
require 'config.php';

$kode = $_GET['kode'] ?? '';
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
    flash('error', 'Booking tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$title = 'Tiket Whoosh Berhasil';
require 'header.php';
?>
<section class="panel" style="max-width: 720px; margin: auto;">
    <h1>E-Tiket Whoosh</h1>
    <table>
        <tr><th>Kode Booking</th><td><?= e($booking['kode_booking']) ?></td></tr>
        <tr><th>Penumpang</th><td><?= e($booking['nama_penumpang']) ?></td></tr>
        <tr><th>Layanan</th><td><?= e($booking['kereta']) ?></td></tr>
        <tr><th>Rute</th><td><?= e($booking['asal']) ?> ke <?= e($booking['tujuan']) ?></td></tr>
        <tr><th>Berangkat</th><td><?= e(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?>, <?= e(substr($booking['jam_berangkat'], 0, 5)) ?></td></tr>
        <tr><th>Jumlah</th><td><?= e($booking['jumlah_tiket']) ?> tiket</td></tr>
        <tr><th>Total</th><td><?= e(rupiah($booking['total_harga'])) ?></td></tr>
        <tr><th>Metode Pembayaran</th><td><?= e($booking['payment_method'] ?: '-') ?></td></tr>
        <tr><th>Kode Pembayaran</th><td><?= e($booking['payment_code'] ?: '-') ?></td></tr>
        <tr><th>Waktu Bayar</th><td><?= $booking['paid_at'] ? e(date('d M Y H:i', strtotime($booking['paid_at']))) : '-' ?></td></tr>
        <tr><th>Status</th><td><?= e($booking['status']) ?></td></tr>
    </table>
    <p class="actions">
        <?php if ($booking['status'] !== 'Paid'): ?>
            <a class="btn orange" href="payment.php?kode=<?= e($booking['kode_booking']) ?>">Bayar Sekarang</a>
        <?php endif; ?>
        <a class="btn" href="index.php">Beli Tiket Lagi</a>
    </p>
</section>
<?php require 'footer.php'; ?>
