<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<section class="payment-page">
    <p class="eyebrow">Pesanan <?= esc($order['order_no']) ?></p>
    <h1>Pembayaran QRIS</h1>
    <p>Status: <strong><?= esc($order['status']) ?></strong></p>
    <div class="payment-total">
        <span>Total yang harus dibayar</span>
        <strong>Rp<?= number_format((int) $order['total'], 0, ',', '.') ?></strong>
    </div>
    <?php if ($order['status'] === 'pending_payment'): ?>
        <h2 class="qris-heading">Scan QRIS</h2>
        <p>Scan kode QR di bawah dengan aplikasi pembayaran, lalu masukkan nominal total pesanan di atas. Pastikan nama merchant dan nominal sudah benar sebelum membayar.</p>
        <img class="qris-image" src="<?= base_url('assets/images/products/qris.webp') ?>" width="1136" height="1600" alt="Kode QRIS pembayaran KUBO">
        <p>Batas pembayaran: <?= esc($order['payment_due_at']) ?> WIB</p>
        <form method="post" action="<?= site_url('pesanan/' . $order['id'] . '/bukti') ?>" enctype="multipart/form-data" class="proof-form">
            <?= csrf_field() ?>
            <label for="proof">Unggah bukti pembayaran (JPG/PNG, maksimal 2 MB)</label>
            <input id="proof" type="file" name="proof" accept="image/jpeg,image/png" required>
            <button class="button" type="submit">Kirim bukti pembayaran</button>
            <small>Pembayaran akan diverifikasi secara manual.</small>
        </form>
    <?php endif ?>
    <a class="text-link" href="<?= site_url('pesanan/' . $order['id']) ?>">Lihat detail pesanan</a>
</section>
<?= $this->endSection() ?>
