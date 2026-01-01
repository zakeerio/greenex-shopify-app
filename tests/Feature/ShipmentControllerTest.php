<?php

namespace Tests\Feature;

use App\Models\ShopSetting;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ShipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_index_displays_shipments()
    {
        $user = User::factory()->create();
        ShopSetting::factory()->create(['user_id' => $user->id]);

        $this->mock(ShipmentService::class, function ($mock) {
            $mock->shouldReceive('getShipments')
                ->once()
                ->andReturn([
                    'data' => [
                        'parcels' => [
                            [
                                'id' => 1,
                                'tracking_number' => 'GX123',
                                'tracking_id' => 'GX123',
                                'label_url' => 'http://example.com/label1.pdf',
                                'customer_name' => 'John Doe',
                                'customer_phone' => '1234567890',
                                'customer_address' => '123 Main St, New York',
                                'city' => 'New York',
                                'amount' => 50.00,
                                'cod_amount' => 50.00,
                                'invoice_no' => 'INV-001',
                                'deliveryType' => 'Normal',
                                'statusName' => 'Pending',
                                'merchant_name' => 'Test Merchant',
                                'merchant_address' => '456 Market St',
                                'merchant_phone' => '0987654321',
                                'pieces' => 1,
                                'weight' => 2.5,
                                'description' => 'Test Parcel',
                                'status' => 1,
                                'status_id' => 1,
                                'created_at' => now()->toIso8601String(),
                                'merchant' => ['business_name' => 'Biz', 'address' => 'Addr', 'user' => ['mobile' => '111']],
                                'hub' => ['name' => 'Main Hub']
                            ]
                        ]
                    ]
                ]);
        });

        $response = $this->actingAs($user)
            ->get(route('shipments'));

        $response->assertStatus(200);
        $response->assertViewHas('shipments');
    }

    public function test_bulk_print_redirects_if_get_request()
    {
        $user = User::factory()->create();
        ShopSetting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('shipments.print'));

        $response->assertRedirect(route('shipments'));
    }

    public function test_bulk_print_displays_selected_shipments()
    {
        $user = User::factory()->create();
        ShopSetting::factory()->create([
            'user_id' => $user->id,
            'api_response' => ['user' => ['hub' => ['name' => 'Demo Hub']]]
        ]);

        $this->mock(ShipmentService::class, function ($mock) {
            $mock->shouldReceive('getShipments')
                ->once()
                ->andReturn([
                    'data' => [
                        'parcels' => [
                            [
                                'id' => 1,
                                'tracking_number' => 'GX123',
                                'tracking_id' => 'GX123',
                                'label_url' => 'http://example.com/label1.pdf',
                                'customer_name' => 'John Doe',
                                'customer_phone' => '1234567890',
                                'customer_address' => '123 Main St, New York',
                                'city' => 'New York',
                                'amount' => 50.00,
                                'cod_amount' => 50.00,
                                'invoice_no' => 'INV-001',
                                'deliveryType' => 'Normal',
                                'statusName' => 'Pending',
                                'merchant_name' => 'Test Merchant',
                                'merchant_address' => '456 Market St',
                                'merchant_phone' => '0987654321',
                                'pieces' => 1,
                                'weight' => 2.5,
                                'description' => 'Test Parcel',
                                'status' => 1,
                                'status_id' => 1,
                                'created_at' => now()->toIso8601String(),
                                'merchant' => ['business_name' => 'Biz', 'address' => 'Addr', 'user' => ['mobile' => '111']],
                                'hub' => ['name' => 'Main Hub']
                            ]
                        ]
                    ]
                ]);
        });

        $response = $this->actingAs($user)
            ->post(route('shipments.print'), [
                'selected_shipments' => [1]
            ]);

        $response->assertStatus(200);
        $response->assertViewHas('shipments', function ($shipments) {
            return count($shipments) === 1 && $shipments[0]['id'] === 1;
        });
        $response->assertViewHas('hubName', 'Demo Hub');
    }
}
