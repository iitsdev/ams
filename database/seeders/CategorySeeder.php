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
       Category::create(['name' => 'Desktop']);
       Category::create(['name' => 'Laptop']);
       Category::create(['name' => 'Tablet']);
       Category::create(['name' => 'Printer']);
       Category::create(['name' => 'Scanner']);
       Category::create(['name' => 'Projector']);
       Category::create(['name' => 'Monitor']);
       Category::create(['name' => 'Keyboard']);
       Category::create(['name' => 'Mouse']);
       Category::create(['name' => 'Other']);
    }
}
