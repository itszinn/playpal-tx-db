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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PlayPal Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Login Admin</h3>
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
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </div>
            <p class="text-center text-muted mt-3">Gunakan akun admin untuk mengakses dashboard.</p>
        </div>
    </div>
</div>
</body>
</html>
