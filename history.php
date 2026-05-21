<?php
require 'config.php';

$email = trim($_GET['email'] ?? '');
$bookings = [];
$sessionCodes = $_SESSION['booking_history'] ?? [];

if ($email !== '') {
    $stmt = $pdo->prepare("
        SELECT b.*, s.jam_berangkat, s.jam_tiba, t.nama AS kereta,
               a.nama AS asal, j.nama AS tujuan
        FROM bookings b
        JOIN schedules s ON s.id = b.schedule_id
        JOIN trains t ON t.id = s.train_id
        JOIN stations a ON a.id = s.asal_id
        JOIN stations j ON j.id = s.tujuan_id
        WHERE b.email = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$email]);
    $bookings = $stmt->fetchAll();
} elseif ($sessionCodes) {
    $placeholders = implode(',', array_fill(0, count($sessionCodes), '?'));
    $stmt = $pdo->prepare("
        SELECT b.*, s.jam_berangkat, s.jam_tiba, t.nama AS kereta,
               a.nama AS asal, j.nama AS tujuan
        FROM bookings b
        JOIN schedules s ON s.id = b.schedule_id
        JOIN trains t ON t.id = s.train_id
        JOIN stations a ON a.id = s.asal_id
        JOIN stations j ON j.id = s.tujuan_id
        WHERE b.kode_booking IN ($placeholders)
        ORDER BY b.created_at DESC
    ");
    $stmt->execute($sessionCodes);
    $bookings = $stmt->fetchAll();
}

$title = 'History Tiket Whoosh';
require 'header.php';
?>
<section class="panel">
    <h1>History Tiket Whoosh</h1>
    <form method="get" class="grid two">
        <div class="field">
            <label>Cari dengan Email</label>
            <input type="email" name="email" value="<?= e($email) ?>" placeholder="email yang dipakai saat booking">
        </div>
        <button class="btn orange" type="submit">Cari History</button>
    </form>
</section>

<section class="panel" style="margin-top: 18px;">
    <?php if ($bookings): ?>
        <table>
            <tr>
                <th>Kode</th>
                <th>Penumpang</th>
                <th>Jadwal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= e($booking['kode_booking']) ?></td>
                    <td><?= e($booking['nama_penumpang']) ?><br><span class="muted"><?= e($booking['email']) ?></span></td>
                    <td>
                        <?= e($booking['kereta']) ?><br>
                        <?= e($booking['asal']) ?> ke <?= e($booking['tujuan']) ?><br>
                        <span class="muted"><?= e(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?>, <?= e(substr($booking['jam_berangkat'], 0, 5)) ?></span>
                    </td>
                    <td><?= e(rupiah($booking['total_harga'])) ?></td>
                    <td><?= e($booking['status']) ?></td>
                    <td class="actions">
                        <?php if ($booking['status'] === 'Paid'): ?>
                            <a class="btn small green" href="receipt.php?kode=<?= e($booking['kode_booking']) ?>">E-Tiket</a>
                        <?php else: ?>
                            <a class="btn small orange" href="payment.php?kode=<?= e($booking['kode_booking']) ?>">Bayar</a>
                        <?php endif; ?>
                        <a class="btn small" href="check_ticket.php?kode=<?= e($booking['kode_booking']) ?>">Cek</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Belum ada history tiket di browser ini. Jika pernah booking, cari menggunakan email yang dipakai saat pemesanan.</p>
    <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
