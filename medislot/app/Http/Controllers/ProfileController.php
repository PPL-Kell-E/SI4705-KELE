<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\HealthData;

/**
 * ProfileController — PKE-1: Pengelolaan Profil
 * Subtasks: PKE-22 (Lihat Profil), PKE-23 (Ubah Profil)
 */
class ProfileController extends Controller
{
    /**
     * PKE-22: Tampilkan data profil pengguna (read-only mode)
     */
    public function show()
    {
        $user    = Auth::user();
        $profile = Profile::firstOrCreate(
            ['id' => $user->id],
            [
                'name'   => $user->full_name ?? explode('@', $user->email)[0],
                'age'    => 1,
                'gender' => 'Lainnya',
            ]
        );

        $healthData = HealthData::where('user_id', $user->id)
                                ->latest('recorded_at')
                                ->first();

        return view('profile.show', compact('profile', 'healthData'));
    }

    /**
     * PKE-23: Simpan perubahan data profil
     * Acceptance Criteria:
     *  - Nama dan Usia wajib diisi
     *  - Usia hanya menerima angka positif
     *  - Muncul notifikasi sukses setelah berhasil
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'age'               => ['required', 'integer', 'min:1', 'max:150'],
            'gender'            => ['required', 'in:Laki-laki,Perempuan,Lainnya'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'address'           => ['nullable', 'string', 'max:500'],
            'birth_date'        => ['nullable', 'date'],
            'avatar'            => ['nullable', 'image', 'max:2048'],
            // Health data
            'blood_type'        => ['nullable', 'in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'height_cm'         => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight_kg'         => ['nullable', 'numeric', 'min:1', 'max:500'],
            'allergies'         => ['nullable', 'string'],
            'chronic_conditions'=> ['nullable', 'string'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'age.required'   => 'Usia wajib diisi.',
            'age.min'        => 'Usia harus berupa angka positif.',
            'gender.required'=> 'Jenis kelamin wajib dipilih.',
        ]);

        $user = Auth::user();

        // Handle avatar upload
        $profile = Profile::firstOrCreate(['id' => $user->id]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
            $profile->avatar_url = Storage::url($path);
        }

        $profile->fill([
            'name'       => $request->name,
            'age'        => (int) $request->age,
            'gender'     => $request->gender,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'birth_date' => $request->birth_date,
        ])->save();

        // Update health data
        $allergies  = $request->allergies
            ? array_map('trim', explode(',', $request->allergies))
            : [];
        $conditions = $request->chronic_conditions
            ? array_map('trim', explode(',', $request->chronic_conditions))
            : [];

        HealthData::updateOrCreate(
            ['user_id' => $user->id],
            [
                'blood_type'         => $request->blood_type,
                'height_cm'          => $request->height_cm,
                'weight_kg'          => $request->weight_kg,
                'allergies'          => $allergies,
                'chronic_conditions' => $conditions,
                'recorded_at'        => now(),
            ]
        );

        return redirect()->route('profile.show')
                         ->with('success', 'Profil berhasil diperbarui! ✓');
    }
}
