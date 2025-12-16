<?php
// filepath: c:\Users\daveg\Desktop\Projects\ams\app\Http\Controllers\DashboardController.php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use App\Models\AssetAssignment;
use App\Models\MaintenanceLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Asset summary by category with status breakdown
        $assetSummary = Category::withCount('assets')
            ->with(['assets' => function ($query) {
                $query->select('id', 'category_id', 'status_id')
                    ->with('status:id,name');
            }])
            ->get()
            ->map(function ($category) {
                // Count assets by status for this category
                $inUse = $category->assets->filter(function ($asset) {
                    return $asset->status && in_array(strtolower($asset->status->name), ['deployed', 'in use', 'assigned']);
                })->count();

                $inStore = $category->assets->filter(function ($asset) {
                    return $asset->status && in_array(strtolower($asset->status->name), ['in stock', 'available', 'in store']);
                })->count();

                $inRepair = $category->assets->filter(function ($asset) {
                    return $asset->status && in_array(strtolower($asset->status->name), ['under maintenance', 'in repair', 'maintenance']);
                })->count();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'assets_count' => $category->assets_count,
                    'in_use' => $inUse,
                    'in_store' => $inStore,
                    'in_repair' => $inRepair,
                ];
            });

        // Overview Statistics
        $totalAssets = Asset::count();
        $totalValue = Asset::sum('purchase_cost');
        $assignedAssets = Asset::whereNotNull('assigned_to')->count();
        $availableAssets = Asset::whereHas('status', function ($query) {
            $query->whereIn(DB::raw('LOWER(name)'), ['in stock', 'available', 'in store']);
        })->count();

        // Assets by Status
        $assetsByStatus = Asset::select('status_id', DB::raw('count(*) as count'))
            ->with('status:id,name')
            ->groupBy('status_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->status->name ?? 'Unknown',
                    'count' => $item->count
                ];
            });

        // Assets by Category with values - Fixed version
        $assetsByCategory = Category::select('categories.id', 'categories.name', DB::raw('COALESCE(COUNT(assets.id), 0) as count'), DB::raw('COALESCE(SUM(assets.purchase_cost), 0) as total_value'))
            ->leftJoin('assets', 'categories.id', '=', 'assets.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                    'value' => $item->total_value
                ];
            });

        // Add uncategorized assets if any exist
        $uncategorizedCount = Asset::whereNull('category_id')->count();
        if ($uncategorizedCount > 0) {
            $uncategorizedValue = Asset::whereNull('category_id')->sum('purchase_cost') ?? 0;
            $assetsByCategory->push([
                'name' => 'Uncategorized',
                'count' => $uncategorizedCount,
                'value' => $uncategorizedValue
            ]);
        }

        // Assets by Location
        $assetsByLocation = Location::select('locations.id', 'locations.name', DB::raw('COALESCE(COUNT(assets.id), 0) as count'))
            ->leftJoin('assets', 'locations.id', '=', 'assets.location_id')
            ->groupBy('locations.id', 'locations.name')
            ->having('count', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count
                ];
            });

        // Recent Assignments - Filter out null assets
        $recentAssignments = AssetAssignment::with(['asset', 'user', 'assignedBy'])
            ->whereNull('returned_at')
            ->whereHas('asset')
            ->whereHas('user')
            ->whereHas('assignedBy')
            ->latest('assigned_at')
            ->take(5)
            ->get()
            ->filter(function ($assignment) {
                return $assignment->asset && $assignment->user && $assignment->assignedBy;
            })
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'asset_name' => $assignment->asset->name,
                    'asset_tag' => $assignment->asset->asset_tag,
                    'asset_id' => $assignment->asset->id,
                    'user_name' => $assignment->user->name,
                    'user_id' => $assignment->user->id,
                    'assigned_by' => $assignment->assignedBy->name,
                    'assigned_at' => $assignment->assigned_at->format('M d, Y'),
                    'duration_days' => $assignment->assigned_at->diffInDays(now())
                ];
            })
            ->values();

        // Upcoming Warranty Expiries (next 30 days)
        $upcomingWarrantyExpiries = Asset::with(['category', 'status'])
            ->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [now(), now()->addDays(30)])
            ->orderBy('warranty_expiry', 'asc')
            ->take(5)
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'asset_tag' => $asset->asset_tag,
                    'category' => $asset->category->name ?? 'N/A',
                    'warranty_expiry' => $asset->warranty_expiry->format('M d, Y'),
                    'days_remaining' => now()->diffInDays($asset->warranty_expiry, false)
                ];
            });

        // Recent Maintenance - Filter out null assets and users
        $recentMaintenance = MaintenanceLog::with(['asset', 'performedByUser'])
            ->whereHas('asset')
            ->whereHas('performedByUser')
            ->latest('performed_at')
            ->take(5)
            ->get()
            ->filter(function ($log) {
                return $log->asset && $log->performedByUser;
            })
            ->map(function ($log) {
                $performedAt = $log->performed_at;
                if (is_string($performedAt)) {
                    $performedAt = \Carbon\Carbon::parse($performedAt);
                }

                return [
                    'id' => $log->id,
                    'asset_name' => $log->asset->name,
                    'asset_tag' => $log->asset->asset_tag,
                    'asset_id' => $log->asset->id,
                    'type' => $log->type,
                    'description' => $log->description,
                    'cost' => $log->cost,
                    'performed_by' => $log->performedByUser->name,
                    'performed_at' => $performedAt->format('M d, Y')
                ];
            })
            ->values();

        // Top Users by Asset Count
        $topUsers = User::select('users.id', 'users.name', DB::raw('count(assets.id) as asset_count'))
            ->leftJoin('assets', 'users.id', '=', 'assets.assigned_to')
            ->groupBy('users.id', 'users.name')
            ->having('asset_count', '>', 0)
            ->orderByDesc('asset_count')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'asset_count' => $user->asset_count
                ];
            });

        // Monthly Asset Trends (last 6 months)
        $monthlyTrends = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Asset::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $monthlyTrends->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Current Depreciation Summary
        $totalCurrentValue = 0;
        $totalOriginalValue = Asset::whereNotNull('purchase_cost')->sum('purchase_cost');
        
        Asset::whereNotNull('purchase_cost')
            ->whereNotNull('purchase_date')
            ->get()
            ->each(function ($asset) use (&$totalCurrentValue) {
                $depreciation = $this->calculateDepreciation($asset);
                if ($depreciation) {
                    $totalCurrentValue += $depreciation['current_value'];
                }
            });

        $totalDepreciation = $totalOriginalValue - $totalCurrentValue;
        $depreciationPercentage = $totalOriginalValue > 0 
            ? round(($totalDepreciation / $totalOriginalValue) * 100, 2) 
            : 0;

        return Inertia::render('Dashboard', [
            'assetSummary' => $assetSummary,
            'stats' => [
                'total_assets' => $totalAssets,
                'total_value' => $totalValue,
                'assigned_assets' => $assignedAssets,
                'available_assets' => $availableAssets,
                'total_users' => User::count(),
                'total_categories' => Category::count(),
                'current_value' => $totalCurrentValue,
                'depreciation' => $totalDepreciation,
                'depreciation_percentage' => $depreciationPercentage,
            ],
            'charts' => [
                'assets_by_status' => $assetsByStatus,
                'assets_by_category' => $assetsByCategory,
                'assets_by_location' => $assetsByLocation,
                'monthly_trends' => $monthlyTrends,
                'top_users' => $topUsers,
            ],
            'recent' => [
                'assignments' => $recentAssignments,
                'maintenance' => $recentMaintenance,
                'warranty_expiries' => $upcomingWarrantyExpiries,
            ]
        ]);
    }

    private function calculateDepreciation(Asset $asset): ?array
    {
        if (!$asset->purchase_cost || !$asset->purchase_date) {
            return null;
        }

        $purchaseDate = $asset->purchase_date;
        $purchaseCost = $asset->purchase_cost;
        $usefulLife = 5; // years
        $monthsInYear = 12;
        $totalMonths = $usefulLife * $monthsInYear;

        $monthsUsed = $purchaseDate->diffInMonths(now());
        $monthlyDepreciation = $purchaseCost / $totalMonths;
        $accumulatedDepreciation = min($monthlyDepreciation * $monthsUsed, $purchaseCost);
        $currentValue = max($purchaseCost - $accumulatedDepreciation, 0);

        return [
            'purchase_cost' => $purchaseCost,
            'current_value' => round($currentValue, 2),
            'accumulated_depreciation' => round($accumulatedDepreciation, 2),
            'monthly_depreciation' => round($monthlyDepreciation, 2),
            'months_used' => $monthsUsed,
            'useful_life_months' => $totalMonths,
        ];
    }
}
