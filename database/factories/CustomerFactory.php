<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name'    => fake()->name(),
            'email'   => fake()->unique()->safeEmail(),
            // Bangladeshi mobile style, 11 digits (fits the 15-char column)
            'phone'   => fake()->numerify(fake()->randomElement(['017', '018', '019', '016', '015']) . '########'),
            'address' => fake()->streetAddress() . ', ' . fake()->randomElement(['Dhaka', 'Chattogram', 'Bogura', 'Rajshahi', 'Khulna', 'Sylhet']),
        ];
    }
}
