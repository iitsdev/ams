<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;



class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Category::updateOrcreate(['name' => 'Desktop'], ['lifespan_months' => 60]);
        Category::updateOrCreate(['name' => 'Laptop'], ['lifespan_months' => 36]);
        Category::updateOrCreate(['name' => 'Tablet'], ['lifespan_months' => 36]);
        Category::updateOrCreate(['name' => 'Printer'], ['lifespan_months' => 60]);
        Category::updateOrCreate(['name' => 'Scanner'], ['lifespan_months' => 36]);
        Category::updateOrCreate(['name' => 'Projector'], ['lifespan_months' => 36]);
        Category::updateOrCreate(['name' => 'Monitor'], ['lifespan_months' => 60]);
        Category::updateOrCreate(['name' => 'Keyboard'], ['lifespan_months' => 24]);
        Category::updateOrCreate(['name' => 'Mouse'], ['lifespan_months' => 24]);
        Category::updateOrCreate(['name' => 'Other'], ['lifespan_months' => 60]);
    }
}
