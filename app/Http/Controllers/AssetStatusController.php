<?php

namespace App\Http\Controllers;

use App\Models\AssetStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AssetStatusController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $statuses = AssetStatus::query()
            ->withCount('assets')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('settings/asset-statuses/Index', [
            'statuses' => $statuses,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('settings/asset-statuses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:asset_statuses,name',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
        ]);

        AssetStatus::create($validated);

        return redirect()->route('asset-statuses.index')
            ->with('success', 'Status created successfully!');
    }

    public function edit(AssetStatus $assetStatus)
    {
        return Inertia::render('settings/asset-statuses/Edit', [
            'status' => $assetStatus,
        ]);
    }

    public function update(Request $request, AssetStatus $assetStatus)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_statuses')->ignore($assetStatus->id)],
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
        ]);

        $assetStatus->update($validated);

        return redirect()->route('asset-statuses.index')
            ->with('success', 'Status updated successfully!');
    }

    public function destroy(AssetStatus $assetStatus)
    {
        if ($assetStatus->assets()->count() > 0) {
            return back()->with('error', 'Cannot delete status that has assets assigned to it.');
        }

        $assetStatus->delete();

        return redirect()->route('asset-statuses.index')
            ->with('success', 'Status deleted successfully!');
    }
}
