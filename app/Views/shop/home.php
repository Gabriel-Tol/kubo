<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<section class="hero-menu">
    <div class="hero-note"><span>Bolu rumahan · Pontianak</span><span>Ambil atau antar</span></div>
    <div class="hero-main">
    <div class="hero-card">
        <p class="eyebrow">Bolu Hangat KUBO</p>
        <h1>Untuk meja<br>bersama</h1>
        <span class="menu-divider" aria-hidden="true">◆</span>
        <p class="hero-description">Bolu lembut dibuat untuk menemani cerita di rumah, di hari biasa, dan di hari istimewa.</p>
        <a class="button" href="<?= site_url('katalog') ?>">Belanja bolu</a>
        <p class="sample-note">Ilustrasi, menu, dan harga pada situs ini adalah data contoh.</p>
    </div>
    </div>
    <div class="hero-bottom"><span>01 / Katalog bolu</span><span>Dipanggang untuk dinikmati bersama</span></div>
</section>
<section class="featured-section">
    <div class="section-heading"><div><p class="eyebrow">Dari dapur KUBO</p><h2>Favorit dari oven</h2></div><a class="text-link" href="<?= site_url('katalog') ?>">Lihat semua bolu</a></div>
    <?php if ($featured === []): ?><p class="empty-state">Menu sedang disiapkan. Silakan kembali sebentar lagi.</p><?php else: ?>
    <div class="product-grid"><?php foreach ($featured as $product): ?><?= view('shop/_product', ['product' => $product]) ?><?php endforeach ?></div>
    <?php endif ?>
</section>
<section class="receiving-section">
    <div class="receiving-title"><p class="eyebrow">Pesananmu</p><h2>Siap dinikmati<br>di mana saja</h2></div>
    <div class="receiving-copy"><p>Ambil langsung di toko atau pilih antar lokal di area Pontianak. Tentukan tanggal dan waktu yang paling pas saat checkout.</p><a class="text-link" href="<?= site_url('katalog') ?>">Mulai pilih bolu</a></div>
    <div class="receiving-illustration" aria-label="Ilustrasi kotak bolu KUBO">
        <svg viewBox="0 0 440 250" role="img" aria-labelledby="box-title"><title id="box-title">Ilustrasi kotak bolu KUBO</title><path fill="#d7a674" d="M45 93 220 32l175 61-175 65z"/><path fill="#b87950" d="M45 93v104l175 42V158z"/><path fill="#92553a" d="M220 158v81l175-42V93z"/><path fill="#fcf8f2" d="m114 84 106-38 105 38-105 38z"/><path fill="#34231d" d="m180 79 40-14 40 14-40 15z"/><path fill="#34231d" d="M202 94h36v20h-36z"/><text x="220" y="108" text-anchor="middle" fill="#fcf8f2" font-size="13" font-family="Arial">KUBO</text><path fill="#f2dfc9" d="M83 117v63l105 24v-63z" opacity=".8"/><path fill="#cf9a6b" d="M257 173v33l92-23v-33z"/></svg>
        <p>Gambar ilustrasi kemasan</p>
    </div>
</section>
<?= $this->endSection() ?>
