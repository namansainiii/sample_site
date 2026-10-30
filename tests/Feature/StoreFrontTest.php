<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Money;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreFrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);
    }

    /**
     * @return array<string, string>
     */
    private function address(array $overrides = []): array
    {
        return [
            'shipping_name' => 'Ravi Kumar',
            'shipping_phone' => '9812345678',
            'shipping_address' => 'Plot 7, Focal Point',
            'shipping_city' => 'Ludhiana',
            'shipping_state' => 'Punjab',
            'shipping_pincode' => '141010',
            'payment_method' => 'cod',
            ...$overrides,
        ];
    }

    private function variant(): ProductVariant
    {
        return ProductVariant::where('stock', '>', 20)->with('product')->firstOrFail();
    }

    public function test_public_pages_render_without_login(): void
    {
        $product = Product::firstOrFail();

        $this->get('/')->assertOk()->assertSee('Shop by category');
        $this->get(route('shop'))->assertOk();
        $this->get(route('shop.category', $product->category))->assertOk()->assertSee($product->name);
        $this->get(route('shop', ['q' => 'nylock', 'sort' => 'price_asc', 'in_stock' => 1]))->assertOk()->assertSee('Nylock Nut');
        $this->get(route('product.show', $product))->assertOk()->assertSee($product->variants->first()->sku);
        $this->get(route('quotes.create'))->assertOk();
        $this->get(route('cart.index'))->assertOk();
    }

    public function test_hidden_products_are_not_reachable(): void
    {
        $product = Product::firstOrFail();
        $product->update(['is_active' => false]);

        $this->get(route('product.show', $product))->assertNotFound();
    }

    public function test_checkout_and_account_require_login(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('login'));
        $this->get(route('account.orders'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_bulk_tier_price_applies_in_cart(): void
    {
        $variant = $this->variant();

        $this->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 10])->assertRedirect();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Bulk price')
            ->assertSee(Money::inr(round($variant->price * 0.95, 2) * 10));
    }

    public function test_cart_quantity_is_capped_at_stock(): void
    {
        $variant = $this->variant();

        $this->postJson(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 9999])
            ->assertOk()
            ->assertJson(['count' => $variant->stock]);
    }

    public function test_quick_order_adds_known_skus_and_reports_unknown(): void
    {
        $variant = $this->variant();

        $this->post(route('cart.quick-order'), ['lines' => strtolower($variant->sku).", 3\nNOPE-123, 2"])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('warning', fn (string $message) => str_contains($message, 'NOPE-123'));

        $this->assertSame([$variant->id => 3], session('cart'));
    }

    public function test_cod_checkout_creates_order_and_reserves_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant();
        $stockBefore = $variant->stock;

        $this->actingAs($user)->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user)->post(route('checkout.store'), $this->address(['gstin' => '03abcde1234f1z5']));

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('account.orders.show', $order));

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('03ABCDE1234F1Z5', $order->gstin);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertEquals(round($order->subtotal + $order->gst_amount + $order->shipping_fee, 2), (float) $order->total);
        $this->assertSame($stockBefore - 2, $variant->fresh()->stock);
        $this->assertNull(session('cart'));

        $this->actingAs($user)->get(route('account.orders.show', $order))->assertOk()->assertSee($order->order_number);
        $this->actingAs($user)->get(route('account.orders.invoice', $order))->assertOk()->assertSee('CGST')->assertSee('TAX INVOICE');
    }

    public function test_checkout_rejects_when_stock_ran_out_meanwhile(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant();

        $this->actingAs($user)->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 5]);
        $variant->update(['stock' => 2]);

        $this->actingAs($user)->post(route('checkout.store'), $this->address())->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $variant->fresh()->stock);
    }

    public function test_customers_cannot_view_other_customers_orders(): void
    {
        $owner = User::factory()->create();
        $variant = $this->variant();

        $this->actingAs($owner)->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($owner)->post(route('checkout.store'), $this->address());
        $order = Order::sole();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('account.orders.show', $order))->assertNotFound();
        $this->actingAs($stranger)->get(route('account.orders.invoice', $order))->assertNotFound();
    }

    public function test_online_payment_is_rejected_when_razorpay_is_not_configured(): void
    {
        config(['services.razorpay.key' => null, 'services.razorpay.secret' => null]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cart.add'), ['variant_id' => $this->variant()->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('checkout.store'), $this->address(['payment_method' => 'razorpay']))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_razorpay_payment_is_verified_by_signature(): void
    {
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'test_secret']);
        Http::fake(['api.razorpay.com/*' => Http::response(['id' => 'order_TEST123'])]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cart.add'), ['variant_id' => $this->variant()->id, 'quantity' => 1]);
        $order = null;
        $this->actingAs($user)->post(route('checkout.store'), $this->address(['payment_method' => 'razorpay']))
            ->assertRedirect(route('checkout.pay', $order = Order::sole()));

        $this->assertSame('order_TEST123', $order->fresh()->razorpay_order_id);

        $this->actingAs($user)->post(route('checkout.verify', $order), [
            'razorpay_order_id' => 'order_TEST123',
            'razorpay_payment_id' => 'pay_ABC',
            'razorpay_signature' => 'forged',
        ]);
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);

        $this->actingAs($user)->post(route('checkout.verify', $order), [
            'razorpay_order_id' => 'order_TEST123',
            'razorpay_payment_id' => 'pay_ABC',
            'razorpay_signature' => hash_hmac('sha256', 'order_TEST123|pay_ABC', 'test_secret'),
        ])->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Confirmed, $order->status);
    }

    public function test_quote_request_is_saved(): void
    {
        $this->post(route('quotes.store'), [
            'name' => 'Anita',
            'phone' => '9811122233',
            'email' => 'anita@example.com',
            'requirements' => 'M12 x 75 HT bolts, 5000 pcs',
        ])->assertRedirect(route('quotes.create'));

        $this->assertDatabaseHas('quote_requests', ['email' => 'anita@example.com', 'status' => 'new']);
    }

    public function test_user_can_register_and_login(): void
    {
        $this->post(route('register'), [
            'name' => 'Sunil',
            'email' => 'sunil@example.com',
            'phone' => '9876501234',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->post(route('logout'));

        $this->post(route('login'), ['email' => 'sunil@example.com', 'password' => 'secret-pass'])->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }
}
