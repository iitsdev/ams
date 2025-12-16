<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Apple', 'website' => 'https://www.apple.com'],
            ['name' => 'Dell', 'website' => 'https://www.dell.com'],
            ['name' => 'HP', 'website' => 'https://www.hp.com'],
            ['name' => 'Lenovo', 'website' => 'https://www.lenovo.com'],
            ['name' => 'Microsoft', 'website' => 'https://www.microsoft.com'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
