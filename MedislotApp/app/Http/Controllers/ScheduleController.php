<?php

namespace App\Http\Controllers;

use App\Models\ExaminationSchedule;
use App\Models\Examination;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display all schedules
     */
    public function index()
    {
        $schedules = ExaminationSchedule::with('examination')
            ->orderBy('schedule_date')
            ->paginate(15);

        return view('schedules.index', compact('schedules'));
    }

    /**
     * Create schedule form
     */
    public function create()
    {
        $examinations = Examination::where('is_active', true)->get();
        return view('schedules.create', compact('examinations'));
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'examination_id' => 'required|exists:examinations,id',
            'schedule_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
        ]);

        $validated['current_capacity'] = 0;
        $validated['status'] = 'available';

        ExaminationSchedule::create($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat');
    }

    /**
     * Show schedule details
     */
    public function show($id)
    {
        $schedule = ExaminationSchedule::with('examination', 'bookings')->findOrFail($id);
        return view('schedules.show', compact('schedule'));
    }

    /**
     * Edit schedule form
     */
    public function edit($id)
    {
        $schedule = ExaminationSchedule::findOrFail($id);
        $examinations = Examination::where('is_active', true)->get();
        return view('schedules.edit', compact('schedule', 'examinations'));
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = ExaminationSchedule::findOrFail($id);

        $validated = $request->validate([
            'examination_id' => 'required|exists:examinations,id',
            'schedule_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,full,cancelled',
        ]);

        $schedule->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui');
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        $schedule = ExaminationSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dihapus');
    }
}
