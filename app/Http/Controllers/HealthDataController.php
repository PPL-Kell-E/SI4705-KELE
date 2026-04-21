<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\HealthData;

class HealthDataController extends Controller
{
    public function index()
    {
        $healthData = HealthData::latest()->first();
        return view('health.index', compact('healthData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'medical_history' => 'nullable|string',
            'last_checkup' => 'nullable|date',
        ]);

        HealthData::create($validated);

        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'medical_history' => 'nullable|string',
            'last_checkup' => 'nullable|date',
        ]);

        $healthData = HealthData::findOrFail($id);
        $healthData->update($validated);

        return redirect()->back()->with('success', 'Data berhasil diedit');
    }
}
