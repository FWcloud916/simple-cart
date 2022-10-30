<?php

namespace Fwcloud916\SimpleCart\Database\Factories;

use Fwcloud916\SimpleCart\Models\SimpleCart;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimpleCartFactory extends Factory
{
    protected $model = SimpleCart::class;

    public function definition()
    {
        return [
            'user_id' => $this->faker->randomNumber(1, 100),
            'product_id' => $this->faker->randomNumber(1, 100),
            'coupon_id' => $this->faker->randomNumber(1, 100),
            'quantity' => $this->faker->randomNumber(1, 100),
            'price' => $this->faker->randomNumber(1, 100),
            'discount' => $this->faker->randomNumber(1, 100),
            'total' => $this->faker->randomNumber(1, 100),
        ];
    }
}
