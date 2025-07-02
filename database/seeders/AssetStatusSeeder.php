<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssetStatus;


class AssetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AssetStatus::firstOrCreate(['name' => 'In stock']);
        AssetStatus::firstOrCreate(['name' => 'Deployed']);
        AssetStatus::firstOrCreate(['name' => 'In Repair']);
        AssetStatus::firstOrCreate(['name' => 'Archived']);
        AssetStatus::firstOrCreate(['name' => 'Retired']);
        
    }
}
