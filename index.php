<?php
require_once __DIR__ . '/helpers.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $db = db_connect();
        $stmt = $db->prepare('SELECT id, password FROM users WHERE email = ? AND role = ? LIMIT 1');
        $role = 'admin';
        $stmt->bind_param('ss', $email, $role);
        $stmt->execute();
        $stmt->bind_result($userId, $hash);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            $_SESSION[ADMIN_SESSION_KEY] = true;
            $_SESSION['admin_user_id'] = $userId;
            redirect('dashboard.php');
        }
        $errors[] = 'Email atau password tidak valid.';
        $stmt->close();
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Admin - PlayPal Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/template/AdminLTE-master/AdminLTE-master/dist/css/adminlte.min.css">
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body class="hold-transition login-page" style="background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);">
<div class="login-box">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>Play</b>Pal</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Masuk untuk mengelola dashboard transaksi.</p>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-text">
                        <span class="bi bi-envelope"></span>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-text">
                        <span class="bi bi-lock"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                    </div>
                </div>
            </form>
            <p class="mt-3 text-muted text-center">Gunakan akun admin untuk mengakses dashboard.</p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/dist/js/OverlayScrollbars.min.js"></script>
<script src="assets/template/AdminLTE-master/AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>
