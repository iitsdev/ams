<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AuditEntry;
use App\Models\AuditSession;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuditSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $sessions = AuditSession::with(['location', 'startedBy'])
            ->latest()
            ->paginate(15)
            ->through(function ($s) {
                $createdAt = $s->created_at instanceof Carbon ? $s->created_at : Carbon::parse($s->created_at);
                $closedAt = $s->closed_at ? ($s->closed_at instanceof Carbon ? $s->closed_at : Carbon::parse($s->closed_at)) : null;
                return [
                    'id' => $s->id,
                    'location' => $s->location?->name,
                    'status' => $s->status,
                    'started_by' => $s->startedBy?->name,
                    'created_at' => $createdAt->toDateTimeString(),
                    'closed_at' => $closedAt?->toDateTimeString(),
                ];
            });

        $locations = Location::orderBy('name')->get(['id', 'name']);

        return Inertia::render('audits/Index', [
            'sessions' => $sessions,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => ['nullable', 'exists:locations,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $session = AuditSession::create([
            'location_id' => $data['location_id'] ?? null,
            'started_by' => Auth::id(),
            'status' => 'open',
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('audits.show', $session->id)
            ->with('success', 'Audit session started');
    }

    public function show(AuditSession $audit): Response
    {
        $audit->load(['location', 'entries.asset.category', 'entries.foundLocation', 'entries.scannedBy']);

        $entries = $audit->entries->map(function ($e) {
            return [
                'id' => $e->id,
                'asset' => [
                    'id' => $e->asset->id,
                    'name' => $e->asset->name,
                    'asset_tag' => $e->asset->asset_tag,
                    'category' => $e->asset->category?->name,
                    'location' => $e->asset->location?->name,
                ],
                'found_location' => $e->foundLocation?->name,
                'scanned_by' => $e->scannedBy?->name,
                'scanned_at' => $e->scanned_at,
                'notes' => $e->notes,
            ];
        });

        $locations = Location::orderBy('name')->get(['id', 'name']);

        return Inertia::render('audits/Show', [
            'audit' => [
                'id' => $audit->id,
                'status' => $audit->status,
                'location' => $audit->location?->only(['id', 'name']),
                'notes' => $audit->notes,
                'created_at' => ($audit->created_at instanceof Carbon ? $audit->created_at : Carbon::parse($audit->created_at))->toDateTimeString(),
                'closed_at' => $audit->closed_at ? (($audit->closed_at instanceof Carbon ? $audit->closed_at : Carbon::parse($audit->closed_at))->toDateTimeString()) : null,
            ],
            'entries' => $entries,
            'locations' => $locations,
        ]);
    }

    public function scan(Request $request, AuditSession $audit)
    {
        abort_unless($audit->status === 'open', 400, 'Audit is closed');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'found_location_id' => ['nullable', 'exists:locations,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $asset = Asset::where('asset_tag', $data['code'])
            ->orWhere('serial_number', $data['code'])
            ->first();

        if (!$asset) {
            return response()->json(['message' => 'Asset not found'], 404);
        }

        $entry = AuditEntry::updateOrCreate(
            ['audit_session_id' => $audit->id, 'asset_id' => $asset->id],
            [
                'scanned_by' => Auth::id(),
                'scanned_at' => now(),
                'found_location_id' => $data['found_location_id'] ?? $asset->location_id,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Scanned',
            'entry' => $entry->load('asset.category', 'foundLocation', 'scannedBy'),
        ]);
    }

    public function close(Request $request, AuditSession $audit)
    {
        abort_unless($audit->status === 'open', 400, 'Audit already closed');

        $audit->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ]);

        return redirect()->route('audits.show', $audit->id)
            ->with('success', 'Audit session closed');
    }

    public function variance(Request $request, AuditSession $audit)
    {
        $audit->load('entries.asset');

        $expectedQuery = Asset::query();
        if ($audit->location_id) {
            $expectedQuery->where('location_id', $audit->location_id);
        }
        $expected = $expectedQuery->pluck('id')->toArray();
        $scanned = $audit->entries->pluck('asset_id')->toArray();

        $missingIds = array_values(array_diff($expected, $scanned));
        $extraIds = array_values(array_diff($scanned, $expected));

        $moved = $audit->entries
            ->filter(function ($e) {
                return $e->asset && $e->found_location_id && $e->asset->location_id !== $e->found_location_id;
            })
            ->values();

        return response()->json([
            'missing' => Asset::with('category', 'location')->whereIn('id', $missingIds)->get(),
            'extra' => Asset::with('category', 'location')->whereIn('id', $extraIds)->get(),
            'moved' => $moved->load('asset.location', 'foundLocation', 'scannedBy'),
        ]);
    }
}
