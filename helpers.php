<?php
require_once __DIR__ . '/config.php';

function db_connect() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('Database connection failed: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function is_admin_logged_in() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function format_idr($value) {
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function current_month_bounds() {
    $start = date('Y-m-01 00:00:00');
    $end = date('Y-m-t 23:59:59');
    return [$start, $end];
}

function compute_prices_from_supplier($supplierPrice) {
    $supplierPrice = floatval($supplierPrice);
    return [
        'guest' => round($supplierPrice * 1.03, 2),
        'silver' => round($supplierPrice * 1.022, 2),
        'gold' => round($supplierPrice * 1.015, 2),
        'platinum' => round($supplierPrice * 1.008, 2),
    ];
}

function get_payment_methods() {
    return [
        'Bank Transfer',
        'Virtual Account',
        'E-Wallet',
        'QRIS',
        'Retail Store',
    ];
}

function get_status_badge_class($status) {
    return match ($status) {
        'Success' => 'badge-success',
        'Pending' => 'badge-pending',
        'Refund' => 'badge-refund',
        'Paid' => 'badge-paid',
        'Waiting for Approval' => 'badge-wfa',
        default => 'badge-secondary',
    };
}

function fetch_supplier_data() {
    if (empty(SUPPLIER_API_URL) || strpos(SUPPLIER_API_URL, 'example.com') !== false) {
        return null;
    }

    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $result = @file_get_contents(SUPPLIER_API_URL, false, $context);
    if ($result === false) {
        return null;
    }

    $data = json_decode($result, true);
    return is_array($data) ? $data : null;
}
