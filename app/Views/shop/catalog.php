<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<section class="catalog-head"><p class="eyebrow">Menu KUBO · Pontianak</p><h1>Katalog bolu</h1><p>Pilih rasa dan ukuran untuk meja di rumah.</p></section>
<section class="catalog-layout">
    <form class="filter-panel" method="get" action="<?= site_url('katalog') ?>">
        <label for="q">Cari nama bolu</label><input id="q" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Mis. pandan">
        <label for="category">Kategori</label><select id="category" name="category"><option value="">Semua bolu</option><?php foreach ($categories as $category): ?><option value="<?= esc($category['slug']) ?>" <?= ($filters['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option><?php endforeach ?></select>
        <fieldset><legend>Rentang harga</legend><label for="min_price">Dari</label><input id="min_price" name="min_price" type="number" min="0" step="5000" value="<?= esc($filters['min_price'] ?? '') ?>" placeholder="Rp0"><label for="max_price">Sampai</label><input id="max_price" name="max_price" type="number" min="0" step="5000" value="<?= esc($filters['max_price'] ?? '') ?>" placeholder="Rp100.000"></fieldset>
        <label for="sort">Urutkan</label><select id="sort" name="sort"><option value="name_asc">Nama A–Z</option><option value="name_desc" <?= ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Nama Z–A</option><option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Harga terendah</option><option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Harga tertinggi</option></select>
        <button class="button" type="submit">Terapkan filter</button><a class="text-link" href="<?= site_url('katalog') ?>">Reset filter</a>
    </form>
    <div class="catalog-results"><div class="results-heading"><p><strong><?= $count ?></strong> <?= $count === 1 ? 'produk' : 'produk' ?></p><p>Harga dalam rupiah</p></div>
        <?php if ($products === []): ?><div class="empty-state"><h2>Belum ketemu.</h2><p>Coba ubah kata pencarian atau filter harga.</p><a class="button" href="<?= site_url('katalog') ?>">Reset pencarian &amp; filter</a></div><?php else: ?><div class="product-grid"><?php foreach ($products as $product): ?><?= view('shop/_product', ['product' => $product]) ?><?php endforeach ?></div><?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
