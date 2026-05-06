# PlayPal Transaction Dashboard

Project sederhana untuk dashboard transaksi admin menggunakan PHP dan MySQL/Laragon.

## Fitur

- Halaman login admin sederhana
- Dashboard transaksi bulan berjalan:
  - Total transaksi
  - Total omzet
  - Total profit
  - Jumlah user terdaftar bulan ini
  - Grafik omzet harian pada bulan berjalan
  - Daftar 10 transaksi terbaru dengan status Success, Pending, Refund, Paid, Waiting for Approval
- Halaman admin produk di route `/adminPrvldgCfg.php`
  - Tambah / edit / hapus produk
  - Harga supplier + harga member guest/silver/gold/platinum otomatis
  - Produk memiliki `supplier_product_id`
  - API sinkronisasi produk dari supplier setiap 1 menit
- Pembayaran mendukung Bank Transfer, Virtual Account, E-Wallet, QRIS, Retail Store dengan biaya administrasi yang sama

## Setup

1. Salin folder ini ke `C:\laragon\www\playpal-tx-db`.
2. Buka Laragon dan jalankan Apache/MySQL.
3. Buka browser ke `http://localhost/playpal-tx-db/install.php`.
4. Tunggu pesan sukses instalasi.
5. Login admin di `http://localhost/playpal-tx-db/index.php`.

## Akun admin default

- Email: `admin@playpal.local`
- Password: `admin123`

> Silakan ubah password setelah instalasi jika diperlukan.

## Konfigurasi

- `config.php` berisi pengaturan database dan URL API supplier.
- `supplier_sync.php` dapat dijalankan secara manual atau dijadwalkan setiap 1 menit.

## Notes

- Pastikan `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME` disesuaikan jika berbeda.
- Jika ingin memakai layanan supplier asli, set `SUPPLIER_API_URL` di `config.php`.
- `install.php` membuat semua tabel dan akun admin default.
