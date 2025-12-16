<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetStatus;
use App\Models\Category;
use App\Models\Location;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\CheckinCheckoutLog;
use App\Models\AssetAssignment;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only('search', 'status', 'category', 'location', 'assigned_user');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $sortableColumns = ['name', 'created_at'];

        if (!in_array($sortBy, $sortableColumns)) {
            $sortBy = 'created_at';
        }

        $assets = Asset::query()
            ->with(['category', 'status', 'location', 'assignedToUser', 'supplier'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
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
                if ($locationId === 'all') return;
                $query->where('location_id', $locationId);
            })
            ->when($filters['assigned_user'] ?? null, function (Builder $query, $userId) {
                if ($userId === 'all') return;
                if ($userId === 'unassigned') {
                    $query->whereNull('assigned_to');
                } else {
                    $query->where('assigned_to', $userId);
                }
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        $assets->getCollection()->transform(function ($asset) {
            $asset->depreciation = $this->calculateDepreciation($asset);
            // Make sure the years_since_purchase accessor is called
            $asset->makeVisible('years_since_purchase');
            return $asset;
        });

        return Inertia::render('assets/Index', [
            'assets' => $assets,
            'filters' => array_merge($filters, [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ]),
            'dropdowns' => [
                'users' => User::all(['id', 'name']),
                'statuses' => AssetStatus::all(['id', 'name']),
                'categories' => Category::all(['id', 'name']),
                'locations' => Location::all(['id', 'name']),
            ],
            'can' => [
                'create_asset' => auth()->user()->can('create assets'),
                'edit_asset' => auth()->user()->can('edit assets'),
                'assign_asset' => auth()->user()->can('assign assets'),
            ]
        ]);
    }
    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'status',
            'location',
            'brand',
            'supplier',
            'assignedToUser.department',
            'assignments.user',              // Changed from assignedToUser
            'assignments.assignedByUser',
            'assignments.returnedByUser',
            'maintenanceLogs.performedByUser'
        ]);

        // Calculate depreciation
        if ($asset->purchase_cost && $asset->purchase_date) {
            $purchaseDate = Carbon::parse($asset->purchase_date);
            $monthsSincePurchase = $purchaseDate->diffInMonths(now());
            $usefulLifeMonths = 60; // 5 years
            
            $monthlyDepreciation = $asset->purchase_cost / $usefulLifeMonths;
            $accumulatedDepreciation = min(
                $monthlyDepreciation * $monthsSincePurchase,
                $asset->purchase_cost
            );
            $currentValue = max(0, $asset->purchase_cost - $accumulatedDepreciation);

            $asset->depreciation = [
                'monthly_depreciation' => number_format($monthlyDepreciation, 2),
                'accumulated_depreciation' => number_format($accumulatedDepreciation, 2),
                'current_value' => number_format($currentValue, 2),
            ];
        }

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
                'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
            'brand_id' => 'nullable|exists:brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'model' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'purchase_date' => 'nullable|date',
            'deployed_at' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
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
                'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
            'brand_id' => 'nullable|exists:brands,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'model' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('assets')->ignore($asset->id)],
            'purchase_date' => 'nullable|date',
            'deployed_at' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
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
            'notes' => 'nullable|string',
        ]);

        // Get "In Use" status
        $inUseStatus = AssetStatus::where('name', 'In Use')->first();
        
        // Update asset
        $asset->update([
            'assigned_to' => $validated['user_id'],
            'status_id' => $inUseStatus->id,
            'deployed_at' => $asset->deployed_at ?? now(),
        ]);

        // Create assignment record
        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $validated['user_id'],  // Changed from assigned_to
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Asset assigned successfully!');
    }

    public function reassign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Return current assignment
        $currentAssignment = AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update([
                'returned_at' => now(),
                'returned_by' => auth()->id(),
            ]);
        }

        // Create new assignment
        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $validated['user_id'],  // Changed from assigned_to
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update asset
        $asset->update([
            'assigned_to' => $validated['user_id'],
        ]);

        return back()->with('success', 'Asset reassigned successfully!');
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

    public function getAssignments(Asset $asset)
    {
        $assignments = $asset->assignments()
            ->with(['user.department', 'assignedBy'])
            ->orderBy('assigned_at', 'desc')
            ->get();

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'name' => $asset->name,
                'asset_tag' => $asset->asset_tag,
                'assignments' => $assignments,
            ]
        ]);
    }

    public function unassign(Asset $asset)
    {
        // Check if asset is assigned
        if (!$asset->assigned_to) {
            return back()->with('error', 'Asset is not assigned to anyone.');
        }

        $inStockStatus = AssetStatus::where('name', 'In Stock')->firstOrFail();
        
        // Close current assignment
        AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->update(['returned_at' => now()]);

        // Log the checkin
        CheckinCheckoutLog::create([
            'asset_id' => $asset->id,
            'user_id' => $asset->assigned_to,
            'action' => 'checkin',
            'timestamp' => now(),
        ]);

        // Update asset - remove assignment and set to In Stock
        $asset->update([
            'assigned_to' => null,
            'status_id' => $inStockStatus->id,
        ]);

        return back()->with('success', 'Asset unassigned successfully and status set to In Stock!');
    }
}
