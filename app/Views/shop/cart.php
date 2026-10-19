<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<section class="cart-page">
    <p class="eyebrow">Pesananmu</p><h1>Keranjang</h1>
    <nav class="checkout-steps" aria-label="Tahapan pesanan"><strong aria-current="step">Keranjang</strong><span>Checkout</span><span>Bayar</span><span>Konfirmasi</span></nav>
    <?php if (session()->getFlashdata('error')): ?><p class="alert alert-error" role="alert"><?= esc(session()->getFlashdata('error')) ?></p><?php endif ?>
    <?php if (session()->getFlashdata('success')): ?><p class="alert alert-success" role="status"><?= esc(session()->getFlashdata('success')) ?></p><?php endif ?>
    <?php if ($cart['items'] === []): ?><div class="empty-state"><h2>Keranjangmu masih kosong.</h2><p>Pilih bolu dari menu KUBO untuk memulai.</p><a class="button" href="<?= site_url('katalog') ?>">Jelajahi katalog</a></div>
    <?php else: ?>
    <div class="cart-layout"><div class="cart-lines"><?php foreach ($cart['items'] as $item): $itemId = $userId === null ? (int) $item['variant_id'] : (int) $item['id']; ?>
        <article class="cart-line"><img src="<?= base_url('assets/images/products/' . $item['product_slug'] . '.svg') ?>" width="140" height="140" alt="Ilustrasi <?= esc($item['product_name']) ?>"><div class="cart-line-copy"><h2><?= esc($item['product_name']) ?></h2><p><?= esc($item['variant_label']) ?> · Rp<?= number_format((int) $item['price'], 0, ',', '.') ?></p><?php if ($item['note']): ?><p>Catatan: <?= esc($item['note']) ?></p><?php endif ?><p>Stok tersedia: <?= (int) $item['stock'] ?></p></div><div class="cart-line-actions"><form method="post" action="<?= site_url('keranjang/ubah/' . $itemId) ?>"><?= csrf_field() ?><label class="sr-only" for="qty-<?= $itemId ?>">Jumlah <?= esc($item['product_name']) ?></label><input id="qty-<?= $itemId ?>" type="number" name="quantity" min="1" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>"><button class="text-link" type="submit">Perbarui</button></form><strong>Rp<?= number_format((int) $item['line_total'], 0, ',', '.') ?></strong><form method="post" action="<?= site_url('keranjang/hapus/' . $itemId) ?>"><?= csrf_field() ?><button class="text-link" type="submit">Hapus</button></form></div></article>
    <?php endforeach ?></div>
    <aside class="cart-summary"><h2>Ringkasan</h2><p><span>Subtotal</span><strong>Rp<?= number_format((int) $cart['subtotal'], 0, ',', '.') ?></strong></p><p class="summary-total"><span>Total sementara</span><strong>Rp<?= number_format((int) $cart['subtotal'], 0, ',', '.') ?></strong></p><p class="summary-note">Ongkir dihitung saat checkout.</p><?php if ($userId === null): ?><a class="button" href="<?= site_url('login?redirect=checkout') ?>">Masuk untuk lanjut</a><?php else: ?><a class="button" href="<?= site_url('checkout') ?>">Lanjut ke checkout</a><?php endif ?></aside></div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>