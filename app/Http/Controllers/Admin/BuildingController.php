<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuildingRequest;
use App\Models\ActivityLog;
use App\Models\BuildingDetails;
use App\Models\Infrastructure;
use App\Models\InfrastructureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $condition = $request->input('condition');

        $buildings = Infrastructure::ofType('building')
            ->with(['buildingDetails', 'type'])
            ->search($search)
            ->condition($condition)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.buildings.index', compact('buildings', 'search', 'condition'));
    }

    public function create(): View
    {
        return view('admin.buildings.form', ['building' => null]);
    }

    public function store(BuildingRequest $request): RedirectResponse
    {
        $type = InfrastructureType::where('slug', 'building')->firstOrFail();
        $data = $request->validated();

        $building = DB::transaction(function () use ($type, $data, $request) {
            $image = $request->hasFile('image') ? $request->file('image')->store('buildings', 'public') : null;

            $infrastructure = Infrastructure::create([
                'type_id' => $type->id,
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => $data['address'],
                'address' => $data['address'],
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $image,
                'created_by' => auth()->id(),
            ]);

            BuildingDetails::create([
                'infrastructure_id' => $infrastructure->id,
                'building_type' => $data['building_type'],
                'floors' => $data['floors'] ?? 1,
                'area' => $data['area'] ?? null,
                'department' => $data['department'] ?? null,
            ]);

            ActivityLog::record('created', 'Building', $infrastructure->id, "Admin added a new building: {$data['name']}");

            return $infrastructure;
        });

        return redirect()->route('admin.buildings.show', $building)->with('success', "Building \"{$building->name}\" was created successfully.");
    }

    public function show(Infrastructure $building): View
    {
        abort_unless($building->type?->slug === 'building', 404);

        $building->load(['buildingDetails', 'type', 'maintenanceRequests', 'contacts']);

        return view('admin.buildings.show', ['building' => $building]);
    }

    public function edit(Infrastructure $building): View
    {
        abort_unless($building->type?->slug === 'building', 404);

        $building->load('buildingDetails');

        return view('admin.buildings.form', ['building' => $building]);
    }

    public function update(BuildingRequest $request, Infrastructure $building): RedirectResponse
    {
        abort_unless($building->type?->slug === 'building', 404);

        $data = $request->validated();

        DB::transaction(function () use ($request, $building, $data) {
            $building->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => $data['address'],
                'address' => $data['address'],
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $request->hasFile('image') ? $request->file('image')->store('buildings', 'public') : $building->image,
            ]);

            $building->buildingDetails()->update([
                'building_type' => $data['building_type'],
                'floors' => $data['floors'] ?? 1,
                'area' => $data['area'] ?? null,
                'department' => $data['department'] ?? null,
            ]);

            ActivityLog::record('updated', 'Building', $building->id, "Admin updated building: {$building->name}");
        });

        return redirect()->route('admin.buildings.show', $building)->with('success', "Building \"{$building->name}\" was updated successfully.");
    }

    public function destroy(Infrastructure $building): RedirectResponse
    {
        abort_unless($building->type?->slug === 'building', 404);

        $name = $building->name;
        $building->delete();

        ActivityLog::record('deleted', 'Building', $building->id, "Admin deleted building: {$name}");

        return redirect()->route('admin.buildings.index')->with('success', "Building \"{$name}\" was deleted.");
    }
}
