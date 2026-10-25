<?php

declare(strict_types=1);

use App\Services\CartService;
use App\Services\CheckoutService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class CheckoutServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = ['CodeIgniter\\Shield', 'App'];
    private int $userId;
    private int $variantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('App\\Database\\Seeds\\DemoShopSeeder');
        $users = model(\CodeIgniter\Shield\Models\UserModel::class);
        $user = $users->createNewUser([
            'username' => 'checkout-customer', 'email' => 'checkout@example.test',
            'password' => 'StrongPassword123!', 'password_confirm' => 'StrongPassword123!',
        ]);
        $users->save($user);
        $user->id = (int) $users->getInsertID();
        $users->addToDefaultGroup($user);
        $this->userId = (int) $user->id;
        $this->variantId = (int) $this->db->table('product_variants v')->select('v.id')->join('products p', 'p.id = v.product_id')->where('p.slug', 'bolu-panggang-meses-cokelat')->get()->getRow('id');
        (new CartService($this->db))->add($this->userId, 'checkout-test', $this->variantId, 2, '');
    }

    public function testPickupHasZeroDeliveryFeeAndIgnoresClientPrice(): void
    {
        $order = (new CheckoutService($this->db))->placeOrder($this->userId, $this->input([
            'fulfillment_type' => 'pickup', 'client_total' => 1, 'client_price' => 1,
        ]));

        $this->assertSame(0, $order['delivery_fee']);
        $this->assertSame(40000, $order['subtotal']);
        $this->assertSame(40000, $order['total']);
        $this->assertSame('pending_payment', $order['status']);
        $this->assertStringStartsWith('BH-', $order['order_no']);
    }

    public function testDeliveryTotalAddsConfiguredZoneFee(): void
    {
        $zoneId = (int) $this->db->table('delivery_zones')->where('fee', 20000)->get()->getRow('id');
        $order = (new CheckoutService($this->db))->placeOrder($this->userId, $this->input([
            'fulfillment_type' => 'delivery', 'delivery_zone_id' => $zoneId, 'address' => 'Jalan Contoh 1',
        ]));

        $this->assertSame(20000, $order['delivery_fee']);
        $this->assertSame(60000, $order['total']);
    }

    public function testInsufficientStockDoesNotCreateOrder(): void
    {
        $this->db->table('product_variants')->where('id', $this->variantId)->update(['stock' => 1]);
        try {
            (new CheckoutService($this->db))->placeOrder($this->userId, $this->input(['fulfillment_type' => 'pickup']));
            $this->fail('Checkout should reject a quantity above current stock.');
        } catch (\InvalidArgumentException) {
            $this->assertSame(0, $this->db->table('orders')->countAllResults());
        }
    }

    private function input(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Nabila Putri', 'customer_phone' => '081234567890',
            'customer_email' => 'checkout@example.test', 'fulfillment_type' => 'pickup',
            'scheduled_date' => date('Y-m-d', strtotime('+1 day')), 'time_slot_id' => 1,
        ], $overrides);
    }
}
