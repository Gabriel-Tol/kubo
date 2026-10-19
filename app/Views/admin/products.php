<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<p class="eyebrow">Katalog toko</p>
<div class="admin-section-heading"><h1>Produk</h1><a class="button" href="<?= site_url('admin/produk/tambah') ?>">Tambah produk</a></div>
<?php if (session()->getFlashdata('error')): ?><p class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></p><?php endif ?>
<?php if (session()->getFlashdata('success')): ?><p class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></p><?php endif ?>
<div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Harga / varian</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($products as $product): ?><tr>
<td><strong><?= esc($product['name']) ?></strong><small><?= esc($product['slug']) ?></small></td>
<td><?= esc($product['category_name'] ?? 'Tanpa kategori') ?></td>
<td><?php foreach ($product['variants'] as $variant): ?><small><?= esc($variant['label']) ?> · Rp<?= number_format((int) $variant['price'], 0, ',', '.') ?></small><?php endforeach ?></td>
<td><?php foreach ($product['variants'] as $variant): ?><small><?= (int) $variant['stock'] ?> tersedia</small><?php endforeach ?></td>
<td><?= $product['archived_at'] ? 'Diarsipkan' : ($product['is_active'] ? 'Aktif' : 'Nonaktif') ?></td>
<td><a href="<?= site_url('admin/produk/' . $product['id'] . '/edit') ?>">Edit</a><form method="post" action="<?= site_url('admin/produk/' . $product['id'] . '/arsip') ?>"><?= csrf_field() ?><button class="text-link"><?= $product['archived_at'] ? 'Pulihkan' : 'Arsipkan' ?></button></form></td>
</tr><?php endforeach ?>
</tbody></table></div>
<section class="admin-section"><h2>Kategori</h2>
<div class="category-list"><?php foreach ($categories as $category): ?><div class="category-list-item">
<form method="post" action="<?= site_url('admin/kategori/' . $category['id']) ?>"><?= csrf_field() ?><label class="sr-only" for="category-<?= (int) $category['id'] ?>">Nama kategori <?= esc($category['name']) ?></label><input id="category-<?= (int) $category['id'] ?>" name="name" value="<?= esc($category['name']) ?>" required maxlength="100"><button class="button button-small">Simpan</button></form>
<form method="post" action="<?= site_url('admin/kategori/' . $category['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus kategori ini?')"><?= csrf_field() ?><button class="text-link">Hapus</button></form>
</div><?php endforeach ?></div>
<form method="post" action="<?= site_url('admin/kategori') ?>" class="admin-filter"><?= csrf_field() ?><label for="category-name">Kategori baru</label><input id="category-name" name="name" required maxlength="100"><button class="button button-small">Tambah kategori</button></form>
<p>Kategori yang masih dipakai produk tidak dapat dihapus.</p></section>
<?= $this->endSection() ?>