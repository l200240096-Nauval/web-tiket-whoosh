<?php
require 'config.php';

if (is_login()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        header('Location: dashboard.php');
        exit;
    }

    flash('error', 'Email atau password salah.');
}

$title = 'Login';
require 'header.php';
?>
<section class="panel" style="max-width: 460px; margin: 40px auto;">
    <h1>Login Admin</h1>
    <form method="post" class="grid">
        <div class="field" style="grid-column: 1 / -1;">
            <label>Email</label>
            <input type="email" name="email" value="admin@kai.test" required>
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label>Password</label>
            <input type="password" name="password" value="admin123" required>
        </div>
        <button class="btn" type="submit" style="grid-column: 1 / -1;">Masuk</button>
    </form>
</section>
<?php require 'footer.php'; ?>

