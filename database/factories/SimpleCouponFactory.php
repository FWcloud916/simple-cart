<?php

namespace Fwcloud916\SimpleCart\Database\Factories;

use Fwcloud916\SimpleCart\Models\SimpleCoupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimpleCouponFactory extends Factory
{
    protected $model = SimpleCoupon::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'type' => $this->faker->randomElement(['fixed', 'percent']),
            'value' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
