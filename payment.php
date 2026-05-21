<?php
require 'config.php';

$kode = $_GET['kode'] ?? $_POST['kode_booking'] ?? '';
$stmt = $pdo->prepare("
    SELECT b.*, s.jam_berangkat, t.nama AS kereta,
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'];
    $paymentCode = strtoupper($method) . '-' . $booking['kode_booking'];

    $stmt = $pdo->prepare('UPDATE bookings SET status = "Paid", payment_method = ?, payment_code = ?, paid_at = NOW() WHERE kode_booking = ?');
    $stmt->execute([$method, $paymentCode, $booking['kode_booking']]);

    flash('success', 'Pembayaran berhasil. Tiket sudah aktif.');
    header('Location: receipt.php?kode=' . urlencode($booking['kode_booking']));
    exit;
}

$title = 'Pembayaran Tiket Whoosh';
require 'header.php';
?>
<section class="panel" style="max-width: 760px; margin: auto;">
    <h1>Pembayaran Tiket Whoosh</h1>
    <table>
        <tr><th>Kode Booking</th><td><?= e($booking['kode_booking']) ?></td></tr>
        <tr><th>Penumpang</th><td><?= e($booking['nama_penumpang']) ?></td></tr>
        <tr><th>Layanan</th><td><?= e($booking['kereta']) ?></td></tr>
        <tr><th>Rute</th><td><?= e($booking['asal']) ?> ke <?= e($booking['tujuan']) ?></td></tr>
        <tr><th>Berangkat</th><td><?= e(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?>, <?= e(substr($booking['jam_berangkat'], 0, 5)) ?></td></tr>
        <tr><th>Total Bayar</th><td><strong><?= e(rupiah($booking['total_harga'])) ?></strong></td></tr>
        <tr><th>Status</th><td><?= e($booking['status']) ?></td></tr>
    </table>

    <?php if ($booking['status'] === 'Paid'): ?>
        <div class="alert success" style="margin-top: 16px;">Booking ini sudah dibayar.</div>
        <p><a class="btn" href="receipt.php?kode=<?= e($booking['kode_booking']) ?>">Lihat E-Tiket</a></p>
    <?php else: ?>
        <form method="post" class="payment-box">
            <input type="hidden" name="kode_booking" value="<?= e($booking['kode_booking']) ?>">
            <h2>Pilih Metode Pembayaran</h2>
            <label class="pay-option">
                <input type="radio" name="payment_method" value="Transfer Bank" required>
                <span>
                    <strong>Transfer Bank</strong>
                    <small>Bayar melalui ATM, mobile banking, atau internet banking.</small>
                </span>
            </label>
            <label class="pay-option">
                <input type="radio" name="payment_method" value="E-Wallet" required>
                <span>
                    <strong>E-Wallet</strong>
                    <small>Bayar memakai dompet digital.</small>
                </span>
            </label>
            <label class="pay-option">
                <input type="radio" name="payment_method" value="QRIS" required>
                <span>
                    <strong>QRIS</strong>
                    <small>Scan QR dari aplikasi pembayaran favorit.</small>
                </span>
            </label>
            <button class="btn green" type="submit">Bayar Sekarang</button>
        </form>
    <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
