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
            ['name' => 'Bolu Marmer', 'slug' => 'bolu-marmer', 'description' => 'Bolu lembut dengan perpaduan vanilla dan cokelat.', 'composition' => 'Telur, tepung terigu, gula, mentega, susu, cokelat.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'category' => 'bolu-favorit', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 85000, 'stock' => 8], ['label' => 'Loyang 24 cm', 'price' => 120000, 'stock' => 4]]],
            ['name' => 'Bolu Pandan', 'slug' => 'bolu-pandan', 'description' => 'Aroma pandan harum dengan tekstur lembut.', 'composition' => 'Telur, tepung terigu, gula, mentega, pandan, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'category' => 'bolu-klasik', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 75000, 'stock' => 12]]],
            ['name' => 'Bolu Cokelat', 'slug' => 'bolu-cokelat', 'description' => 'Bolu cokelat lembut untuk teman minum teh.', 'composition' => 'Telur, tepung terigu, gula, mentega, cokelat, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'category' => 'bolu-favorit', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 90000, 'stock' => 6]]],
            ['name' => 'Bolu Keju', 'slug' => 'bolu-keju', 'description' => 'Bolu lembut dengan taburan keju gurih.', 'composition' => 'Telur, tepung terigu, gula, mentega, keju, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'category' => 'bolu-spesial', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 95000, 'stock' => 5]]],
            ['name' => 'Bolu Pisang', 'slug' => 'bolu-pisang', 'description' => 'Bolu pisang rumahan dengan rasa manis alami.', 'composition' => 'Pisang, telur, tepung terigu, gula, mentega.', 'allergens' => 'Mengandung telur dan gluten.', 'category' => 'bolu-klasik', 'variants' => [['label' => 'Loyang 20 cm', 'price' => 80000, 'stock' => 0]]],
            ['name' => 'Bolu Gulung', 'slug' => 'bolu-gulung', 'description' => 'Bolu gulung lembut dengan isian manis.', 'composition' => 'Telur, tepung terigu, gula, mentega, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'category' => 'bolu-spesial', 'variants' => [['label' => '1 gulung', 'price' => 65000, 'stock' => 9]]],
        ];

        foreach ($products as $product) {
            $variants = $product['variants'];
            unset($product['variants']);
            $product['category_id'] = $categoryIds[$product['category']];
            unset($product['category']);
            $product['storage_note'] = 'Simpan dalam wadah tertutup dan konsumsi selagi segar.';
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
                'path' => 'assets/images/products/' . $product['slug'] . '.svg',
                'alt_text' => $product['name'],
                'sort_order' => 0,
                'is_primary' => true,
                'created_at' => $now,
            ]);
        }

        $this->db->table('delivery_zones')->insertBatch([
            ['name' => 'Zona A – Coblong', 'description' => 'Area Coblong dan sekitarnya.', 'fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Zona B – Sukajadi', 'description' => 'Area Sukajadi dan sekitarnya.', 'fee' => 20000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
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
            ['setting_key' => 'demo_payment_notice', 'setting_value' => 'Data rekening demo. Jangan melakukan transfer.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
