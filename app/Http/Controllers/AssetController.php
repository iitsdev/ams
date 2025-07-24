<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetStatus;
use App\Models\Category;
use App\Models\Location;
use App\Models\CheckinCheckoutLog;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetsExport;
use App\Imports\AssetsImport;

class AssetController extends Controller
{
    public function index(Request $request)
    {

        $filters = $request->only('search', 'status', 'category', 'location');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $sortableColumns = ['name', 'created_at'];

        if (!in_array($sortBy, $sortableColumns)) {
            $sortBy = 'created_at';
        }

        //Fetch the assets from the database
        // We use `with()` to eager-load related data and prevent N+1 query issues.        

        $assets = Asset::query()
            ->with(['category', 'status', 'location', 'assignedToUser'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $statusId) {
                if ($statusId === 'all') return;
                $query->where('status_id', $statusId);
            })
            ->when($filters['category'] ?? null, function (Builder $query, $categoryId) {
                if ($categoryId === 'all') return;
                $query->where('category_id', $categoryId);
            })
            ->when($filters['location'] ?? null, function (Builder $query, $locationId) {
                if ($locationId === 'all')  return;
                $query->where('location_id', $locationId);
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate(10)
            ->withQueryString();


        $assets->getCollection()->transform(function ($asset) {
            $asset->depreciation = $this->calculateDepreciation($asset);

            return $asset;
        });

        return Inertia::render('assets/Index', [
            'assets' => $assets,
            'filters' => array_merge($filters, [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ]),
            'dropdowns' => [
                'statuses' => AssetStatus::all(['id', 'name']),
                'categories' => Category::all(['id', 'name']),
                'locations' => Location::all(['id', 'name']),
                'users' => User::all(['id', 'name']),
            ],
            'can' => [
                'create_asset' => auth()->user()->can('create assets'),
            ]
        ]);
    }
    public function show(Asset $asset)
    {
        $asset->load(['category', 'status', 'location', 'assignedToUser', 'maintenanceLogs.performedByUser']);

        $asset->depreciation = $this->calculateDepreciation($asset);

        return Inertia::render('assets/Show', [

            'asset' => $asset,
        ]);
    }

    private function calculateDepreciation(Asset $asset): ?array
    {

        // Ensure we have all the required data
        if (!$asset->purchase_cost || !$asset->purchase_date || !$asset->category?->lifespan_months) {
            return null;
        }

        // Use startOfDay() to ignore time and prevent small calculation errors
        $purchaseDate = $asset->purchase_date->startOfDay();
        $now = now()->startOfDay();

        // If the asset is new, it has not depreciated
        if ($purchaseDate->isAfter($now) || $purchaseDate->isSameDay($now)) {
            return [
                'current_value' => round($asset->purchase_cost, 2),
                'monthly_depreciation' => round($asset->purchase_cost / $asset->category->lifespan_months, 2),
            ];
        }

        $ageInMonths = $purchaseDate->diffInMonths($now);
        $lifespanMonths = (int) $asset->category->lifespan_months;

        // Cap the age at the asset's total lifespan
        if ($ageInMonths > $lifespanMonths) {
            $ageInMonths = $lifespanMonths;
        }

        $monthlyDepreciation = (float) $asset->purchase_cost / $lifespanMonths;
        $totalDepreciation = $monthlyDepreciation * $ageInMonths;
        $currentValue = (float) $asset->purchase_cost - $totalDepreciation;

        return [
            'current_value' => round($currentValue, 2),
            'monthly_depreciation' => round($monthlyDepreciation, 2),
        ];
    }

    public function create()
    {

        return Inertia::render('assets/Create', [
            'dropdowns' => [
                'statuses' => AssetStatus::all(['id', 'name']),
                'categories' => Category::all(['id', 'name']),
                'locations' => Location::all(['id', 'name']),
            ]
        ]);
    }



    public function store(Request $request)
    {

        $validateData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'status_id' => 'required|exists:asset_statuses,id',
            'location_id' => 'required|exists:locations,id',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'description' => 'nullable|string',

        ]);

        $validateData['created_by'] = auth()->id();
        $validateData['asset_tag'] = 'AMS-' . random_int(100000, 999999);

        Asset::create($validateData);

        return to_route('assets.index')->with('success', 'Asset created successfully!');
    }

    public function edit(Asset $asset)
    {
        return Inertia::render('assets/Edit', [
            'asset' => $asset,
            'dropdowns' => [
                'statuses' => AssetStatus::all(['id', 'name']),
                'categories' => Category::all(['id', 'name']),
                'locations' => Location::all(['id', 'name']),
            ],
        ]);
    }

    public function update(Request $request, Asset $asset)
    {

        $validateData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'status_id' => 'required|exists:asset_statuses,id',
            'location_id' => 'required|exists:locations,id',
            'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('assets')->ignore($asset->id)],
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'description' => 'nullable|string',
        ]);

        $asset->update($validateData);

        return to_route('assets.index')->with('success', 'Asset updated successfully!');
    }

    public function destroy(Asset $asset)
    {

        $asset->delete();

        return to_route('assets.index')->with('success', 'Asset deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:assets,id',
        ]);

        Asset::whereIn('id', $validated['ids'])->delete();

        $count = count($validated['ids']);

        return to_route('assets.index')->with('success', "$count " . str('asset')->plural($count) . "deleted successfully!");
    }



    public function export()
    {
        return Excel::download(new AssetsExport, 'assets.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', File::types(['csv', 'xlsx', 'xls'])],
        ]);

        try {
            Excel::import(new AssetsImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Assets imported successfully!');
    }

    public function assign(Request $request, Asset $asset)
    {

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $deployedStatus = AssetStatus::where('name', 'Deployed')->firstOrFail();

        $asset->update([
            'assigned_to' => $validated['user_id'],
            'status_id' => $deployedStatus->id,
        ]);

        CheckinCheckoutLog::create([
            'asset_id' => $asset->id,
            'user_id' => $validated['user_id'],
            'action' => 'checkout',
            'timestamp' => now(),
        ]);

        return back()->with('success', 'Asset assigned successfully!');
    }

    public function barcode(Asset $asset)
    {

        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = $generator->getBarcode(
            $asset->asset_tag,
            $generator::TYPE_CODE_128,
            2,
            50
        );

        $barcodeWidth = imagesx(imagecreatefromstring($barcodeImage));
        $textHeight = 20; // Height for the text area
        $totalHeight = 50 + $textHeight;

        $finalImage = imagecreatetruecolor($barcodeWidth, $totalHeight);

        $backgroundColor = imagecolorallocate($finalImage, 255, 255, 255);
        $textColor = imagecolorallocate($finalImage, 0, 0, 0);
        imagefill($finalImage, 0, 0, $backgroundColor);

        $barcodeSource = imagecreatefromstring($barcodeImage);
        imagecopy($finalImage, $barcodeSource, 0, 0, 0, 0, $barcodeWidth, 50);

        $font = 4;
        $textWidth = imagefontwidth($font) * strlen($asset->asset_tag);
        $textX = ($barcodeWidth - $textWidth) / 2;
        imagestring($finalImage, $font, $textX, 55, $asset->asset_tag, $textColor);

        ob_start();
        imagepng($finalImage);
        $finalImageData = ob_get_clean();
        imagedestroy($finalImage);


        return response($finalImageData)->header('Content-Type', 'image/png');
    }

    public function printLabel(Asset $asset)
    {
        return Inertia::render('assets/PrintLabel', [
            'asset' => $asset,
        ]);
    }
}
