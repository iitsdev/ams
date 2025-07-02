<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Asset;
use App\Models\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAssets = Asset::count();
        $deployedAssetsCount = Asset::whereHas('status', fn($query) => $query->where('name', 'Deployed'))->count();

        $assetsByStatus = AssetStatus::query()->withCount('assets')->get();


        $chartData = [
            'labels' => $assetsByStatus->pluck('name'),
            'datasets' => [
                [
                    'data' => $assetsByStatus->pluck('asset_count'),
                    'backgourndColor' => ['#4ade80', '#facc15', '#f87171', '#60a5fa', '#a78bfa'],
                ],
            ],
        ];



        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalAssets,
                'deployed' => $deployedAssetsCount,
            ],
            'assetsByStatusChart' => $chartData,
        ]);
    }
}
