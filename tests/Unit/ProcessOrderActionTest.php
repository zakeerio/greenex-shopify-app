<?php

namespace Tests\Unit;

use App\Actions\ProcessOrderAction;
use App\Models\SentOrder;
use App\Models\ShopSetting;
use App\Models\User;
use App\Services\ShopifyOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ProcessOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_processes_orders_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $shopSetting = ShopSetting::factory()->create(['user_id' => $user->id]);

        $shopifyService = Mockery::mock(ShopifyOrderService::class);
        $shopifyService->shouldReceive('getOrder')->andReturn([
            'order_number' => '#1001',
            'fulfillment_status' => null,
            'financial_status' => 'paid',
            'customer' => ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com'],
            'total_price' => 100,
            'billing_address' => [],
            'shipping_address' => [],
            'line_items' => []
        ]);
        $shopifyService->shouldReceive('fulfillOrderWithTracking')->andReturn(true);

        $action = new ProcessOrderAction($shopifyService);

        Http::fake([
            '*/order/save' => Http::response([
                'tracking_id' => 'GX123456',
                'portal_order_id' => 999
            ], 200)
        ]);

        $selectedOrders = [
            [
                'order_id' => 12345,
                'order_number' => '#1001',
                'collect_payment' => 'no' // Assuming strict validation is removed or mocked in DTO
            ]
        ];

        // Act
        $result = $action->execute($user, $selectedOrders);

        // Assert
        $this->assertCount(1, $result['processed']);
        $this->assertCount(0, $result['failed']);

        $this->assertDatabaseHas('sent_orders', [
            'order_id' => 12345,
            'tracking_id' => 'GX123456'
        ]);
    }

    public function test_it_fails_if_duplicate()
    {
        // Arrange
        $user = User::factory()->create();
        $shopSetting = ShopSetting::factory()->create(['user_id' => $user->id]);
        SentOrder::factory()->create([
            'user_id' => $user->id,
            'order_id' => 12345
        ]);

        $shopifyService = Mockery::mock(ShopifyOrderService::class);
        $action = new ProcessOrderAction($shopifyService);

        $selectedOrders = [['order_id' => 12345, 'order_number' => '#1001']];

        // Act
        $result = $action->execute($user, $selectedOrders);

        // Assert
        $this->assertCount(0, $result['processed']);
        $this->assertCount(1, $result['failed']);
        $this->assertEquals('Duplicate', $result['failed'][0]['reason']);
    }
}
