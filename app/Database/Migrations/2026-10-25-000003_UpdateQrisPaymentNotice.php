<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateQrisPaymentNotice extends Migration
{
    public function up(): void
    {
        $this->db->table('shop_settings')->where('setting_key', 'demo_payment_notice')->update([
            'setting_value' => 'QRIS statis. Periksa transaksi sebelum menyetujui pembayaran.',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        $this->db->table('shop_settings')->where('setting_key', 'demo_payment_notice')->update([
            'setting_value' => 'Data rekening demo. Jangan melakukan transfer.',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
