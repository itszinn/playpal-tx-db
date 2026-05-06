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

        $liClass = 'nav-item';
        if ($hasChildren && $childActive) {
            $liClass .= ' menu-open';
        }

        $aHref = $hasChildren ? '#' : (isset($item['href']) ? htmlspecialchars($item['href']) : '#');
        $aClass = 'nav-link';
        if ($isActive || $childActive) {
            $aClass .= ' active';
        }

        ?>
        <li class="<?= htmlspecialchars($liClass) ?>">
            <a href="<?= $aHref ?>" class="<?= htmlspecialchars($aClass) ?>" title="<?= htmlspecialchars($item['label']) ?>">
                <i class="nav-icon bi <?= htmlspecialchars($item['icon']) ?>"></i>
                <p>
                    <?= htmlspecialchars($item['label']) ?>
                    <?php if ($hasChildren): ?>
                        <i class="nav-arrow bi bi-chevron-right"></i>
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

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark" data-lte-toggle="sidebar">
    <div class="sidebar-brand">
        <a href="dashboard.php" class="brand-link">
            <span class="brand-image opacity-75 shadow d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.08);">
                <i class="bi bi-controller"></i>
            </span>
            <span class="brand-text fw-light ms-2">PlayPal</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="navigation"
                aria-label="Main navigation"
                data-accordion="false"
                id="navigation"
            >
                <?php renderMenu($menuItems, $currentPage); ?>
            </ul>
        </nav>
    </div>
</aside>

