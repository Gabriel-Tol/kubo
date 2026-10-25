# Spesifikasi Desain MVP Web Bolu Hangat (KUBO)

**Tanggal:** 2026-10-18  
**Status:** Draf untuk ditinjau  
**Acuan:** `prd.md` dan spesifikasi UI di dalamnya. PRD menyebut `My Best.pdf` sebagai referensi visual; gambar halaman PDF belum dapat diekstrak secara langsung di lingkungan ini.

## 1. Tujuan dan Sasaran

Membangun situs e-commerce untuk tugas kuliah dengan satu outlet Bolu Hangat (KUBO) di Pontianak. Aplikasi mendemonstrasikan alur pemesanan pelanggan dan operasional admin, lalu dapat diunggah oleh mahasiswa ke InfinityFree. Produk, alamat, kontak, dan jadwal pada MVP adalah data contoh sampai dikonfirmasi pemilik. Pembayaran menggunakan QRIS statis dan perlu diverifikasi manual.

Sasaran berhasil bila pelanggan dapat melihat katalog, memesan, mengirim bukti transfer manual, dan melacak status; admin dapat mengelola katalog serta memproses pesanan; total dan stok tetap konsisten; serta akses pelanggan dan admin terpisah. Cakupan mengikuti MVP di PRD. Kuota produksi harian, grafik laporan, notifikasi email, payment gateway, QRIS, GPS, voucher, dan banyak outlet berada di luar MVP.

## 2. Arsitektur

Gunakan satu aplikasi CodeIgniter 4 MVC dengan MySQL/MariaDB (InnoDB, `utf8mb4`) dan tampilan yang dirender di server. Controller dibuat tipis; aturan harga, checkout, slot, bukti pembayaran, stok, status pesanan, dan kedaluwarsa ditempatkan dalam service. Model menangani persistensi. Transaksi database menjaga perubahan status dan stok tetap atomik.

Gunakan CodeIgniter Shield untuk autentikasi pelanggan/admin dan grup peran. Frontend memakai CSS biasa dan JavaScript ringan tanpa proses build Node. JavaScript hanya menangani interaksi tambahan seperti pengubah jumlah, penghitung karakter, tab, dan hitung mundur pembayaran; validasi server tetap menjadi sumber kebenaran.

## 3. Alur MVP dan Aturan Bisnis

### Pelanggan

1. Melihat beranda dan katalog produk aktif; mencari, memfilter, dan mengurutkan produk.
2. Memilih varian aktif, jumlah, dan catatan opsional maksimal 150 karakter.
3. Menyimpan keranjang tamu di sesi dan keranjang akun di database; gabungkan keranjang tamu setelah login.
4. Wajib login untuk checkout. Server memvalidasi ulang stok, harga, zona antar, tanggal, dan slot waktu.
5. Membuat snapshot pesanan berisi data pelanggan, nama/varian/harga produk, alamat, biaya, total, dan batas pembayaran. Ambil di toko tanpa ongkir; antar memakai zona lokal yang terdaftar.
6. Mengunggah bukti JPG/PNG maksimal 2 MB. Bukti yang terkirim berstatus menunggu pemeriksaan, bukan berarti pembayaran diterima.
7. Melihat konfirmasi, riwayat, dan linimasa status yang hanya dapat diakses pemilik pesanan.

### Admin

Sediakan login admin terpisah dan lindungi semua rute `/admin/*`. Admin mengelola kategori, produk, varian, foto, dan arsip; memeriksa bukti; serta mengubah status sesuai transisi yang diizinkan. Penolakan bukti wajib berisi alasan. Pengarsipan produk mempertahankan riwayat pesanan.

### Pembayaran, stok, dan kedaluwarsa

Pesanan dimulai dengan status **Menunggu Pembayaran**. Bukti baru dianggap diterima setelah diperiksa admin. Saat admin menerima pembayaran, sistem memeriksa ulang stok dan dalam satu transaksi menerima bukti, mengurangi stok, mengonfirmasi pesanan, serta mencatat log status. Jika bukti ditolak, pesanan tetap menunggu pembayaran dan pelanggan boleh mengunggah ulang sebelum tenggat. Pembatalan hanya mengembalikan stok jika stok sebelumnya sudah dipotong untuk pesanan tersebut.

Gunakan transisi status PRD, termasuk status pemenuhan yang berbeda untuk ambil dan antar. Simpan nama dan harga produk sebagai snapshot. Karena hosting bersama tidak menyediakan cron CLI, proses kedaluwarsa saat halaman pembayaran, detail pesanan, atau dashboard admin diakses. Akibatnya, status di database dapat terlambat berubah jika tidak ada permintaan ke aplikasi; saat diperiksa, pesanan lewat tenggat ditampilkan kedaluwarsa dan pembatalan sistem dicatat.

