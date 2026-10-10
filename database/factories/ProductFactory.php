<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'        => ucwords(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 50, 5000),
            'stock'       => fake()->numberBetween(20, 500),
        ];
    }
}
