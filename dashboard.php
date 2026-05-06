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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PlayPal Admin</a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light" href="adminPrvldgCfg.php">Admin Produk</a>
            <a class="btn btn-light" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="mb-4">
        <h1 class="h3">Dashboard Transaksi Bulan Berjalan</h1>
        <p class="text-muted">Ringkasan statistik dan laporan transaksi.</p>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total Transaksi</h6>
                    <p class="display-6 mb-0"><?= number_format($stats['total_transactions']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Jumlah Omzet</h6>
                    <p class="display-6 mb-0"><?= format_idr($stats['omzet']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Jumlah Profit</h6>
                    <p class="display-6 mb-0"><?= format_idr($stats['profit']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>User Terdaftar</h6>
                    <p class="display-6 mb-0"><?= number_format($stats['registered_users']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Grafik Omzet</h5>
            <canvas id="omzetChart" height="120"></canvas>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">10 Transaksi Terbaru</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
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
                                <td><?= htmlspecialchars($tx['status']) ?></td>
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
