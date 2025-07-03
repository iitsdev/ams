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

        $labels = [];
        $data = [];

        foreach ($assetsByStatus as $status) {
            $labels[] = $status->name;
            $data[] = $status->assets_count;
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#4ADE80', '#FACC15', '#F87171', '#60A5FA', '#A78BFA', '#F472B6'],
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
