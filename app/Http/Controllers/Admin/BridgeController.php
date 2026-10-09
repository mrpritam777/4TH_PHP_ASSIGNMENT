<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BridgeRequest;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\BridgeDetails;
use App\Models\Infrastructure;
use App\Models\InfrastructureType;
use App\Models\MaintenanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BridgeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $condition = $request->input('condition');

        $bridges = Infrastructure::ofType('bridge')
            ->with(['bridgeDetails', 'type'])
            ->search($search)
            ->condition($condition)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bridges.index', compact('bridges', 'search', 'condition'));
    }

    public function create(): View
    {
        return view('admin.bridges.form', ['bridge' => null]);
    }

    public function store(BridgeRequest $request): RedirectResponse
    {
        $type = InfrastructureType::where('slug', 'bridge')->firstOrFail();

        $data = $this->validated($request);

        $bridge = DB::transaction(function () use ($type, $data) {
            $infrastructure = Infrastructure::create([
                'type_id' => $type->id,
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => trim(($data['state'] ?? '') . ', ' . ($data['district'] ?? '') . ', ' . ($data['city'] ?? '')),
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $data['image_path'] ?? null,
                'created_by' => auth()->id(),
            ]);

            BridgeDetails::create([
                'infrastructure_id' => $infrastructure->id,
                'bridge_type' => $data['bridge_type'],
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'lanes' => $data['lanes'] ?? 2,
                'material' => $data['material'] ?? null,
                'load_capacity' => $data['load_capacity'] ?? null,
                'structural_condition' => $data['structural_condition'],
                'surface_condition' => $data['surface_condition'],
                'safety_status' => $data['safety_status'],
                'last_inspection_date' => $data['last_inspection_date'] ?? null,
            ]);

            ActivityLog::record('created', 'Bridge', $infrastructure->id, "Admin added a new bridge: {$data['name']}");

            return $infrastructure;
        });

        return redirect()
            ->route('admin.bridges.show', $bridge)
            ->with('success', "Bridge \"{$bridge->name}\" was created successfully.");
    }

    public function show(Infrastructure $bridge): View
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $bridge->load(['bridgeDetails', 'type', 'maintenanceRequests.updates', 'contacts', 'documents']);

        $history = $bridge->maintenanceRequests
            ->whereNotNull('created_at')
            ->sortByDesc(function ($request) {
                return $request->completion_date ?? $request->created_at;
            });

        $gallery = $this->galleryImages($bridge);

        return view('admin.bridges.show', compact('bridge', 'history', 'gallery'));
    }

    public function edit(Infrastructure $bridge): View
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $bridge->load('bridgeDetails');

        return view('admin.bridges.form', ['bridge' => $bridge]);
    }

    public function update(BridgeRequest $request, Infrastructure $bridge): RedirectResponse
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $data = $this->validated($request);

        DB::transaction(function () use ($bridge, $data) {
            $bridge->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => trim(($data['state'] ?? '') . ', ' . ($data['district'] ?? '') . ', ' . ($data['city'] ?? '')),
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $data['image_path'] ?? $bridge->image,
            ]);

            $bridge->bridgeDetails()->update([
                'bridge_type' => $data['bridge_type'],
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'lanes' => $data['lanes'] ?? 2,
                'material' => $data['material'] ?? null,
                'load_capacity' => $data['load_capacity'] ?? null,
                'structural_condition' => $data['structural_condition'],
                'surface_condition' => $data['surface_condition'],
                'safety_status' => $data['safety_status'],
                'last_inspection_date' => $data['last_inspection_date'] ?? null,
            ]);

            ActivityLog::record('updated', 'Bridge', $bridge->id, "Admin updated bridge: {$bridge->name}");
        });

        return redirect()
            ->route('admin.bridges.show', $bridge)
            ->with('success', "Bridge \"{$bridge->name}\" was updated successfully.");
    }

    public function destroy(Infrastructure $bridge): RedirectResponse
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $name = $bridge->name;
        $bridge->delete();

        ActivityLog::record('deleted', 'Bridge', $bridge->id, "Admin deleted bridge: {$name}");

        return redirect()
            ->route('admin.bridges.index')
            ->with('success', "Bridge \"{$name}\" was deleted.");
    }

    public function history(Infrastructure $bridge): View
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $bridge->load('type');

        $history = $bridge->maintenanceRequests()
            ->with('updates')
            ->get()
            ->sortByDesc(function ($request) {
                return $request->completion_date ?? $request->created_at;
            });

        return view('admin.bridges.history', compact('bridge', 'history'));
    }

    protected function validated(BridgeRequest $request): array
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('bridges', 'public');
        } else {
            $data['image_path'] = null;
        }

        return $data;
    }

    protected function galleryImages(Infrastructure $bridge): array
    {
        return [
            $bridge->imageUrl(),
            asset('images/infra/bridge-alt1.svg'),
            asset('images/infra/bridge-alt2.svg'),
        ];
    }
}
