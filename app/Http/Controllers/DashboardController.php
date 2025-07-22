<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount([
                'assets as assets_in_use_count' => function ($query) {
                    $query->whereHas('status', fn($q) => $q->where('name', 'Deployed'));
                },
                'assets as assets_in_store_count' => function ($query) {
                    $query->whereHas('status', fn($q) => $q->where('name', 'In Stock'));
                },
                'assets as assets_in_repair_count' => function ($query) {
                    $query->whereHas('status', fn($q) => $q->where('name', 'In Repair'));
                },
            ])
            ->withCount('assets as total_assets_count')
            ->get();

        return Inertia::render('Dashboard', [
            'assetSummary' => $categories,
        ]);
    }

    public function show(Asset $asset)
    {
        // Eager-load all the relationships we want to display
        $asset->load(['category', 'status', 'location', 'assignedToUser', 'maintenanceLogs.performedByUser']);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
        ]);
    }
}
