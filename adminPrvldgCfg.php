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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/template/AdminLTE-master/AdminLTE-master/dist/css/adminlte.min.css">
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="bi bi-list"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout <i class="bi bi-box-arrow-right"></i></a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link text-decoration-none">
            <span class="brand-text fs-5 ms-3">PlayPal Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="adminPrvldgCfg.php" class="nav-link active">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Admin Produk</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Admin Produk</h1>
                        <p class="text-muted">Mengelola produk, harga, dan sinkronisasi supplier.</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Tambah Produk Baru</h3>
                            </div>
                            <div class="card-body">
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
                                <p class="mt-3 text-muted">Produk akan diperbarui otomatis dari supplier setiap 1 menit melalui <code>supplier_sync.php</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Produk</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
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
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <p class="mb-1"><strong>Catatan:</strong> Set <code>SUPPLIER_API_URL</code> di <code>config.php</code> lalu jalankan <code>supplier_sync.php?token=...</code> setiap 1 menit.</p>
                            <p class="mb-0">Semua metode pembayaran saat ini memakai biaya administrasi yang sama.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/dist/js/OverlayScrollbars.min.js"></script>
<script src="assets/template/AdminLTE-master/AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>
