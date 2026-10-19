# Deploy ke InfinityFree

Panduan ini menyiapkan demo tugas kuliah. Uji di domain sementara sebelum membagikannya. InfinityFree memakai `htdocs` sebagai document root dan membatasi akses PHP ke folder situs melalui `open_basedir`; karena itu seluruh proyek CI4, termasuk `vendor/` dan `writable/`, harus berada di dalam `htdocs`. Folder sensitif diblokir oleh `.htaccess` di root dan `writable/.htaccess`.

## Persiapan lokal

1. Pastikan PHP lokal 8.2+ dan ekstensi `intl`, `mbstring`, `mysqli`, `fileinfo`, serta `gd` tersedia.
2. Jalankan `composer install --no-dev --optimize-autoloader` dari root proyek.
3. Buat database MySQL lokal, salin `env` menjadi `.env`, lalu atur `CI_ENVIRONMENT = production`, `app.baseURL`, `database.default.*`, dan encryption key acak.
4. Jalankan `php spark migrate` dan `php spark db:seed DemoShopSeeder`. Untuk admin lokal, gunakan `DEMO_ADMIN_EMAIL` dan `DEMO_ADMIN_PASSWORD` dengan `DemoAdminSeeder`. Akun admin hosting dibuat setelah upload melalui langkah khusus di bawah.
5. Ekspor skema dan data katalog dari database, kemudian siapkan database kosong di panel InfinityFree dan impor melalui phpMyAdmin. Pastikan konfigurasi `.env` produksi menunjuk database hosting.

## Upload dan konfigurasi

1. Upload isi proyek ke `htdocs` melalui FTP, termasuk `vendor/`, `public/`, `app/`, `writable/`, `env`, dan `.htaccess` root. File tersembunyi seperti `.htaccess` perlu ikut terunggah. Jangan mengunggah `.env` lokal.
2. Buat `.env` baru di root `htdocs`. Isi base URL HTTPS dan data database persis seperti yang diberikan panel. Set `CI_ENVIRONMENT = production`; jangan tampilkan debug detail ke pengunjung.
3. Atur permission `writable/` agar PHP dapat menulis cache, log, sesi, serta `uploads/payment-proofs`. Pertahankan file `writable/.htaccess` yang menolak semua akses HTTP langsung.
4. Aktifkan sertifikat HTTPS di panel sebelum memakai URL HTTPS. Uji beranda, aset CSS, login, gambar, sesi, dan koneksi database.
5. Untuk admin, buat akun melalui halaman daftar, lalu tambahkan akun tersebut ke grup admin lewat phpMyAdmin:

```sql
INSERT INTO auth_groups_users (user_id, `group`, created_at)
SELECT id, 'admin', NOW()
FROM users
WHERE email = 'email-admin-anda@example.com'
  AND NOT EXISTS (
    SELECT 1 FROM auth_groups_users g
    WHERE g.user_id = users.id AND g.`group` = 'admin'
  );
```

Ganti email dengan akun milik Anda. Logout lalu login kembali dan pastikan halaman `/admin` terbuka. Jangan membagikan akses phpMyAdmin.

## Pemeriksaan keamanan dan batasan

- Pastikan permintaan langsung ke `/.env`, `/app/`, `/vendor/`, `/writable/`, `/writable/uploads/payment-proofs/`, `/composer.json`, dan `/spark` menghasilkan 403/404, bukan isi file.
- Bukti transfer disimpan privat dan hanya diunduh lewat halaman pesanan pemiliknya. Gambar katalog yang diunggah admin memang berada di `public/assets/images/uploads/`.
- Jangan gunakan data pembayaran demo untuk transaksi nyata. Ganti keterangan, gambar contoh, kontak, zona, slot, dan produk sebelum presentasi.
- Hosting gratis dapat membatasi versi PHP, ekstensi, ukuran unggahan, proses latar, dan koneksi. Aplikasi mensyaratkan PHP 8.2+; cek versi aktual pada panel/akun sebelum upload. Kedaluwarsa pesanan diperiksa saat halaman terkait dibuka, sehingga cron opsional bukan prasyarat demo.
- Deployment belum diuji pada akun InfinityFree pengguna. Tandai berhasil hanya setelah checklist manual dijalankan pada domain tersebut.

## Backup dan rollback

Sebelum perubahan, ekspor database melalui phpMyAdmin dan unduh salinan `app/`, `.env`, `public/assets/images/uploads/`, serta `writable/uploads/` melalui FTP. Untuk rollback, pulihkan file versi sebelumnya dan impor kembali dump database yang cocok. Jangan menghapus database produksi sebelum backup dapat dibuka.

Referensi hosting: [InfinityFree: open_basedir](https://forum.infinityfree.com/t/open-basedir/107378), [InfinityFree: folder htdocs dan pembatasan akses](https://forum.infinityfree.com/t/permissions/92456).