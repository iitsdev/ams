<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetStatus;


class AssetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Active',
            'Vacant',
            'Replacement',
            'For Repair',
            'Lost',
            'For Checking',
            'For Disposal',
            'Service Unit',
            'Incomming',
        ];

        foreach ($statuses as $status) {
            AssetStatus::firstOrCreate(['name' => $status]);
        }
    }
}
