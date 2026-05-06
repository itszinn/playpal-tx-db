<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$menuItems = [
    ['href' => 'dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
    ['href' => 'adminPrvldgCfg.php', 'icon' => 'bi-box-seam', 'label' => 'Product'],
    ['href' => 'transactions.php', 'icon' => 'bi-receipt', 'label' => 'Transactions'],
    ['href' => 'reports.php', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
    ['href' => 'users.php', 'icon' => 'bi-people', 'label' => 'Users'],
    ['href' => 'settings.php', 'icon' => 'bi-gear', 'label' => 'Settings'],
];
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link text-decoration-none">
        <span class="brand-text fs-5 ms-3">PlayPal Admin</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php foreach ($menuItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($item['href']) ?>" class="nav-link<?= $currentPage === basename($item['href']) ? ' active' : '' ?>">
                            <i class="nav-icon bi <?= htmlspecialchars($item['icon']) ?>"></i>
                            <p><?= htmlspecialchars($item['label']) ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
