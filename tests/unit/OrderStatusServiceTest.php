<?php

declare(strict_types=1);

use App\Services\OrderStatusService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class OrderStatusServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];
    private int $orderId;
    private int $variantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
        $this->db->table('users')->insert(['username' => 'status-test', 'active' => 1]);
        $userId = (int) $this->db->insertID();
        $this->variantId = (int) $this->db->table('product_variants')->where('label', 'Loyang 20 cm')->get()->getRow('id');
        $this->db->table('orders')->insert([
            'user_id' => $userId, 'order_no' => 'BH-181026-001', 'status' => 'pending_payment',
            'fulfillment_type' => 'pickup', 'customer_name' => 'Test', 'customer_phone' => '081234567890',
            'pickup_date' => date('Y-m-d', strtotime('+1 day')), 'time_slot_snapshot' => '08.00–10.00',
            'subtotal' => 170000, 'delivery_fee' => 0, 'total' => 170000,
            'payment_due_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 'stock_deducted' => false,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->orderId = (int) $this->db->insertID();
        $this->db->table('order_items')->insert([
            'order_id' => $this->orderId, 'variant_id' => $this->variantId,
            'product_name_snapshot' => 'Bolu Marmer', 'variant_name_snapshot' => 'Loyang 20 cm',
            'unit_price' => 85000, 'quantity' => 2, 'line_total' => 170000,
        ]);
    }

    public function testConfirmationDeductsStockAndCancellationRestoresItOnce(): void
    {
        $service = new OrderStatusService($this->db);
        $service->transition($this->orderId, 'confirmed', null, 'Pembayaran diperiksa.');
        $this->assertSame(6, (int) $this->db->table('product_variants')->where('id', $this->variantId)->get()->getRow('stock'));

        $service->transition($this->orderId, 'processing', null);
        $service->transition($this->orderId, 'cancelled', null, 'Pelanggan membatalkan pesanan.');
        $this->assertSame(8, (int) $this->db->table('product_variants')->where('id', $this->variantId)->get()->getRow('stock'));
    }

    public function testInvalidStatusTransitionIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new OrderStatusService($this->db))->transition($this->orderId, 'processing', null);
    }

    public function testCancellationRequiresReason(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new OrderStatusService($this->db))->transition($this->orderId, 'cancelled', null);
    }
}