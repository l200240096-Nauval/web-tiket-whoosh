<?php
require 'config.php';
require_admin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    flash('success', 'Data user berhasil dihapus.');
    header('Location: users.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        if ($_POST['password'] !== '') {
            $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, password=?, role=? WHERE id=?');
            $stmt->execute([$_POST['nama'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role'], $_POST['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, role=? WHERE id=?');
            $stmt->execute([$_POST['nama'], $_POST['email'], $_POST['role'], $_POST['id']]);
        }
        flash('success', 'Data user berhasil diubah.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$_POST['nama'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role']]);
        flash('success', 'Data user berhasil ditambahkan.');
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT id, nama, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
$title = 'Data User';
require 'header.php';
?>
<h1>Data User</h1>
<section class="panel">
    <form method="post" class="grid">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="field"><label>Nama</label><input name="nama" value="<?= e($edit['nama'] ?? '') ?>" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
        <div class="field">
            <label>Role</label>
            <select name="role">
                <option value="user" <?= ($edit['role'] ?? '') === 'user' ? 'selected' : '' ?>>user</option>
                <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>admin</option>
            </select>
        </div>
        <button class="btn green" type="submit"><?= $edit ? 'Update' : 'Insert' ?></button>
    </form>
</section>
<section class="panel" style="margin-top: 18px;">
    <table>
        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['nama']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['role']) ?></td>
                <td class="actions">
                    <a class="btn small" href="?edit=<?= e($user['id']) ?>">Edit</a>
                    <a class="btn small red" href="?delete=<?= e($user['id']) ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php require 'footer.php'; ?>

