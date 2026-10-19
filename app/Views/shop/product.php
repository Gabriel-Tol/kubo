<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= site_url('katalog') ?>">Katalog</a><span>/</span><a href="<?= site_url('katalog?category=' . rawurlencode($product['category_slug'] ?? '')) ?>"><?= esc($product['category_name'] ?? 'Bolu') ?></a><span>/</span><span aria-current="page"><?= esc($product['name']) ?></span></nav>
<section class="product-detail">
    <div class="detail-art"><img src="<?= base_url(esc($product['primary_image']['path'] ?? ('assets/images/products/' . $product['slug'] . '.svg'))) ?>" width="800" height="800" alt="Ilustrasi <?= esc($product['name']) ?>"></div>
    <div class="detail-copy"><p class="eyebrow"><?= esc($product['category_name'] ?? 'Bolu KUBO') ?></p><h1><?= esc($product['name']) ?></h1><p class="detail-description"><?= esc($product['description']) ?></p>
        <form method="post" action="<?= site_url('keranjang/tambah') ?>" class="add-form">
            <?= csrf_field() ?><input type="hidden" name="slug" value="<?= esc($product['slug']) ?>">
            <label for="variant">Pilih ukuran</label><select id="variant" name="variant_id" required><?php foreach ($product['variants'] as $variant): ?><option value="<?= (int) $variant['id'] ?>" data-price="<?= (int) $variant['price'] ?>" data-stock="<?= (int) $variant['stock'] ?>" <?= (int) $variant['stock'] < 1 ? 'disabled' : '' ?>><?= esc($variant['label']) ?> · Rp<?= number_format((int) $variant['price'], 0, ',', '.') ?><?= (int) $variant['stock'] < 1 ? ' · Habis' : '' ?></option><?php endforeach ?></select>
            <p class="availability" id="availability"><?= $product['available_stock'] > 0 ? 'Tersedia ' . (int) $product['available_stock'] . ' bolu' : 'Stok sedang habis' ?></p>
            <label for="quantity">Jumlah</label><input id="quantity" name="quantity" type="number" min="1" max="<?= max(1, (int) $product['available_stock']) ?>" value="1" required>
            <label for="note">Catatan atau ucapan <span>(opsional, maks. 150 karakter)</span></label><textarea id="note" name="note" maxlength="150" rows="3"></textarea><small id="note-count">0/150</small>
            <button class="button" type="submit" <?= $product['available_stock'] < 1 ? 'disabled' : '' ?>><?= $product['available_stock'] < 1 ? 'Stok habis' : 'Tambah ke keranjang' ?></button>
        </form>
        <div class="product-facts"><h2>Komposisi &amp; penyimpanan</h2><p><?= esc($product['composition'] ?? 'Komposisi belum tersedia.') ?></p><p><strong>Alergen:</strong> <?= esc($product['allergens'] ?? 'Informasi alergen belum tersedia.') ?></p><p><strong>Saran simpan:</strong> <?= esc($product['storage_note'] ?? 'Simpan dalam wadah tertutup.') ?></p></div>
    </div>
</section>
<script src="<?= base_url('assets/js/store.js') ?>" defer></script>
<?= $this->endSection() ?>