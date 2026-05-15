<?php

namespace App\Http\Controllers;

use App\Models\HealthData;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthDataController extends Controller
{
    public function index()
    {
        $healthData = HealthData::where('user_id', Auth::id())->first();
        $profile    = Profile::find(Auth::id());
        return view('data-kesehatan.index', compact('healthData', 'profile'));
    }

    public function export()
    {
        $healthData = HealthData::where('user_id', Auth::id())->first();
        $profile    = Profile::find(Auth::id());
        $user       = Auth::user();
        return view('data-kesehatan.export', compact('healthData', 'profile', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'height_cm'          => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight_kg'          => ['nullable', 'numeric', 'min:1', 'max:500'],
            'blood_type'         => ['nullable', 'in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies'          => ['nullable', 'string', 'max:255'],
            'chronic_conditions' => ['nullable', 'string', 'max:255'],
            'recorded_at'        => ['nullable', 'date'],
        ]);

        $isNew = !HealthData::where('user_id', Auth::id())->exists();

        HealthData::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'height_cm'          => $request->height_cm ?: null,
                'weight_kg'          => $request->weight_kg ?: null,
                'blood_type'         => $request->blood_type ?: null,
                'allergies'          => $request->allergies
                    ? array_map('trim', explode(',', $request->allergies))
                    : [],
                'chronic_conditions' => $request->chronic_conditions
                    ? array_map('trim', explode(',', $request->chronic_conditions))
                    : [],
                'recorded_at'        => $request->recorded_at ?: now(),
            ]
        );

        return response()->json([
            'status'  => 'ok',
            'message' => $isNew ? 'Data berhasil disimpan' : 'Data berhasil diedit',
        ]);
    }
}
