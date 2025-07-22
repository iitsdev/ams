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
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

    public function create()
    {

        return Inertia::render('assets/Create', [
            'dropdowns' => [
                'statuses' => AssetStatus::all('id', 'name'),
                'categories' => Category::all('id', 'name'),
                'locations' => Location::all('id', 'name'),
            ]
        ]);
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'status', 'location', 'assignedToUser', 'maintenanceLogs.performedByUser']);

        return Inertia::render('assets/Show', [

            'asset' => $asset, 
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

        return to_route('assets.index')->with('success', "$count" . str('asset')->plural($count) . "deleted successfully!");
    }



    public function export()
    {
        $fileName = 'assets.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Create a new StreamedResponse
        return new StreamedResponse(function () {
            // Open the output stream
            $handle = fopen('php://output', 'w');

            // Add the header row
            fputcsv($handle, [
                'ID',
                'Asset Name',
                'Asset Tag',
                'Serial Number',
                'Category',
                'Status',
                'Location',
                'Assigned To',
                'Purchase Date',
                'Purchase Cost',
            ]);

            // Add the data rows by chunking for better performance
            Asset::with(['category', 'status', 'location', 'assignedToUser'])
                ->chunk(1000, function ($assets) use ($handle) {
                    foreach ($assets as $asset) {
                        fputcsv($handle, [
                            $asset->id,
                            $asset->name,
                            $asset->asset_tag,
                            $asset->serial_number,
                            $asset->category?->name,
                            $asset->status?->name,
                            $asset->location?->name,
                            $asset->assignedToUser?->name,
                            $asset->purchase_date,
                            $asset->purchase_cost,
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers); // Pass the status code and headers to the response

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
}
