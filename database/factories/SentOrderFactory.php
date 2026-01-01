<?php

namespace Database\Factories;

use App\Models\SentOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class SentOrderFactory extends Factory
{
    protected $model = SentOrder::class;

    public function definition(): array
    {
        return [
            'shop_setting_id' => \App\Models\ShopSetting::factory(),
            'order_id' => $this->faker->unique()->randomNumber(5),
            'order_number' => '#' . $this->faker->randomNumber(4),
            'portal_reference' => $this->faker->uuid,
            'tracking_id' => 'GX' . $this->faker->randomNumber(8),
            'portal_order_id' => $this->faker->randomNumber(5),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
