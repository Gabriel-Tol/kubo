<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShopTables extends Migration
{
    private array $attributes = ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci'];

    public function up(): void
    {
        $this->createTable('customer_profiles', [
            'id' => $this->id(), 'user_id' => $this->int(), 'full_name' => $this->varchar(120),
            'phone' => $this->varchar(30), 'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['user_id'], [['user_id', 'users', 'id', 'CASCADE']]);

        $this->createTable('categories', [
            'id' => $this->id(), 'name' => $this->varchar(100), 'slug' => $this->varchar(120),
            'description' => $this->text(true), 'sort_order' => $this->int(false, 0),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], [], [], ['slug']);

        $this->createTable('delivery_zones', [
            'id' => $this->id(), 'name' => $this->varchar(100), 'description' => $this->varchar(255, true),
            'fee' => $this->int(false, 0), 'is_active' => $this->bool(true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], [], [], ['name']);

        $this->createTable('addresses', [
            'id' => $this->id(), 'user_id' => $this->int(), 'label' => $this->varchar(60),
            'recipient_name' => $this->varchar(120), 'phone' => $this->varchar(30),
            'address_line' => $this->text(), 'landmark' => $this->varchar(255, true), 'delivery_zone_id' => $this->int(true),
            'is_default' => $this->bool(false), 'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['user_id', 'delivery_zone_id'], [['user_id', 'users', 'id', 'CASCADE'], ['delivery_zone_id', 'delivery_zones', 'id', 'SET NULL']]);

        $this->createTable('time_slots', [
            'id' => $this->id(), 'label' => $this->varchar(80), 'start_time' => ['type' => 'time'],
            'end_time' => ['type' => 'time'], 'is_full' => $this->bool(false), 'is_active' => $this->bool(true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], [], [], ['label']);

        $this->createTable('products', [
            'id' => $this->id(), 'category_id' => $this->int(true), 'name' => $this->varchar(160),
            'slug' => $this->varchar(180), 'description' => $this->text(true), 'composition' => $this->text(true),
            'allergens' => $this->varchar(255, true), 'storage_note' => $this->varchar(255, true),
            'is_active' => $this->bool(true), 'archived_at' => $this->datetime(true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['category_id', 'is_active'], [['category_id', 'categories', 'id', 'SET NULL']], ['slug']);

        $this->createTable('product_variants', [
            'id' => $this->id(), 'product_id' => $this->int(), 'label' => $this->varchar(100),
            'price' => $this->int(false, 0), 'stock' => $this->int(false, 0),
            'serving_note' => $this->varchar(255, true), 'is_active' => $this->bool(true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['product_id', 'is_active'], [['product_id', 'products', 'id', 'CASCADE']]);

        $this->createTable('product_images', [
            'id' => $this->id(), 'product_id' => $this->int(), 'path' => $this->varchar(255),
            'alt_text' => $this->varchar(180, true), 'sort_order' => $this->int(false, 0),
            'is_primary' => $this->bool(false), 'created_at' => $this->datetime(true),
        ], ['product_id'], [['product_id', 'products', 'id', 'CASCADE']]);

        $this->createTable('carts', [
            'id' => $this->id(), 'user_id' => $this->int(true), 'session_key' => $this->varchar(128, true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['user_id', 'session_key'], [['user_id', 'users', 'id', 'CASCADE']]);

        $this->createTable('cart_items', [
            'id' => $this->id(), 'cart_id' => $this->int(), 'variant_id' => $this->int(),
            'quantity' => $this->int(false, 1), 'note' => $this->varchar(150, true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['cart_id', 'variant_id'], [['cart_id', 'carts', 'id', 'CASCADE'], ['variant_id', 'product_variants', 'id', 'CASCADE']]);

        $this->createTable('orders', [
            'id' => $this->id(), 'user_id' => $this->int(), 'order_no' => $this->varchar(40),
            'status' => $this->varchar(40), 'fulfillment_type' => $this->varchar(20),
            'customer_name' => $this->varchar(120), 'customer_phone' => $this->varchar(30),
            'customer_email' => $this->varchar(190, true), 'zone_name_snapshot' => $this->varchar(100, true),
            'address_snapshot' => $this->text(true), 'pickup_date' => ['type' => 'date'], 'time_slot_snapshot' => $this->varchar(80),
            'subtotal' => $this->int(false, 0), 'delivery_fee' => $this->int(false, 0), 'total' => $this->int(false, 0),
            'payment_due_at' => $this->datetime(), 'stock_deducted' => $this->bool(false),
            'cancellation_reason' => $this->text(true), 'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['user_id', 'status', 'payment_due_at'], [['user_id', 'users', 'id', 'RESTRICT']], ['order_no']);

        $this->createTable('order_items', [
            'id' => $this->id(), 'order_id' => $this->int(), 'variant_id' => $this->int(true),
            'product_name_snapshot' => $this->varchar(160), 'variant_name_snapshot' => $this->varchar(100),
            'unit_price' => $this->int(false, 0), 'quantity' => $this->int(false, 1), 'note' => $this->varchar(150, true),
            'line_total' => $this->int(false, 0), 'created_at' => $this->datetime(true),
        ], ['order_id', 'variant_id'], [['order_id', 'orders', 'id', 'CASCADE'], ['variant_id', 'product_variants', 'id', 'SET NULL']]);

        $this->createTable('payment_proofs', [
            'id' => $this->id(), 'order_id' => $this->int(), 'file_path' => $this->varchar(255),
            'original_name' => $this->varchar(255), 'mime_type' => $this->varchar(100), 'file_size' => $this->int(false, 0),
            'status' => $this->varchar(20), 'reviewed_by' => $this->int(true), 'review_reason' => $this->text(true),
            'reviewed_at' => $this->datetime(true), 'created_at' => $this->datetime(true),
        ], ['order_id', 'status'], [['order_id', 'orders', 'id', 'CASCADE'], ['reviewed_by', 'users', 'id', 'SET NULL']]);

        $this->createTable('order_status_logs', [
            'id' => $this->id(), 'order_id' => $this->int(), 'actor_id' => $this->int(true),
            'from_status' => $this->varchar(40, true), 'to_status' => $this->varchar(40),
            'note' => $this->text(true), 'created_at' => $this->datetime(true),
        ], ['order_id', 'actor_id'], [['order_id', 'orders', 'id', 'CASCADE'], ['actor_id', 'users', 'id', 'SET NULL']]);

        $this->createTable('settings', [
            'id' => $this->id(), 'class' => $this->varchar(255), 'key' => $this->varchar(255),
            'value' => $this->text(true), 'type' => $this->varchar(31), 'context' => $this->varchar(255, true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], ['class', 'key', 'context']);

        $this->createTable('shop_settings', [
            'id' => $this->id(), 'setting_key' => $this->varchar(100), 'setting_value' => $this->text(true),
            'created_at' => $this->datetime(true), 'updated_at' => $this->datetime(true),
        ], [], [], ['setting_key']);
    }

    public function down(): void
    {
        foreach (['shop_settings', 'settings', 'order_status_logs', 'payment_proofs', 'order_items', 'orders', 'cart_items', 'carts', 'product_images', 'product_variants', 'products', 'time_slots', 'addresses', 'delivery_zones', 'categories', 'customer_profiles'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }

    private function createTable(string $table, array $fields, array $indexes = [], array $foreignKeys = [], array $uniqueIndexes = []): void
    {
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        foreach ($indexes as $index) {
            $this->forge->addKey($index);
        }
        foreach ($foreignKeys as [$field, $reference, $referenceField, $onDelete]) {
            $this->forge->addForeignKey($field, $reference, $referenceField, '', $onDelete);
        }
        foreach ($uniqueIndexes as $index) {
            $this->forge->addUniqueKey($index);
        }
        $this->forge->createTable($table, true, $this->db->getPlatform() === 'MySQLi' ? $this->attributes : []);
    }

    private function id(): array { return ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true]; }
    private function int(bool $nullable = false, ?int $default = null): array
    {
        $field = ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => $nullable];
        if ($default !== null) { $field['default'] = $default; }
        return $field;
    }
    private function bool(bool $default): array { return ['type' => 'tinyint', 'constraint' => 1, 'default' => (int) $default]; }
    private function varchar(int $length, bool $nullable = false): array { return ['type' => 'varchar', 'constraint' => $length, 'null' => $nullable]; }
    private function text(bool $nullable = false): array { return ['type' => 'text', 'null' => $nullable]; }
    private function datetime(bool $nullable = false): array { return ['type' => 'datetime', 'null' => $nullable]; }
}
