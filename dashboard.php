<?php
require_once __DIR__ . '/helpers.php';
require_admin();
$db = db_connect();

list($startDate, $endDate) = current_month_bounds();

$stats = [
    'total_transactions' => 0,
    'omzet' => 0.0,
    'profit' => 0.0,
    'registered_users' => 0,
];

$stmt = $db->prepare('SELECT COUNT(*) AS total_transactions, COALESCE(SUM(total_amount),0) AS omzet, COALESCE(SUM(profit),0) AS profit FROM transactions WHERE created_at BETWEEN ? AND ?');
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$stmt->bind_result($stats['total_transactions'], $stats['omzet'], $stats['profit']);
$stmt->fetch();
$stmt->close();

$stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?');
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$stmt->bind_result($stats['registered_users']);
$stmt->fetch();
$stmt->close();

$chartLabels = [];
$chartData = [];
$query = 'SELECT DATE(created_at) AS tanggal, COALESCE(SUM(total_amount), 0) AS omzet FROM transactions WHERE created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY tanggal ASC';
$stmt = $db->prepare($query);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$stmt->bind_result($tanggal, $omzetRow);
$daily = [];
while ($stmt->fetch()) {
    $daily[$tanggal] = $omzetRow;
}
$stmt->close();

$period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime(date('Y-m-d', strtotime($endDate . ' +1 day'))));
foreach ($period as $date) {
    $label = $date->format('Y-m-d');
    $chartLabels[] = $label;
    $chartData[] = isset($daily[$label]) ? floatval($daily[$label]) : 0;
}

$statusList = ['Success', 'Pending', 'Refund', 'Paid', 'Waiting for Approval'];
$placeholders = implode(',', array_fill(0, count($statusList), '?'));
$sql = "SELECT t.id, u.email AS user_email, p.name AS product_name, t.status, t.payment_method, t.total_amount, t.profit, t.created_at FROM transactions t LEFT JOIN users u ON t.user_id = u.id LEFT JOIN products p ON t.product_id = p.id WHERE t.status IN ($placeholders) ORDER BY t.created_at DESC LIMIT 10";
$stmt = $db->prepare($sql);
$types = str_repeat('s', count($statusList));
$stmt->bind_param($types, ...$statusList);
$stmt->execute();
$result = $stmt->get_result();
$recentTransactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Transaksi - PlayPal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/template/AdminLTE-master/AdminLTE-master/dist/css/adminlte.min.css">
    <link href="assets/css/theme.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Dashboard</a>
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
                        <a href="dashboard.php" class="nav-link active">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="adminPrvldgCfg.php" class="nav-link">
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
                        <h1 class="m-0">Dashboard Transaksi</h1>
                        <p class="text-muted">Ringkasan transaksi bulan berjalan.</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= number_format($stats['total_transactions']) ?></h3>
                                <p>Total Transaksi</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= format_idr($stats['omzet']) ?></h3>
                                <p>Jumlah Omzet</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= format_idr($stats['profit']) ?></h3>
                                <p>Jumlah Profit</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= number_format($stats['registered_users']) ?></h3>
                                <p>User Terdaftar</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Grafik Omzet</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="omzetChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">10 Transaksi Terbaru</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Produk</th>
                                            <th>Status</th>
                                            <th>Metode</th>
                                            <th>Omzet</th>
                                            <th>Profit</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentTransactions)): ?>
                                            <tr><td colspan="8" class="text-center">Tidak ada transaksi</td></tr>
                                        <?php else: foreach ($recentTransactions as $index => $tx): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($tx['user_email'] ?: 'Guest') ?></td>
                                                <td><?= htmlspecialchars($tx['product_name'] ?: '-') ?></td>
                                                <td><span class="badge <?= get_status_badge_class($tx['status']) ?>"><?= htmlspecialchars($tx['status']) ?></span></td>
                                                <td><?= htmlspecialchars($tx['payment_method']) ?></td>
                                                <td><?= format_idr($tx['total_amount']) ?></td>
                                                <td><?= format_idr($tx['profit']) ?></td>
                                                <td><?= htmlspecialchars($tx['created_at']) ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
<script>
const omzetChart = document.getElementById('omzetChart');
new Chart(omzetChart, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels, JSON_HEX_TAG) ?>,
        datasets: [{
            label: 'Omzet Harian',
            data: <?= json_encode($chartData, JSON_HEX_TAG) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            tension: 0.25,
            fill: true,
            pointRadius: 3,
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value) }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
</body>
</html>
