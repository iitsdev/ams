<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function query()
    {

        return Asset::query()->with(['category', 'status', 'location', 'assignedToUser']);
    }

    /**
     * @return array
     */

    public function headings(): array
    {

        return [
            'ID',
            'Asset Name',
            'Asset Tag',
            'Category',
            'Status',
            'Assigned To',
            'Purchase Cost',
            'Purchase Date',
        ];
    }

    /**
     * @param Asset $asset
     * @return array
     */

    public function map($asset): array
    {

        return [
            $asset->id,
            $asset->name,
            $asset->asset_tag,
            $asset->category?->name,
            $asset->status?->name,
            $asset->assignedToUser?->name,
            $asset->purchase_cost,
            $asset->purchase_date,
        ];
    }
}
