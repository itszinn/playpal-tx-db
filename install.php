<?php
require_once __DIR__ . '/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_errno) {
    die('Gagal terhubung ke database: ' . $mysqli->connect_error);
}

$mysqli->query('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
if ($mysqli->errno) {
    die('Gagal membuat database: ' . $mysqli->error);
}

$mysqli->close();
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    die('Gagal terhubung ke database baru: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');

$db->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','member','guest') NOT NULL DEFAULT 'guest',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_product_id VARCHAR(191) NOT NULL,
    name VARCHAR(191) NOT NULL,
    supplier_price DECIMAL(12,2) NOT NULL,
    price_guest DECIMAL(12,2) NOT NULL,
    price_silver DECIMAL(12,2) NOT NULL,
    price_gold DECIMAL(12,2) NOT NULL,
    price_platinum DECIMAL(12,2) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY supplier_product_id_unique (supplier_product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    product_id INT DEFAULT NULL,
    status ENUM('Success','Pending','Refund','Paid','Waiting for Approval') NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(14,2) NOT NULL,
    cost_amount DECIMAL(14,2) NOT NULL,
    admin_fee DECIMAL(12,2) NOT NULL,
    profit DECIMAL(14,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    admin_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$adminEmail = 'admin@playpal.local';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $adminEmail);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmtInsert = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?);');
    $role = 'admin';
    $name = 'Admin PlayPal';
    $stmtInsert->bind_param('ssss', $name, $adminEmail, $adminPassword, $role);
    $stmtInsert->execute();
    $stmtInsert->close();
}
$stmt->close();

$methods = ['Bank Transfer', 'Virtual Account', 'E-Wallet', 'QRIS', 'Retail Store'];
foreach ($methods as $method) {
    $stmtMethod = $db->prepare('INSERT IGNORE INTO payment_methods (name, admin_fee) VALUES (?, 0.00)');
    $stmtMethod->bind_param('s', $method);
    $stmtMethod->execute();
    $stmtMethod->close();
}

$db->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install PlayPal Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4">Instalasi Selesai</h1>
            <p>Database dan tabel berhasil dibuat.</p>
            <p>Akun admin default:</p>
            <ul>
                <li>Email: <strong>admin@playpal.local</strong></li>
                <li>Password: <strong>admin123</strong></li>
            </ul>
            <p>Silakan buka <a href="index.php">Login Admin</a> untuk mulai menggunakan dashboard.</p>
        </div>
    </div>
</div>
</body>
</html>
