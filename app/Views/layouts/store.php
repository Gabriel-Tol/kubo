<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fcf8f2">
    <title><?= esc($title ?? 'Bolu Hangat KUBO') ?> · KUBO</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<header class="site-header">
    <a class="wordmark" href="<?= site_url('/') ?>" aria-label="KUBO, beranda"><img class="wordmark-logo" src="<?= base_url('assets/images/products/KUBO%20Kue%20Bolu%20Bakery%20Badge.webp') ?>" width="56" height="56" alt="Logo KUBO Kue Bolu"></a>
    <nav class="main-nav" aria-label="Navigasi utama">
        <a href="<?= site_url('/') ?>">Beranda</a>
        <a href="<?= site_url('katalog') ?>">Katalog</a>
        <a href="<?= site_url('pesanan') ?>">Pesanan</a>
        <a href="<?= site_url('keranjang') ?>">Keranjang</a>
        <a href="<?= site_url(auth()->loggedIn() ? 'akun' : 'login') ?>">Akun</a>
    </nav>
    <a class="button button-small" href="<?= site_url('katalog') ?>">Belanja bolu</a>
</header>
<main><?= $this->renderSection('content') ?></main>
<footer class="site-footer">
    <div><a class="footer-wordmark" href="<?= site_url('/') ?>">Bolu Hangat KUBO</a><p>Bolu rumahan untuk meja bersama. Pontianak.</p></div>
    <div class="footer-contact"><span>Ambil di toko</span><span>Antar area Pontianak</span></div>
</footer>
</body>
</html>