## 4. Model Data

Entitas MVP: pengguna/grup Shield; profil dan alamat pelanggan; zona antar; kategori; produk; varian; foto produk; keranjang dan item keranjang; slot waktu; pesanan; item pesanan; bukti pembayaran; log status; serta pengaturan toko key/value. Pesanan menyimpan snapshot pelanggan dan alamat. Item pesanan menyimpan snapshot nama/varian, harga satuan, jumlah, catatan, dan subtotal baris. Simpan nilai rupiah sebagai bilangan bulat dan gunakan zona waktu `Asia/Jakarta`.

Jangan menambah entitas kuota harian atau penyesuaian stok v1.1 kecuali implementasi MVP menunjukkan kebutuhan khusus.

## 5. Layar dan Arah Visual

Layar pelanggan mengikuti PRD: beranda, katalog, detail produk, keranjang, checkout, pembayaran, konfirmasi, pelacakan/riwayat, masuk/daftar, profil/alamat. Layar admin: masuk, dashboard, produk/kategori, daftar dan detail pesanan.

Ikuti palet handoff: kanvas `#FCF8F2`, permukaan putih, kakao `#34231D`, terakota `#A64220`, teks sekunder `#74645A`, sukses `#256046`, dan error `#A32929`. Gunakan Lora untuk judul dan Inter untuk teks antarmuka. Gunakan skala jarak 4/8/16/24/32/48 px, target aksi minimal 44 px, fokus keyboard yang terlihat, dan label teks pada semua status.

Target responsif 360, 768, dan 1280 px: katalog dua kolom di ponsel, tiga kolom di tablet, filter samping di desktop. Checkout bertumpuk di ponsel dan menampilkan formulir serta ringkasan berdampingan di desktop. Admin memakai sidebar 220 px di desktop dan navigasi ringkas di layar kecil. Rancang state kosong, loading, validasi, stok habis, area tak terlayani, slot penuh/lewat, pembayaran kedaluwarsa, serta unggahan yang salah secara eksplisit.

## 6. Keamanan dan Privasi

Terapkan filter peran pada semua rute admin dan pemeriksaan kepemilikan pada setiap permintaan pesanan/bukti pelanggan. Aktifkan CSRF, hash kata sandi, regenerasi ID sesi setelah login, pembatasan percobaan login, output escaping, dan Query Builder/prepared statement. Validasi MIME dari isi unggahan di server, batasi 2 MB, acak nama berkas, simpan bukti di `writable/` di luar web root, dan sajikan melalui controller yang memeriksa izin.

Simpan kredensial di `.env`, matikan debug di production, dan gunakan HTTPS serta cookie sesi Secure, HttpOnly, SameSite=Lax saat HTTPS aktif. Kumpulkan hanya data pribadi yang disebut PRD. Rekening demo tidak boleh ditampilkan sebagai tujuan pembayaran nyata.

## 7. Verifikasi dan Deploy

Cakupan verifikasi PRD meliputi unit test hitung harga/ongkir, stok, dan transisi status; feature test checkout, otorisasi, dan pemeriksaan pembayaran; serta pengujian black-box skenario pelanggan/admin. Periksa layout responsif dan target aksesibilitas pada 360/768/1280 px. Pesan validasi harus menyebut sebab dan tindakan perbaikan, bukan mengandalkan warna.

Jalankan Composer secara lokal dan sertakan `vendor/` hasil instalasi pada paket hosting. Atur database hosting lewat panel/phpMyAdmin dan jangan simpan kredensial dalam source. Atur web root/rewrite InfinityFree agar hanya `public/` yang dapat diakses web. Uji bahwa `app/`, `vendor/`, `writable/`, `.env`, dan unggahan privat tidak dapat diakses langsung. Sebelum demo, uji HTTPS, unggahan, database, sesi, dan routing di hosting sebenarnya.

## 8. Asumsi dan Keputusan Terbuka

Produk, harga, zona, slot, alamat, jam buka, kontak, dan rekening pada PRD adalah data seed/demo. Pemilik belum memastikan batas pembayaran, lead time slot, zona antar final, kebijakan batal/refund, dan identitas toko final. Buat nilai tersebut mudah dikonfigurasi bila praktis dan beri label data demo dengan jelas. MVP tidak memakai email atau integrasi pembayaran eksternal.
