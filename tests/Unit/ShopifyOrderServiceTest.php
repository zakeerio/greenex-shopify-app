<?php

namespace Tests\Unit;

use App\Services\ShopifyOrderService;
use Mockery;
use Tests\TestCase;

class ShopifyOrderServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_it_fetches_orders()
    {
        // Mock the User model which behaves like a Shopify shop
        $shop = Mockery::mock(\App\Models\User::class)->makePartial();
        $shop->id = 1;

        $shop->shouldReceive('api->rest')->with('GET', '/admin/api/' . config('shopify-app.api_version') . '/orders.json', [])
            ->andReturn([
                'errors' => false,
                'body' => ['orders' => [['id' => 1, 'order_number' => '#1001', 'line_items' => [], 'total_price' => 100]]]
            ]);

        $service = new ShopifyOrderService();
        $orders = $service->fetchOrders($shop);

        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['id']);
    }

    public function test_it_handles_shopify_errors()
    {
        $this->expectException(\Exception::class);

        $shop = Mockery::mock(\App\Models\User::class);
        $shop->shouldReceive('api->rest')->andReturn([
            'errors' => true,
            'body' => 'Error'
        ]);

        $service = new ShopifyOrderService();
        $service->fetchOrders($shop);
    }
}
