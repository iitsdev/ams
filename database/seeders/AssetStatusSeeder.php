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
            // Core lifecycle
            'Active',        // fallback for assigned
            'Vacant',        // fallback for unassigned
            'In Use',        // preferred for assigned
            'In Stock',      // preferred for unassigned

            // Additional
            'Replacement',
            'For Repair',
            'Lost',
            'For Checking',
            'For Disposal',
            'Service Unit',
            'Incoming',
        ];

        foreach ($statuses as $status) {
            AssetStatus::firstOrCreate(['name' => $status]);
        }
    }
}
