<?php
require_once __DIR__ . '/helpers.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PlayPal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/template/AdminLTE-master/AdminLTE-master/dist/css/adminlte.min.css">
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-panel">
        <?php
        $pageTitle = 'Settings';
        include __DIR__ . '/topbar.php';
        ?>
        <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Settings</h1>
                        <p class="text-muted">Placeholder page for system settings.</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-secondary">
                    <div class="card-body">
                        <p>This area will display system configuration options.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/dist/js/OverlayScrollbars.min.js"></script>
<script src="assets/template/AdminLTE-master/AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>
