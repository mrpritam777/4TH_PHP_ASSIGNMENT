<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoadRequest;
use App\Models\ActivityLog;
use App\Models\Infrastructure;
use App\Models\InfrastructureType;
use App\Models\RoadDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoadController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $condition = $request->input('condition');

        $roads = Infrastructure::ofType('road')
            ->with(['roadDetails', 'type'])
            ->search($search)
            ->condition($condition)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.roads.index', compact('roads', 'search', 'condition'));
    }

    public function create(): View
    {
        return view('admin.roads.form', ['road' => null]);
    }

    public function store(RoadRequest $request): RedirectResponse
    {
        $type = InfrastructureType::where('slug', 'road')->firstOrFail();
        $data = $request->validated();

        $road = DB::transaction(function () use ($type, $data) {
            $image = $request->hasFile('image') ? $request->file('image')->store('roads', 'public') : null;

            $infrastructure = Infrastructure::create([
                'type_id' => $type->id,
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => $data['location'],
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $image,
                'created_by' => auth()->id(),
            ]);

            RoadDetails::create([
                'infrastructure_id' => $infrastructure->id,
                'road_type' => $data['road_type'],
                'start_point' => $data['start_point'] ?? null,
                'end_point' => $data['end_point'] ?? null,
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'lanes' => $data['lanes'] ?? 2,
            ]);

            ActivityLog::record('created', 'Road', $infrastructure->id, "Admin added a new road: {$data['name']}");

            return $infrastructure;
        });

        return redirect()->route('admin.roads.show', $road)->with('success', "Road \"{$road->name}\" was created successfully.");
    }

    public function show(Infrastructure $road): View
    {
        abort_unless($road->type?->slug === 'road', 404);

        $road->load(['roadDetails', 'type', 'maintenanceRequests', 'contacts']);

        return view('admin.roads.show', ['road' => $road]);
    }

    public function edit(Infrastructure $road): View
    {
        abort_unless($road->type?->slug === 'road', 404);

        $road->load('roadDetails');

        return view('admin.roads.form', ['road' => $road]);
    }

    public function update(RoadRequest $request, Infrastructure $road): RedirectResponse
    {
        abort_unless($road->type?->slug === 'road', 404);

        $data = $request->validated();

        DB::transaction(function () use ($request, $road, $data) {
            $road->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => $data['location'],
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $request->hasFile('image') ? $request->file('image')->store('roads', 'public') : $road->image,
            ]);

            $road->roadDetails()->update([
                'road_type' => $data['road_type'],
                'start_point' => $data['start_point'] ?? null,
                'end_point' => $data['end_point'] ?? null,
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'lanes' => $data['lanes'] ?? 2,
            ]);

            ActivityLog::record('updated', 'Road', $road->id, "Admin updated road: {$road->name}");
        });

        return redirect()->route('admin.roads.show', $road)->with('success', "Road \"{$road->name}\" was updated successfully.");
    }

    public function destroy(Infrastructure $road): RedirectResponse
    {
        abort_unless($road->type?->slug === 'road', 404);

        $name = $road->name;
        $road->delete();

        ActivityLog::record('deleted', 'Road', $road->id, "Admin deleted road: {$name}");

        return redirect()->route('admin.roads.index')->with('success', "Road \"{$name}\" was deleted.");
    }
}
