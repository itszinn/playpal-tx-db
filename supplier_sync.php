<?php
require_once __DIR__ . '/helpers.php';

$token = $_GET['token'] ?? '';
if ($token !== SUPPLIER_SYNC_TOKEN) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid.']);
    exit;
}

$data = fetch_supplier_data();
if ($data === null) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Tidak dapat mengambil data supplier. Periksa SUPPLIER_API_URL.']);
    exit;
}

$db = db_connect();
$updated = 0;
foreach ($data as $item) {
    $supplierProductId = trim($item['product_id'] ?? '');
    $supplierPrice = floatval($item['supplier_price'] ?? 0);
    if ($supplierProductId === '' || $supplierPrice <= 0) {
        continue;
    }
    $prices = compute_prices_from_supplier($supplierPrice);
    $stmt = $db->prepare('UPDATE products SET supplier_price = ?, price_guest = ?, price_silver = ?, price_gold = ?, price_platinum = ?, updated_at = NOW() WHERE supplier_product_id = ?');
    $stmt->bind_param('ddddds', $supplierPrice, $prices['guest'], $prices['silver'], $prices['gold'], $prices['platinum'], $supplierProductId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $updated++;
    }
    $stmt->close();
}
$db->close();

echo json_encode(['status' => 'success', 'updated' => $updated]);
