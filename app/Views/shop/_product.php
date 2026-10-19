<article class="product-item">
    <a class="product-art" href="<?= site_url('produk/' . $product['slug']) ?>">
        <img src="<?= base_url(esc($product['primary_image']['path'] ?? ('assets/images/products/' . $product['slug'] . '.svg'))) ?>" width="640" height="640" loading="lazy" alt="Ilustrasi <?= esc($product['name']) ?>">
        <?php if ($product['available_stock'] < 1): ?><span class="stock-label">Habis</span><?php endif ?>
    </a>
    <div class="product-copy">
        <div><p class="category-label"><?= esc($product['category_name'] ?? 'Bolu KUBO') ?></p><h3><a href="<?= site_url('produk/' . $product['slug']) ?>"><?= esc($product['name']) ?></a></h3></div>
        <p class="product-price"><?php if ($product['min_price'] === $product['max_price']): ?>Rp<?= number_format((int) $product['min_price'], 0, ',', '.') ?><?php else: ?>Mulai Rp<?= number_format((int) $product['min_price'], 0, ',', '.') ?><?php endif ?></p>
    </div>
    <a class="text-link" href="<?= site_url('produk/' . $product['slug']) ?>"><?= $product['available_stock'] < 1 ? 'Lihat detail' : 'Pilih ukuran' ?></a>
</article>