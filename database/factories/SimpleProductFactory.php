<?php

namespace Fwcloud916\SimpleCart\Database\Factories;

use Fwcloud916\SimpleCart\Models\SimpleProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimpleProductFactory extends Factory
{
    protected $model = SimpleProduct::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
