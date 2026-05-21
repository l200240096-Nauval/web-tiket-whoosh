<?php
require 'config.php';
require_admin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM stations WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    flash('success', 'Data stasiun berhasil dihapus.');
    header('Location: stations.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM stations WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE stations SET kode = ?, nama = ?, kota = ? WHERE id = ?');
        $stmt->execute([$_POST['kode'], $_POST['nama'], $_POST['kota'], $_POST['id']]);
        flash('success', 'Data stasiun berhasil diubah.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO stations (kode, nama, kota) VALUES (?, ?, ?)');
        $stmt->execute([$_POST['kode'], $_POST['nama'], $_POST['kota']]);
        flash('success', 'Data stasiun berhasil ditambahkan.');
    }
    header('Location: stations.php');
    exit;
}

$stations = $pdo->query('SELECT * FROM stations ORDER BY kota, nama')->fetchAll();
$title = 'Data Stasiun';
require 'header.php';
?>
<h1>Data Stasiun</h1>
<section class="panel">
    <form method="post" class="grid">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="field">
            <label>Kode</label>
            <input name="kode" value="<?= e($edit['kode'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label>Nama Stasiun</label>
            <input name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label>Kota</label>
            <input name="kota" value="<?= e($edit['kota'] ?? '') ?>" required>
        </div>
        <button class="btn green" type="submit"><?= $edit ? 'Update' : 'Insert' ?></button>
    </form>
</section>
<section class="panel" style="margin-top: 18px;">
    <table>
        <tr><th>Kode</th><th>Nama</th><th>Kota</th><th>Aksi</th></tr>
        <?php foreach ($stations as $station): ?>
            <tr>
                <td><?= e($station['kode']) ?></td>
                <td><?= e($station['nama']) ?></td>
                <td><?= e($station['kota']) ?></td>
                <td class="actions">
                    <a class="btn small" href="?edit=<?= e($station['id']) ?>">Edit</a>
                    <a class="btn small red" href="?delete=<?= e($station['id']) ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php require 'footer.php'; ?>

