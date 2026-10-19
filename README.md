# Bolu Hangat KUBO

Aplikasi toko bolu berbasis CodeIgniter 4 untuk katalog, keranjang, checkout, unggah bukti pembayaran demo, pelacakan pesanan, dan operasional admin.

## Kebutuhan

- PHP 8.2+ dengan ekstensi `intl`, `mbstring`, `mysqli`, `fileinfo`, dan `gd`.
- Composer 2 dan MySQL/MariaDB untuk penggunaan aplikasi.
- Proyek menyediakan PHPUnit dan SQLite untuk pengujian otomatis.

## Menjalankan Lokal

1. Jalankan `composer install`.
2. Salin `env` menjadi `.env`, lalu atur `CI_ENVIRONMENT = development`, `app.baseURL`, dan kredensial database.
3. Buat database MySQL kosong, lalu jalankan `php spark migrate` dan `php spark db:seed DemoShopSeeder`.
4. Buat kredensial admin melalui environment lokal `DEMO_ADMIN_EMAIL` dan `DEMO_ADMIN_PASSWORD`, lalu jalankan `php spark db:seed DemoAdminSeeder`. Kata sandi minimal 12 karakter; jangan commit `.env`.
5. Jalankan `php spark serve` dan buka URL yang dicetak di terminal.

Akun admin tidak disediakan secara default. Instruksi pengaturan admin dan deployment ada di [docs/deployment/infinityfree.md](docs/deployment/infinityfree.md).

## Perintah Pengembangan

- `composer test` menjalankan seluruh unit dan feature test.
- `vendor/bin/phpunit tests/feature` menjalankan feature test saja.
- `php spark migrate:status` memeriksa status migrasi.

## Struktur Proyek

- `app/Controllers`, `app/Services`, `app/Views`: alur HTTP, aturan bisnis, dan halaman server-rendered.
- `app/Database/Migrations` dan `app/Database/Seeds`: skema dan data demo.
- `public/assets`: CSS, JavaScript, dan ilustrasi produk contoh.
- `tests/unit` dan `tests/feature`: pengujian otomatis.
- `writable`: sesi, cache, log, dan bukti pembayaran privat.

Semua gambar produk bawaan adalah ilustrasi contoh. Informasi pembayaran pada demo bukan rekening untuk transfer nyata.