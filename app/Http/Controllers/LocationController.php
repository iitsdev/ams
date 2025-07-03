<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{

    public function index()
    {

        return Inertia::render('settings/locations/Index', [

            'locations' => Location::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:225|unique:locations,name',
        ]);

        Location::create($validated);

        return back()->with('success', 'Location created successfully!');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('locations')->ignore($location->id)],
        ]);

        $location->update($validated);

        return back()->with('success', 'Location update successfuly.');
    }

    public function destroy(Location $location)
    {

        $location->delete();

        return back()->with('success', 'Location deleted successfully!');
    }
}
