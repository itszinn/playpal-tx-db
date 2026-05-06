<?php
$pageTitle = $pageTitle ?? 'PlayPal Dashboard';
?>
<nav class="main-header navbar navbar-expand">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link text-white" data-widget="pushmenu" href="#" role="button"><i class="bi bi-list"></i></a>
        </li>
        <li class="nav-item d-flex align-items-center ms-2">
            <a href="dashboard.php" class="navbar-brand text-white d-flex align-items-center gap-2 mb-0">
                <span class="brand-icon"><i class="bi bi-controller"></i></span>
                <span class="brand-name">PlayPal</span>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item d-none d-md-block me-3">
            <span class="navbar-text text-white small"><?= htmlspecialchars($pageTitle) ?></span>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="#" role="button" title="Fullscreen"><i class="bi bi-arrows-fullscreen"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="settings.php" role="button" title="Settings"><i class="bi bi-gear"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="logout.php" role="button" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
        </li>
    </ul>
</nav>
