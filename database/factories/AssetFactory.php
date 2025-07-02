<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Location;
use App\Models\AssetStatus;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' ' . fake()->word(),
            'asset_tag' => fake()->unique()->numerify('AMS-######'),
            'serial_number' => fake()->unique()->ean13(),
            'description' => fake()->sentence(),
            'category_id' => Category::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'status_id' => AssetStatus::firstOrCreate(['name' => 'In Stock'])->id, // Default to 'In Stock'
            'purchase_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'purchase_cost' => fake()->randomFloat(2, 100, 3000),
            'warranty_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'assigned_to' => null, // Not assigned by default
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
