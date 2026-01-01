<?php

namespace Database\Factories;

use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopSettingFactory extends Factory
{
    protected $model = ShopSetting::class;

    public function definition(): array
    {
        return [
            'shop_domain' => $this->faker->domainName,
            'email' => $this->faker->email,
            'api_token' => $this->faker->uuid,
            'user_id' => \App\Models\User::factory(),
            'api_response' => ['user' => ['hub' => ['name' => 'Test Hub']]],
        ];
    }
}
