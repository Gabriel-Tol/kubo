<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateBoluCatalog extends Migration
{
    private array $products = [
        'bolu-cokelat' => [
            'name' => 'Bolu Panggang Meses Cokelat', 'slug' => 'bolu-panggang-meses-cokelat',
            'description' => 'Kue bolu panggang berbentuk cincin dengan taburan meses cokelat, cocok untuk camilan bersama keluarga dan teman.',
            'composition' => 'Tepung terigu, telur, gula, margarin, susu, bahan pengembang, vanili, garam, dan meses cokelat.',
            'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.',
            'image' => 'bolu-panggang-meses-cokelat.webp',
        ],
        'bolu-pandan' => [
            'name' => 'Bolu Marmer Pandan', 'slug' => 'bolu-marmer-pandan',
            'description' => 'Bolu panggang bermotif marmer dengan perpaduan warna hijau pandan dan cokelat, memiliki tampilan yang menarik serta cocok untuk menemani waktu santai bersama keluarga.',
            'composition' => 'Tepung terigu, telur, gula, margarin, susu, pasta pandan, cokelat atau kakao, vanili, bahan pengembang, dan garam.',
            'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.',
            'image' => 'bolu-marmer-pandan.webp',
        ],
        'bolu-marmer' => [
            'name' => 'Bolu Marmer Cokelat', 'slug' => 'bolu-marmer-cokelat',
            'description' => 'Bolu panggang berbentuk cincin dengan motif marmer berupa perpaduan warna cokelat tua dan cokelat muda. Tampilannya khas dan menarik, serta cocok dinikmati sebagai camilan bersama keluarga atau teman.',
            'composition' => 'Tepung terigu, telur, gula pasir, margarin, susu, cokelat atau bubuk kakao, vanili, bahan pengembang, dan garam.',
            'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.',
            'image' => 'bolu-marmer-cokelat.webp',
        ],
        'bolu-keju' => [
            'name' => 'Bolu Meses Warna-Warni', 'slug' => 'bolu-meses-warna-warni',
            'description' => 'Bolu panggang berbentuk cincin dengan warna cokelat muda dan taburan meses warna-warni di permukaannya. Tampilannya ceria dan cocok dijadikan camilan sehari-hari maupun hidangan untuk keluarga dan teman.',
            'composition' => 'Tepung terigu, telur, gula pasir, margarin, susu, meses warna-warni, vanili, bahan pengembang, dan garam.',
            'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering.',
            'image' => 'bolu-meses-warna-warni.webp',
        ],
        'bolu-pisang' => [
            'name' => 'Bolu Pisang', 'slug' => 'bolu-pisang',
            'description' => 'Bolu panggang berbentuk cincin dengan warna cokelat keemasan dan hiasan irisan pisang di bagian atas. Cita rasa manis khas pisang ini cocok dinikmati sebagai camilan bersama keluarga maupun teman.',
            'composition' => 'Tepung terigu, pisang matang, telur, gula pasir, margarin atau minyak goreng, susu, vanili, bahan pengembang, dan garam.',
            'storage_note' => 'Simpan dalam wadah tertutup di tempat sejuk dan kering. Hindari paparan sinar matahari langsung.',
            'image' => 'bolu-pisang.webp',
        ],
    ];

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($this->products as $oldSlug => $product) {
            $existing = $this->db->table('products')->where('slug', $oldSlug)->get()->getRowArray();
            if ($existing === null) {
                continue;
            }
            $this->db->table('products')->where('id', $existing['id'])->update([
                'name' => $product['name'], 'slug' => $product['slug'], 'description' => $product['description'],
                'composition' => $product['composition'], 'allergens' => 'Mengandung telur, gluten, dan susu.',
                'storage_note' => $product['storage_note'], 'updated_at' => $now,
            ]);
            $this->db->table('product_images')->where('product_id', $existing['id'])->where('is_primary', 1)->update([
                'path' => 'assets/images/products/' . $product['image'], 'alt_text' => $product['name'],
            ]);
        }
        $this->db->table('products')->where('slug', 'bolu-gulung')->update([
            'is_active' => false, 'archived_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $old = [
            'bolu-panggang-meses-cokelat' => ['name' => 'Bolu Cokelat', 'slug' => 'bolu-cokelat', 'description' => 'Bolu cokelat lembut untuk teman minum teh.', 'composition' => 'Telur, tepung terigu, gula, mentega, cokelat, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'image' => 'bolu-cokelat.svg'],
            'bolu-marmer-pandan' => ['name' => 'Bolu Pandan', 'slug' => 'bolu-pandan', 'description' => 'Aroma pandan harum dengan tekstur lembut.', 'composition' => 'Telur, tepung terigu, gula, mentega, pandan, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'image' => 'bolu-pandan.svg'],
            'bolu-marmer-cokelat' => ['name' => 'Bolu Marmer', 'slug' => 'bolu-marmer', 'description' => 'Bolu lembut dengan perpaduan vanilla dan cokelat.', 'composition' => 'Telur, tepung terigu, gula, mentega, susu, cokelat.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'image' => 'bolu-marmer.svg'],
            'bolu-meses-warna-warni' => ['name' => 'Bolu Keju', 'slug' => 'bolu-keju', 'description' => 'Bolu lembut dengan taburan keju gurih.', 'composition' => 'Telur, tepung terigu, gula, mentega, keju, susu.', 'allergens' => 'Mengandung telur, gluten, dan susu.', 'image' => 'bolu-keju.svg'],
            'bolu-pisang' => ['name' => 'Bolu Pisang', 'slug' => 'bolu-pisang', 'description' => 'Bolu pisang rumahan dengan rasa manis alami.', 'composition' => 'Pisang, telur, tepung terigu, gula, mentega.', 'allergens' => 'Mengandung telur dan gluten.', 'image' => 'bolu-pisang.svg'],
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($old as $newSlug => $product) {
            $existing = $this->db->table('products')->where('slug', $newSlug)->get()->getRowArray();
            if ($existing === null) {
                continue;
            }
            $this->db->table('products')->where('id', $existing['id'])->update([
                'name' => $product['name'], 'slug' => $product['slug'], 'description' => $product['description'],
                'composition' => $product['composition'], 'allergens' => $product['allergens'],
                'storage_note' => 'Simpan dalam wadah tertutup dan konsumsi selagi segar.', 'updated_at' => $now,
            ]);
            $this->db->table('product_images')->where('product_id', $existing['id'])->where('is_primary', 1)->update([
                'path' => 'assets/images/products/' . $product['image'], 'alt_text' => $product['name'],
            ]);
        }
        $this->db->table('products')->where('slug', 'bolu-gulung')->update([
            'is_active' => true, 'archived_at' => null, 'updated_at' => $now,
        ]);
    }
}
