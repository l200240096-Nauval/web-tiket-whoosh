<?php
require 'config.php';
require_admin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM schedules WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    flash('success', 'Data jadwal berhasil dihapus.');
    header('Location: schedules.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM schedules WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [$_POST['train_id'], $_POST['asal_id'], $_POST['tujuan_id'], $_POST['jam_berangkat'], $_POST['jam_tiba'], $_POST['harga'], $_POST['kursi_total']];
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE schedules SET train_id=?, asal_id=?, tujuan_id=?, jam_berangkat=?, jam_tiba=?, harga=?, kursi_total=? WHERE id=?');
        $stmt->execute([...$data, $_POST['id']]);
        flash('success', 'Data jadwal berhasil diubah.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO schedules (train_id, asal_id, tujuan_id, jam_berangkat, jam_tiba, harga, kursi_total) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($data);
        flash('success', 'Data jadwal berhasil ditambahkan.');
    }
    header('Location: schedules.php');
    exit;
}

$trains = $pdo->query('SELECT * FROM trains ORDER BY nama')->fetchAll();
$stations = $pdo->query('SELECT * FROM stations ORDER BY kota, nama')->fetchAll();
$schedules = $pdo->query("
    SELECT s.*, t.nama AS kereta, a.nama AS asal, j.nama AS tujuan
    FROM schedules s
    JOIN trains t ON t.id = s.train_id
    JOIN stations a ON a.id = s.asal_id
    JOIN stations j ON j.id = s.tujuan_id
    ORDER BY s.jam_berangkat
")->fetchAll();

$title = 'Data Jadwal';
require 'header.php';
?>
<h1>Data Jadwal</h1>
<section class="panel">
    <form method="post" class="grid">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="field">
            <label>Layanan</label>
            <select name="train_id" required>
                <?php foreach ($trains as $train): ?>
                    <option value="<?= e($train['id']) ?>" <?= ($edit['train_id'] ?? '') == $train['id'] ? 'selected' : '' ?>><?= e($train['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Asal</label>
            <select name="asal_id" required>
                <?php foreach ($stations as $station): ?>
                    <option value="<?= e($station['id']) ?>" <?= ($edit['asal_id'] ?? '') == $station['id'] ? 'selected' : '' ?>><?= e($station['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Tujuan</label>
            <select name="tujuan_id" required>
                <?php foreach ($stations as $station): ?>
                    <option value="<?= e($station['id']) ?>" <?= ($edit['tujuan_id'] ?? '') == $station['id'] ? 'selected' : '' ?>><?= e($station['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Berangkat</label>
            <input type="time" name="jam_berangkat" value="<?= e(substr($edit['jam_berangkat'] ?? '', 0, 5)) ?>" required>
        </div>
        <div class="field">
            <label>Tiba</label>
            <input type="time" name="jam_tiba" value="<?= e(substr($edit['jam_tiba'] ?? '', 0, 5)) ?>" required>
        </div>
        <div class="field">
            <label>Harga</label>
            <input type="number" name="harga" value="<?= e($edit['harga'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label>Kursi</label>
            <input type="number" name="kursi_total" value="<?= e($edit['kursi_total'] ?? '100') ?>" required>
        </div>
        <button class="btn green" type="submit"><?= $edit ? 'Update' : 'Insert' ?></button>
    </form>
</section>
<section class="panel" style="margin-top: 18px;">
    <table>
        <tr><th>Layanan</th><th>Rute</th><th>Jam Operasi</th><th>Harga</th><th>Aksi</th></tr>
        <?php foreach ($schedules as $schedule): ?>
            <tr>
                <td><?= e($schedule['kereta']) ?></td>
                <td><?= e($schedule['asal']) ?> ke <?= e($schedule['tujuan']) ?></td>
                <td>Setiap hari, <?= e(substr($schedule['jam_berangkat'], 0, 5)) ?> - <?= e(substr($schedule['jam_tiba'], 0, 5)) ?></td>
                <td><?= e(rupiah($schedule['harga'])) ?></td>
                <td class="actions">
                    <a class="btn small" href="?edit=<?= e($schedule['id']) ?>">Edit</a>
                    <a class="btn small red" href="?delete=<?= e($schedule['id']) ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php require 'footer.php'; ?>
