<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
    }

    public function test_customers_are_forbidden_from_admin(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $product = Product::firstOrFail();

        foreach ([
            route('admin.dashboard'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.products.edit', $product),
            route('admin.categories.index'),
            route('admin.orders.index'),
            route('admin.quotes.index'),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_product_with_sizes_and_bulk_tiers(): void
    {
        $category = Category::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Stud Bolt',
            'gst_rate' => 18,
            'is_active' => 1,
            'specs_text' => "Grade: B7\nNot a spec line",
            'variants' => [
                ['sku' => 'tsb-m10', 'size' => 'M10', 'pack_qty' => 25, 'price' => 300, 'stock' => 40, 'min_order_qty' => 1, 'tiers' => '10=280, 50=260, junk'],
                ['sku' => 'TSB-M12', 'size' => 'M12', 'pack_qty' => 25, 'price' => 420, 'stock' => 0, 'min_order_qty' => 2],
            ],
        ])->assertRedirect();

        $product = Product::where('name', 'Test Stud Bolt')->with('variants.priceTiers')->sole();

        $this->assertSame(['Grade' => 'B7'], $product->specs);
        $this->assertSame(['TSB-M10', 'TSB-M12'], $product->variants->pluck('sku')->sort()->values()->all());
        $this->assertSame(280.0, $product->variants->firstWhere('sku', 'TSB-M10')->unitPriceFor(10));
    }

    public function test_duplicate_sku_from_another_product_is_rejected(): void
    {
        $existing = ProductVariant::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => Category::firstOrFail()->id,
            'name' => 'Clash',
            'gst_rate' => 18,
            'variants' => [['sku' => $existing->sku, 'size' => 'X', 'pack_qty' => 1, 'price' => 1, 'stock' => 1, 'min_order_qty' => 1]],
        ])->assertSessionHasErrors('variants');
    }

    public function test_cancelling_an_order_returns_stock(): void
    {
        $customer = User::factory()->create();
        $variant = ProductVariant::where('stock', '>', 20)->firstOrFail();
        $stockBefore = $variant->stock;

        $this->actingAs($customer)->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 3]);
        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'A', 'shipping_phone' => '9812345678', 'shipping_address' => 'X', 'shipping_city' => 'Y',
            'shipping_state' => 'Delhi', 'shipping_pincode' => '110001', 'payment_method' => 'cod',
        ]);
        $order = Order::sole();
        $this->assertSame($stockBefore - 3, $variant->fresh()->stock);

        $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => 'cancelled'])->assertSessionHas('success');
        $this->assertSame($stockBefore, $variant->fresh()->stock);

        // Cancelling twice, or reopening, must not touch stock again.
        $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => 'cancelled']);
        $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])->assertSessionHas('warning');
        $this->assertSame($stockBefore, $variant->fresh()->stock);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))->assertOk();
        $this->actingAs($this->admin)->get(route('account.orders.invoice', $order))->assertOk()->assertSee('IGST');
    }
}
