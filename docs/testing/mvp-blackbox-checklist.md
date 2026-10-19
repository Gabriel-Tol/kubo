# Checklist Black-box MVP Bolu Hangat

Catat versi browser, tanggal, akun uji, hasil, dan bukti screenshot untuk setiap skenario. Jalankan skenario admin menggunakan akun admin demo. Jangan gunakan uang atau rekening nyata.

## Pelanggan dan katalog

1. Buka beranda pada lebar 360 px; navigasi, hero, dan CTA tidak meluber horizontal.
2. Buka beranda pada 768 px dan 1280 px; kolom dan jarak tetap terbaca.
3. Cari produk dengan nama lengkap dan sebagian kata; hasil sesuai.
4. Cari kata acak; tampil empty state dan tautan kembali ke katalog.
5. Filter kategori, rentang harga, dan urut nama; hasil mencerminkan semua filter.
6. Buka detail produk aktif; komposisi, alergen, varian, harga, dan ilustrasi terlihat.
7. Buka slug produk tidak dikenal; halaman memberi respons 404.
8. Tambah satu varian ke keranjang sebagai tamu; muat ulang dan pastikan item masih ada.
9. Kirim jumlah nol, jumlah melebihi stok, varian nonaktif, dan catatan >150 karakter; server menolak perubahan tidak valid.
10. Hapus item terakhir; tampilkan empty state keranjang.
11. Checkout sebagai tamu; diarahkan ke login, lalu lanjutkan dengan akun.

## Akun dan pesanan

12. Daftar dengan password 7 karakter; akun tidak dibuat dan error menjelaskan batas minimal.
13. Daftar dengan data valid; profil pelanggan dibuat dan sesi aktif.
14. Simpan profil dan alamat dengan zona aktif; data muncul kembali setelah reload.
15. Coba menghapus alamat milik akun lain; akses ditolak.
16. Checkout pickup; ongkir nol dan total sama dengan subtotal.
17. Checkout delivery Zona B; ongkir sesuai konfigurasi dan total dihitung server.
18. Ubah total/harga di request browser; pesanan tetap memakai harga database.
19. Pilih slot penuh/tidak aktif, tanggal lampau, alamat di luar zona, atau stok kurang; checkout ditolak tanpa membuat pesanan.
20. Buat pesanan valid; nomor unik, item/harga tersnapshot, dan status menunggu pembayaran.
21. Unggah PDF, berkas palsu ber-ekstensi JPG, dan gambar >2 MB; semuanya ditolak.
22. Unggah JPG/PNG valid; status tetap menunggu verifikasi dan bukti hanya dapat dilihat pemilik.
23. Akun lain membuka detail pesanan atau URL bukti; respons 404/ditolak.
24. Admin menolak bukti tanpa alasan; perubahan ditolak. Dengan alasan, pelanggan dapat unggah ulang sebelum batas waktu.
25. Admin menerima bukti saat stok cukup; status terkonfirmasi dan stok berkurang satu kali.
26. Coba menerima bukti atau memindahkan status yang sama dua kali; stok tidak berkurang ulang.
27. Tolak penerimaan bila stok tidak cukup; status dan stok tidak berubah.
28. Batalkan pesanan sebelum konfirmasi dan setelah konfirmasi; stok hanya dipulihkan pada kasus kedua.
29. Buka pesanan yang batas bayarnya lewat; status berubah menjadi dibatalkan dan bukti baru ditolak.
30. Periksa riwayat dan timeline; waktu, status, metode pickup/delivery, serta total tampil konsisten.

## Admin, aksesibilitas, dan hosting

31. Tamu membuka `/admin`; diarahkan ke login admin.
32. Akun customer membuka daftar produk, kategori, dashboard, atau pesanan admin; mendapat 403.
33. Admin menambah/mengubah produk dengan gambar JPG/PNG valid; produk tampil di katalog.
34. Unggah gambar palsu, tipe tidak didukung, dan gambar >3 MB; aplikasi menolak upload.
35. Arsipkan produk yang pernah dipesan; produk hilang dari katalog namun snapshot pesanan tetap.
36. Tambah dan ubah kategori; kategori baru tersedia di form produk.
37. Hapus kategori yang dipakai produk; aplikasi menolak. Hapus kategori kosong; aplikasi berhasil.
38. Uji semua form dengan keyboard; fokus terlihat, label terhubung, dan tombol dapat dijalankan tanpa hover.
39. Pastikan status pesanan memakai label teks dan bukan warna saja.
40. Pada hosting, akses langsung ke file/folder privat dalam panduan deployment; tidak ada source, rahasia, atau bukti yang terlihat.
41. Uji login/logout, sesi setelah reload, aset, upload, database, dan base URL HTTPS pada domain hosting.