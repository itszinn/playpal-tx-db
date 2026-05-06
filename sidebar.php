<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$menuItems = [
    ['href' => 'dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
    ['label' => 'Order', 'icon' => 'bi-card-list', 'children' => [
        ['href' => 'transactions.php', 'icon' => 'bi-dot', 'label' => 'Kelola Order'],
        ['href' => '#', 'icon' => 'bi-dot', 'label' => 'Queue'],
        ['href' => 'reports.php', 'icon' => 'bi-dot', 'label' => 'Report'],
        ['href' => '#', 'icon' => 'bi-dot', 'label' => 'Report by Pengguna'],
        ['href' => '#', 'icon' => 'bi-dot', 'label' => 'Report by Provider'],
    ]],
    ['href' => '#', 'icon' => 'bi-heart-pulse', 'label' => 'Layanan'],
    ['href' => '#', 'icon' => 'bi-grid', 'label' => 'PID Kombo'],
    ['href' => '#', 'icon' => 'bi-person-badge', 'label' => 'Membership'],
    ['href' => '#', 'icon' => 'bi-chat-left-text', 'label' => 'Ulasan'],
    ['href' => '#', 'icon' => 'bi-arrow-repeat', 'label' => 'Resend Report Bulk'],
    ['label' => 'Promo', 'icon' => 'bi-tag', 'children' => [
        ['href' => '#', 'icon' => 'bi-dot', 'label' => 'Flash Sale Jadwal'],
        ['href' => '#', 'icon' => 'bi-dot', 'label' => 'Voucher Saldo'],
    ]],
    ['href' => '#', 'icon' => 'bi-cash-stack', 'label' => 'Pembayaran'],
];

function renderMenu(array $items, string $currentPage)
{
    foreach ($items as $item) {
        $hasChildren = isset($item['children']);
        $isActive = isset($item['href']) && $item['href'] !== '#' && $currentPage === basename($item['href']);
        $childActive = false;
        if ($hasChildren) {
            foreach ($item['children'] as $child) {
                if (isset($child['href']) && $child['href'] !== '#' && $currentPage === basename($child['href'])) {
                    $childActive = true;
                    break;
                }
            }
        }
        ?>
        <li class="nav-item<?= $hasChildren ? ' has-treeview' : '' ?><?= $childActive ? ' menu-open' : '' ?>">
            <a href="<?= isset($item['href']) ? htmlspecialchars($item['href']) : '#' ?>" class="nav-link<?= $isActive || $childActive ? ' active' : '' ?>" title="<?= htmlspecialchars($item['label']) ?>">
                <i class="nav-icon bi <?= htmlspecialchars($item['icon']) ?>"></i>
                <p class="nav-label">
                    <?= htmlspecialchars($item['label']) ?>
                    <?php if ($hasChildren): ?>
                        <i class="right bi bi-caret-down-fill"></i>
                    <?php endif; ?>
                </p>
            </a>
            <?php if ($hasChildren): ?>
                <ul class="nav nav-treeview">
                    <?php renderMenu($item['children'], $currentPage); ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php
    }
}
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link text-decoration-none justify-content-center">
        <span class="brand-icon"><i class="bi bi-controller"></i></span>
        <span class="brand-text">PlayPal</span>
    </a>
    <div class="sidebar">
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar nav-compact flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php renderMenu($menuItems, $currentPage); ?>
            </ul>
        </nav>
    </div>
</aside>
