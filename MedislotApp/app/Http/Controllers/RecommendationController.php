<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRecommendation;
use App\Models\Examination;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * Display all recommendations
     */
    public function index()
    {
        $recommendations = ScheduleRecommendation::with('examination')->paginate(15);
        return view('recommendations.index', compact('recommendations'));
    }

    /**
     * Create recommendation form
     */
    public function create()
    {
        $examinations = Examination::where('is_active', true)->get();
        return view('recommendations.create', compact('examinations'));
    }

    /**
     * Store a new recommendation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'examination_id' => 'required|exists:examinations,id',
            'age_min' => 'required|integer|min:0',
            'age_max' => 'required|integer|min:0|gt:age_min',
            'frequency' => 'required|integer|min:1',
            'frequency_unit' => 'required|in:hari,minggu,bulan,tahun',
            'description' => 'required|string',
        ]);

        $validated['is_active'] = true;
        ScheduleRecommendation::create($validated);

        return redirect()->route('recommendations.index')
            ->with('success', 'Rekomendasi berhasil dibuat');
    }

    /**
     * Show recommendation details
     */
    public function show($id)
    {
        $recommendation = ScheduleRecommendation::with('examination')->findOrFail($id);
        return view('recommendations.show', compact('recommendation'));
    }

    /**
     * Edit recommendation form
     */
    public function edit($id)
    {
        $recommendation = ScheduleRecommendation::findOrFail($id);
        $examinations = Examination::where('is_active', true)->get();
        return view('recommendations.edit', compact('recommendation', 'examinations'));
    }

    /**
     * Update recommendation
     */
    public function update(Request $request, $id)
    {
        $recommendation = ScheduleRecommendation::findOrFail($id);

        $validated = $request->validate([
            'examination_id' => 'required|exists:examinations,id',
            'age_min' => 'required|integer|min:0',
            'age_max' => 'required|integer|min:0|gt:age_min',
            'frequency' => 'required|integer|min:1',
            'frequency_unit' => 'required|in:hari,minggu,bulan,tahun',
            'description' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $recommendation->update($validated);

        return redirect()->route('recommendations.index')
            ->with('success', 'Rekomendasi berhasil diperbarui');
    }

    /**
     * Delete recommendation
     */
    public function destroy($id)
    {
        $recommendation = ScheduleRecommendation::findOrFail($id);
        $recommendation->delete();

        return redirect()->route('recommendations.index')
            ->with('success', 'Rekomendasi berhasil dihapus');
    }
}
