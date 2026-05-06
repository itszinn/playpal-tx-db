<?php
require_once __DIR__ . '/helpers.php';
require_admin();
$db = db_connect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $supplierProductId = trim($_POST['supplier_product_id'] ?? '');
        $supplierPrice = floatval($_POST['supplier_price'] ?? 0);
        if ($name === '' || $supplierProductId === '' || $supplierPrice <= 0) {
            $message = 'Nama produk, supplier product ID, dan harga supplier wajib diisi.';
        } else {
            $prices = compute_prices_from_supplier($supplierPrice);
            $stmt = $db->prepare('INSERT INTO products (supplier_product_id, name, supplier_price, price_guest, price_silver, price_gold, price_platinum, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('ssddddd', $supplierProductId, $name, $supplierPrice, $prices['guest'], $prices['silver'], $prices['gold'], $prices['platinum']);
            $stmt->execute();
            $stmt->close();
            $message = 'Produk baru berhasil ditambahkan.';
        }
    }
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $supplierProductId = trim($_POST['supplier_product_id'] ?? '');
        $supplierPrice = floatval($_POST['supplier_price'] ?? 0);
        if ($id <= 0 || $name === '' || $supplierProductId === '' || $supplierPrice <= 0) {
            $message = 'Semua kolom edit produk wajib diisi.';
        } else {
            $prices = compute_prices_from_supplier($supplierPrice);
            $stmt = $db->prepare('UPDATE products SET supplier_product_id = ?, name = ?, supplier_price = ?, price_guest = ?, price_silver = ?, price_gold = ?, price_platinum = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('ssdddddi', $supplierProductId, $name, $supplierPrice, $prices['guest'], $prices['silver'], $prices['gold'], $prices['platinum'], $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Produk berhasil diperbarui.';
        }
    }
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Produk berhasil dihapus.';
        }
    }
    if ($action === 'sync') {
        $syncUrl = 'supplier_sync.php?token=' . urlencode(SUPPLIER_SYNC_TOKEN);
        $message = 'Sinkronisasi produk akan dijalankan. Klik <a href="' . htmlspecialchars($syncUrl) . '">di sini</a> untuk mengeksekusi sekarang.';
    }
}

$result = $db->query('SELECT * FROM products ORDER BY updated_at DESC');
$products = $result->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Produk - PlayPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body class="bg-surface">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">PlayPal Admin</a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light" href="dashboard.php">Dashboard</a>
            <a class="btn btn-light" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="mb-4">
        <h1 class="h3">Admin Produk</h1>
        <p class="text-muted">Di sini admin bisa menambah, mengelola, dan menghapus produk.</p>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <div class="mb-4">
        <div class="card shadow-sm preview-card">
            <div class="card-body">
                <h5 class="card-title">Tambah Produk Baru</h5>
                <form method="post" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Supplier Product ID</label>
                            <input type="text" name="supplier_product_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga Supplier</label>
                            <input type="number" step="0.01" name="supplier_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Simpan Produk</button>
                        <button type="submit" name="action" value="sync" class="btn btn-secondary">Sinkron Produk</button>
                    </div>
                </form>
                <p class="mt-3 text-muted">Produk akan diperbarui otomatis dari supplier setiap 1 menit melalui `supplier_sync.php`.</p>
            </div>
        </div>
    </div>
    <div class="card shadow-sm preview-card">
        <div class="card-body">
            <h5 class="card-title">Daftar Produk</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Supplier ID</th>
                            <th>Nama</th>
                            <th>Supplier</th>
                            <th>Guest</th>
                            <th>Silver</th>
                            <th>Gold</th>
                            <th>Platinum</th>
                            <th>Updated</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="10" class="text-center">Belum ada produk</td></tr>
                    <?php else: foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['supplier_product_id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= format_idr($product['supplier_price']) ?></td>
                            <td><?= format_idr($product['price_guest']) ?></td>
                            <td><?= format_idr($product['price_silver']) ?></td>
                            <td><?= format_idr($product['price_gold']) ?></td>
                            <td><?= format_idr($product['price_platinum']) ?></td>
                            <td><?= htmlspecialchars($product['updated_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" type="button" data-bs-toggle="collapse" data-bs-target="#edit-<?= $product['id'] ?>">Edit</button>
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?');">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-<?= $product['id'] ?>">
                            <td colspan="10">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label">Supplier Product ID</label>
                                            <input type="text" name="supplier_product_id" class="form-control" value="<?= htmlspecialchars($product['supplier_product_id']) ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nama Produk</label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Harga Supplier</label>
                                            <input type="number" step="0.01" name="supplier_price" class="form-control" value="<?= htmlspecialchars($product['supplier_price']) ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-success w-100">Simpan Perubahan</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 alert alert-info">
        <p class="mb-1"><strong>Catatan:</strong> Set `SUPPLIER_API_URL` di `config.php` lalu jalankan `supplier_sync.php?token=...` setiap 1 menit.</p>
        <p class="mb-0">Semua metode pembayaran saat ini memakai biaya administrasi yang sama.</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
