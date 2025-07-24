<?php

namespace App\Imports;

use App\Models\Asset;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AssetsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $purchaseDate = is_numeric($row['purchase_date']) ? Carbon::instance(Date::excelToDateTimeObject($row['purchase_date'])) : Carbon::parse($row['purchase_date']);

        return new Asset([
            'name' => $row['name'],
            'asset_tag' => 'AMS-' . random_int(100000, 999999),
            'serial_number' => $row['serial_number'],
            'purchase_cost' => $row['purchase_cost'],
            'purchase_date' => $purchaseDate,
            'category_id'   => 1,
            'status_id' => 1,
            'location_id' => 1,
            'created_by' => auth()->id(),
        ]);
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'purchase_cost' => 'required|numeric',
            'purchase_date' => 'required',
            'serial_number' => 'nullable|string|unique:assets, serial_number',
        ];
    }
}
