<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;


class MaintenanceLogController extends Controller
{
    public function store(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:225',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'performed_at' => 'required|date',

        ]);

        $validated['performed_by'] = auth()->id();

        $asset->maintenanceLogs()->create($validated);

        return back()->with('success', 'Maintenace log added successfully.');
    }
}
