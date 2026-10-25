<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateStoreDomicile extends Migration
{
    public function up(): void
    {
        $this->db->table('delivery_zones')->where('name', 'Zona A – Coblong')->update([
            'name' => 'Zona A – Dalam Kota', 'description' => 'Area Pontianak dalam kota.', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('delivery_zones')->where('name', 'Zona B – Sukajadi')->update([
            'name' => 'Zona B – Sekitar Kota', 'description' => 'Area Pontianak dan sekitarnya.', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        $this->db->table('delivery_zones')->where('name', 'Zona A – Dalam Kota')->update([
            'name' => 'Zona A – Coblong', 'description' => 'Area Coblong dan sekitarnya.', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('delivery_zones')->where('name', 'Zona B – Sekitar Kota')->update([
            'name' => 'Zona B – Sukajadi', 'description' => 'Area Sukajadi dan sekitarnya.', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
