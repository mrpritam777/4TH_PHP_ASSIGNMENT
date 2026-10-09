<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HospitalRequest;
use App\Models\ActivityLog;
use App\Models\HospitalDetails;
use App\Models\Infrastructure;
use App\Models\InfrastructureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HospitalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $hospitals = Infrastructure::ofType('hospital')
            ->with(['hospitalDetails', 'type'])
            ->search($search)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.hospitals.index', compact('hospitals', 'search'));
    }

    public function create(): View
    {
        return view('admin.hospitals.form', ['hospital' => null]);
    }

    public function store(HospitalRequest $request): RedirectResponse
    {
        $type = InfrastructureType::where('slug', 'hospital')->firstOrFail();
        $data = $request->validated();

        $hospital = DB::transaction(function () use ($type, $data, $request) {
            $image = $request->hasFile('image') ? $request->file('image')->store('hospitals', 'public') : null;

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

            HospitalDetails::create([
                'infrastructure_id' => $infrastructure->id,
                'hospital_type' => $data['hospital_type'],
                'beds' => $data['beds'] ?? 0,
                'emergency_phone' => $data['emergency_phone'],
                'emergency_facility' => $request->boolean('emergency_facility'),
                'departments' => $data['departments'] ?? null,
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null,
            ]);

            ActivityLog::record('created', 'Hospital', $infrastructure->id, "Admin added a new hospital: {$data['name']}");

            return $infrastructure;
        });

        return redirect()->route('admin.hospitals.show', $hospital)->with('success', "Hospital \"{$hospital->name}\" was created successfully.");
    }

    public function show(Infrastructure $hospital): View
    {
        abort_unless($hospital->type?->slug === 'hospital', 404);

        $hospital->load(['hospitalDetails', 'type', 'maintenanceRequests', 'contacts']);

        return view('admin.hospitals.show', ['hospital' => $hospital]);
    }

    public function edit(Infrastructure $hospital): View
    {
        abort_unless($hospital->type?->slug === 'hospital', 404);

        $hospital->load('hospitalDetails');

        return view('admin.hospitals.form', ['hospital' => $hospital]);
    }

    public function update(HospitalRequest $request, Infrastructure $hospital): RedirectResponse
    {
        abort_unless($hospital->type?->slug === 'hospital', 404);

        $data = $request->validated();

        DB::transaction(function () use ($request, $hospital, $data) {
            $hospital->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'location' => $data['address'],
                'address' => $data['address'],
                'construction_year' => $data['construction_year'] ?? null,
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'image' => $request->hasFile('image') ? $request->file('image')->store('hospitals', 'public') : $hospital->image,
            ]);

            $hospital->hospitalDetails()->update([
                'hospital_type' => $data['hospital_type'],
                'beds' => $data['beds'] ?? 0,
                'emergency_phone' => $data['emergency_phone'],
                'emergency_facility' => $request->boolean('emergency_facility'),
                'departments' => $data['departments'] ?? null,
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null,
            ]);

            ActivityLog::record('updated', 'Hospital', $hospital->id, "Admin updated hospital: {$hospital->name}");
        });

        return redirect()->route('admin.hospitals.show', $hospital)->with('success', "Hospital \"{$hospital->name}\" was updated successfully.");
    }

    public function destroy(Infrastructure $hospital): RedirectResponse
    {
        abort_unless($hospital->type?->slug === 'hospital', 404);

        $name = $hospital->name;
        $hospital->delete();

        ActivityLog::record('deleted', 'Hospital', $hospital->id, "Admin deleted hospital: {$name}");

        return redirect()->route('admin.hospitals.index')->with('success', "Hospital \"{$name}\" was deleted.");
    }
}
