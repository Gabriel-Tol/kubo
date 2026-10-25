<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateBoluPrices extends Migration
{
    public function up(): void
    {
        $prices = [
            'bolu-panggang-meses-cokelat' => 20000,
            'bolu-marmer-pandan' => 20000,
            'bolu-marmer-cokelat' => 20000,
            'bolu-meses-warna-warni' => 25000,
            'bolu-pisang' => 25000,
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($prices as $slug => $price) {
            $product = $this->db->table('products')->select('id')->where('slug', $slug)->get()->getRowArray();
            if ($product !== null) {
                $this->db->table('product_variants')->where('product_id', $product['id'])->update([
                    'price' => $price, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $prices = [
            'bolu-panggang-meses-cokelat' => 90000,
            'bolu-marmer-pandan' => 75000,
            'bolu-marmer-cokelat' => 85000,
            'bolu-meses-warna-warni' => 95000,
            'bolu-pisang' => 80000,
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($prices as $slug => $price) {
            $product = $this->db->table('products')->select('id')->where('slug', $slug)->get()->getRowArray();
            if ($product === null) {
                continue;
            }
            $variants = $this->db->table('product_variants')->where('product_id', $product['id']);
            if ($slug === 'bolu-marmer-cokelat') {
                $variants->update(['price' => 85000, 'updated_at' => $now]);
                $this->db->table('product_variants')->where('product_id', $product['id'])
                    ->where('label', 'Loyang 24 cm')->update(['price' => 120000, 'updated_at' => $now]);
                continue;
            }
            $variants->update(['price' => $price, 'updated_at' => $now]);
        }
    }
}
