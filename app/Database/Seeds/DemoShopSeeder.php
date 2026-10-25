<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoShopSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $categories = [
            ['name' => 'Bolu Favorit', 'slug' => 'bolu-favorit', 'description' => 'Pilihan bolu rumahan KUBO.', 'sort_order' => 1],
            ['name' => 'Bolu Klasik', 'slug' => 'bolu-klasik', 'description' => 'Rasa klasik untuk dinikmati bersama.', 'sort_order' => 2],
            ['name' => 'Bolu Spesial', 'slug' => 'bolu-spesial', 'description' => 'Varian istimewa untuk momen spesial.', 'sort_order' => 3],
        ];
        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }
        unset($category);
        $this->db->table('categories')->insertBatch($categories);
        $categoryIds = array_column($this->db->table('categories')->get()->getResultArray(), 'id', 'slug');

        $products = [
            ['name' => 'Bolu Panggang Meses Cokelat', 'slug' => 'bolu-panggang-meses-cokelat', 'image' => 'bolu-panggang-meses-cokelat.webp', 'description' => 'Kue bolu panggang berbentuk cincin dengan taburan meses cokelat, cocok untuk camilan bersama keluarga dan teman.', 'composition' => 'Tepung terigu, telur, gula, margarin, susu, bahan pengembang, vanili, garam, dan meses cokelat.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.', 'category' => 'bolu-favorit', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 20000, 'stock' => 6]]],
            ['name' => 'Bolu Marmer Pandan', 'slug' => 'bolu-marmer-pandan', 'image' => 'bolu-marmer-pandan.webp', 'description' => 'Bolu panggang bermotif marmer dengan perpaduan warna hijau pandan dan cokelat, memiliki tampilan yang menarik serta cocok untuk menemani waktu santai bersama keluarga.', 'composition' => 'Tepung terigu, telur, gula, margarin, susu, pasta pandan, cokelat atau kakao, vanili, bahan pengembang, dan garam.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.', 'category' => 'bolu-klasik', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 20000, 'stock' => 12]]],
            ['name' => 'Bolu Marmer Cokelat', 'slug' => 'bolu-marmer-cokelat', 'image' => 'bolu-marmer-cokelat.webp', 'description' => 'Bolu panggang berbentuk cincin dengan motif marmer berupa perpaduan warna cokelat tua dan cokelat muda. Tampilannya khas dan menarik, serta cocok dinikmati sebagai camilan bersama keluarga atau teman.', 'composition' => 'Tepung terigu, telur, gula pasir, margarin, susu, cokelat atau bubuk kakao, vanili, bahan pengembang, dan garam.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.', 'category' => 'bolu-favorit', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 20000, 'stock' => 8], ['label' => 'Loyang 24 cm', 'price' => 20000, 'stock' => 4]]],
            ['name' => 'Bolu Meses Warna-Warni', 'slug' => 'bolu-meses-warna-warni', 'image' => 'bolu-meses-warna-warni.webp', 'description' => 'Bolu panggang berbentuk cincin dengan warna cokelat muda dan taburan meses warna-warni di permukaannya. Tampilannya ceria dan cocok dijadikan camilan sehari-hari maupun hidangan untuk keluarga dan teman.', 'composition' => 'Tepung terigu, telur, gula pasir, margarin, susu, meses warna-warni, vanili, bahan pengembang, dan garam.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.', 'category' => 'bolu-spesial', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 25000, 'stock' => 5]]],
            ['name' => 'Bolu Pisang', 'slug' => 'bolu-pisang', 'image' => 'bolu-pisang.webp', 'description' => 'Bolu panggang berbentuk cincin dengan warna cokelat keemasan dan hiasan irisan pisang di bagian atas. Cita rasa manis khas pisang ini cocok dinikmati sebagai camilan bersama keluarga maupun teman.', 'composition' => 'Tepung terigu, pisang matang, telur, gula pasir, margarin atau minyak goreng, susu, vanili, bahan pengembang, dan garam.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering. Hindari paparan sinar matahari langsung.', 'category' => 'bolu-klasik', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 25000, 'stock' => 0]]],
        ];

        foreach ($products as $product) {
            $variants = $product['variants'];
            $image = $product['image'];
            unset($product['variants']);
            unset($product['image']);
            $product['category_id'] = $categoryIds[$product['category']];
            unset($product['category']);
            $product['is_active'] = true;
            $product['created_at'] = $now;
            $product['updated_at'] = $now;
            $this->db->table('products')->insert($product);
            $productId = (int) $this->db->insertID();
            foreach ($variants as $variant) {
                $variant['product_id'] = $productId;
                $variant['serving_note'] = null;
                $variant['is_active'] = true;
                $variant['created_at'] = $now;
                $variant['updated_at'] = $now;
                $this->db->table('product_variants')->insert($variant);
            }
            $this->db->table('product_images')->insert([
                'product_id' => $productId,
                'path' => 'assets/images/products/' . $image,
                'alt_text' => $product['name'],
                'sort_order' => 0,
                'is_primary' => true,
                'created_at' => $now,
            ]);
        }

        $this->db->table('delivery_zones')->insertBatch([
            ['name' => 'Zona A – Dalam Kota', 'description' => 'Area Pontianak dalam kota.', 'fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Zona B – Sekitar Kota', 'description' => 'Area Pontianak dan sekitarnya.', 'fee' => 20000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('time_slots')->insertBatch([
            ['label' => '08.00–10.00', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'is_full' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => '10.00–12.00', 'start_time' => '10:00:00', 'end_time' => '12:00:00', 'is_full' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => '13.00–15.00', 'start_time' => '13:00:00', 'end_time' => '15:00:00', 'is_full' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => '15.00–17.00', 'start_time' => '15:00:00', 'end_time' => '17:00:00', 'is_full' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('shop_settings')->insertBatch([
            ['setting_key' => 'shop_name', 'setting_value' => 'Bolu Hangat KUBO (Demo)', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'slot_lead_minutes', 'setting_value' => '120', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'payment_deadline_minutes', 'setting_value' => '60', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'demo_payment_notice', 'setting_value' => 'QRIS statis. Periksa transaksi sebelum menyetujui pembayaran.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
