<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationSchedule;
use App\Models\ScheduleRecommendation;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    /**
     * Display a listing of all examinations - Catalog page
     */
    public function index()
    {
        $examinations = Examination::where('is_active', true)->get();
        $categories = $examinations->groupBy('category');

        return view('examinations.index', compact('examinations', 'categories'));
    }

    /**
     * Show a single examination detail
     */
    public function show($id)
    {
        $examination = Examination::findOrFail($id);
        $schedules = ExaminationSchedule::where('examination_id', $id)
            ->where('schedule_date', '>=', now())
            ->orderBy('schedule_date')
            ->get();
        $recommendations = ScheduleRecommendation::where('examination_id', $id)
            ->where('is_active', true)
            ->get();

        return view('examinations.show', compact('examination', 'schedules', 'recommendations'));
    }

    /**
     * Show examination management dashboard
     */
    public function dashboard()
    {
        $examinations = Examination::all();
        $totalExaminations = $examinations->count();
        $activeExaminations = $examinations->where('is_active', true)->count();

        return view('examinations.dashboard', compact('examinations', 'totalExaminations', 'activeExaminations'));
    }

    /**
     * Create examination form
     */
    public function create()
    {
        return view('examinations.create');
    }

    /**
     * Store a newly created examination
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
        ]);

        $validated['is_active'] = true;
        Examination::create($validated);

        return redirect()->route('examinations.dashboard')
            ->with('success', 'Pemeriksaan berhasil ditambahkan');
    }

    /**
     * Show edit examination form
     */
    public function edit($id)
    {
        $examination = Examination::findOrFail($id);
        return view('examinations.edit', compact('examination'));
    }

    /**
     * Update examination
     */
    public function update(Request $request, $id)
    {
        $examination = Examination::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $examination->update($validated);

        return redirect()->route('examinations.dashboard')
            ->with('success', 'Pemeriksaan berhasil diperbarui');
    }

    /**
     * Delete examination
     */
    public function destroy($id)
    {
        $examination = Examination::findOrFail($id);
        $examination->delete();

        return redirect()->route('examinations.dashboard')
            ->with('success', 'Pemeriksaan berhasil dihapus');
    }
}
