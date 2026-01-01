<?php

namespace Tests\Feature;

use App\Actions\ProcessOrderAction;
use App\Models\SentOrder;
use App\Models\ShopSetting;
use App\Models\User;
use App\Services\ShopifyOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_index_displays_orders()
    {
        $user = User::factory()->create();
        ShopSetting::factory()->create(['user_id' => $user->id]);

        $this->mock(ShopifyOrderService::class, function ($mock) {
            $mock->shouldReceive('fetchOrders')
                ->once()
                ->andReturn([
                    [
                        'id' => 100,
                        'order_number' => '#1001',
                        'current_total_price' => '100.00',
                        'financial_status' => 'paid',
                        'line_items' => [['quantity' => 1, 'grams' => 1000]],
                        'shipping_address' => ['name' => 'John', 'phone' => '123'],
                        'contact_email' => 'test@example.com',
                        'note' => '',
                        'phone' => '123',
                        'can_be_processed' => true,
                        // Add other fields used in view checks
                    ]
                ]);
        });

        $response = $this->actingAs($user)
            ->get(route('orders'));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
    }

    public function test_process_selected_orders_calls_action()
    {
        $user = User::factory()->create();
        ShopSetting::factory()->create(['user_id' => $user->id]);

        $this->mock(ProcessOrderAction::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    'processed' => [['order_number' => '#1001']],
                    'failed' => []
                ]);
        });

        $response = $this->actingAs($user)
            ->post(route('orders.process-selected'), [
                'selected_orders' => [
                    ['order_id' => 100, 'order_number' => '#1001']
                ]
            ]);

        $response->assertJson([
            'success' => true,
            'processed' => [['order_number' => '#1001']]
        ]);
    }

    public function test_sent_orders_displays_history()
    {
        $user = User::factory()->create();
        $shopSetting = ShopSetting::factory()->create(['user_id' => $user->id]);

        SentOrder::factory()->create([
            'user_id' => $user->id,
            'shop_setting_id' => $shopSetting->id
        ]);

        $response = $this->actingAs($user)
            ->get(route('orders.sent'));

        $response->assertStatus(200);
        $response->assertViewHas('sentOrders');
    }
}
