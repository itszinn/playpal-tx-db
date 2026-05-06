<?php
session_start();

// Database connection untuk Laragon (MySQL default)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'playpal_tx_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Token untuk mengeksekusi sinkronisasi supplier secara aman
define('SUPPLIER_SYNC_TOKEN', 'replace-with-your-secret-token');

define('SUPPLIER_API_URL', 'https://supplier.example.com/api/products');

define('ADMIN_SESSION_KEY', 'playpal_admin');
