<?php
require 'config.php';

$stations = $pdo->query('SELECT * FROM stations ORDER BY kota, nama')->fetchAll();
$where = 'WHERE 1=1';
$params = [];
$tanggalBerangkat = $_GET['tanggal'] ?? date('Y-m-d');

if (!empty($_GET['asal'])) {
    $where .= ' AND s.asal_id = ?';
    $params[] = $_GET['asal'];
}

if (!empty($_GET['tujuan'])) {
    $where .= ' AND s.tujuan_id = ?';
    $params[] = $_GET['tujuan'];
}

$stmt = $pdo->prepare("
    SELECT s.*, t.nama AS kereta, t.kelas, a.nama AS asal, a.kota AS kota_asal,
           j.nama AS tujuan, j.kota AS kota_tujuan,
           COALESCE(SUM(CASE WHEN b.status != 'Cancelled' THEN b.jumlah_tiket ELSE 0 END), 0) AS terjual
    FROM schedules s
    JOIN trains t ON t.id = s.train_id
    JOIN stations a ON a.id = s.asal_id
    JOIN stations j ON j.id = s.tujuan_id
    LEFT JOIN bookings b ON b.schedule_id = s.id AND b.tanggal_berangkat = ?
    $where
    GROUP BY s.id
    ORDER BY s.jam_berangkat
");
$params = array_merge([$tanggalBerangkat], $params);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$title = 'Beli Tiket Whoosh';
require 'header.php';
?>
<section class="hero">
    <div>
        <p class="eyebrow">Kereta Cepat Jakarta - Bandung</p>
        <h1>Beli tiket Whoosh</h1>
        <p>Pilih stasiun Whoosh, tanggal berangkat, lalu isi data penumpang baru secara langsung seperti proses pembelian tiket online.</p>
    </div>
    <form method="get" class="panel grid">
        <div class="field">
            <label>Stasiun Whoosh Asal</label>
            <select name="asal">
                <option value="">Semua asal</option>
                <?php foreach ($stations as $station): ?>
                    <option value="<?= e($station['id']) ?>" <?= ($_GET['asal'] ?? '') == $station['id'] ? 'selected' : '' ?>>
                        <?= e($station['nama']) ?> - <?= e($station['kota']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Stasiun Whoosh Tujuan</label>
            <select name="tujuan">
                <option value="">Semua tujuan</option>
                <?php foreach ($stations as $station): ?>
                    <option value="<?= e($station['id']) ?>" <?= ($_GET['tujuan'] ?? '') == $station['id'] ? 'selected' : '' ?>>
                        <?= e($station['nama']) ?> - <?= e($station['kota']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= e($tanggalBerangkat) ?>" required>
        </div>
        <button class="btn orange" type="submit">Cari Whoosh</button>
    </form>
</section>

<h2 class="section-title">Jadwal Tersedia Setiap Hari</h2>
<div class="cards">
    <?php foreach ($schedules as $schedule): ?>
        <?php $sisa = $schedule['kursi_total'] - $schedule['terjual']; ?>
        <article class="ticket">
            <div>
                <h3><?= e($schedule['kereta']) ?> <span class="muted">(<?= e($schedule['kelas']) ?>)</span></h3>
                <p><strong><?= e($schedule['asal']) ?></strong> ke <strong><?= e($schedule['tujuan']) ?></strong></p>
                <p><?= e(substr($schedule['jam_berangkat'], 0, 5)) ?> - <?= e(substr($schedule['jam_tiba'], 0, 5)) ?></p>
                <p class="muted">Tanggal dipilih: <?= e(date('d M Y', strtotime($tanggalBerangkat))) ?>. Sisa kursi: <?= e($sisa) ?></p>
            </div>
            <div>
                <h3><?= e(rupiah($schedule['harga'])) ?></h3>
                <a class="btn green" href="book.php?schedule_id=<?= e($schedule['id']) ?>&tanggal=<?= e($tanggalBerangkat) ?>">Pilih</a>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$schedules): ?>
        <div class="panel">Jadwal tidak ditemukan.</div>
    <?php endif; ?>
</div>
<?php require 'footer.php'; ?>
