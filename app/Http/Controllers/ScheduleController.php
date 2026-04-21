<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::orderBy('date', 'asc')->orderBy('time', 'asc')->get();
        return view('schedules.index', compact('schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'checkup_type' => 'required|string|max:255',
            'facility' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'notes' => 'nullable|string',
        ]);

        // Simple validation logic for slot availability (mock)
        $exists = Schedule::where('date', $validated['date'])
                         ->where('time', $validated['time'])
                         ->exists();

        if ($exists) {
            return response()->json(['message' => 'Slot tidak tersedia pada waktu tersebut.'], 422);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Berhasil menambahkan jadwal',
            'schedule' => $schedule
        ]);
    }

    /**
     * Check slot availability.
     */
    public function checkAvailability(Request $request)
    {
        $exists = Schedule::where('date', $request->date)
                         ->where('time', $request->time)
                         ->exists();

        return response()->json(['available' => !$exists]);
    }
}
