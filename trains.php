<?php
require 'config.php';
require_admin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM trains WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
        flash('success', 'Data layanan berhasil dihapus.');
    header('Location: trains.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM trains WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE trains SET nama = ?, kelas = ? WHERE id = ?');
        $stmt->execute([$_POST['nama'], $_POST['kelas'], $_POST['id']]);
        flash('success', 'Data layanan berhasil diubah.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO trains (nama, kelas) VALUES (?, ?)');
        $stmt->execute([$_POST['nama'], $_POST['kelas']]);
        flash('success', 'Data layanan berhasil ditambahkan.');
    }
    header('Location: trains.php');
    exit;
}

$trains = $pdo->query('SELECT * FROM trains ORDER BY nama')->fetchAll();
$title = 'Data Layanan Whoosh';
require 'header.php';
?>
<h1>Data Layanan Whoosh</h1>
<section class="panel">
    <form method="post" class="grid">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="field">
            <label>Nama Layanan</label>
            <input name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label>Kelas</label>
            <input name="kelas" value="<?= e($edit['kelas'] ?? '') ?>" required>
        </div>
        <button class="btn green" type="submit"><?= $edit ? 'Update' : 'Insert' ?></button>
    </form>
</section>
<section class="panel" style="margin-top: 18px;">
    <table>
        <tr><th>Nama</th><th>Kelas</th><th>Aksi</th></tr>
        <?php foreach ($trains as $train): ?>
            <tr>
                <td><?= e($train['nama']) ?></td>
                <td><?= e($train['kelas']) ?></td>
                <td class="actions">
                    <a class="btn small" href="?edit=<?= e($train['id']) ?>">Edit</a>
                    <a class="btn small red" href="?delete=<?= e($train['id']) ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php require 'footer.php'; ?>
