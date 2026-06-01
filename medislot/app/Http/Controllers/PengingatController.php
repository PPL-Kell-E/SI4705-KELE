<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Notifikasi;
use App\Models\Pengingat;
use App\Models\PengingatWaktu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengingatController extends Controller
{
    const OFFSET_OPTIONS = [
        30   => '30 menit sebelum',
        60   => '1 jam sebelum',
        180  => '3 jam sebelum',
        360  => '6 jam sebelum',
        720  => '12 jam sebelum',
        1440 => '24 jam sebelum (H-1)',
        2880 => '48 jam sebelum (H-2)',
    ];

    public function index()
    {
        $user = Auth::user();

        try {
            // Auto-create default reminder untuk jadwal mendatang yang belum punya reminder
            $this->autoCreateDefaultReminders($user->id);

            $pengingat = Pengingat::with(['jadwal', 'waktu'])
                ->where('user_id', $user->id)
                ->whereHas('jadwal', fn($q) => $q->where('status', 'mendatang'))
                ->orderBy('created_at')
                ->get();

            $jadwalTanpaReminder = Jadwal::where('user_id', $user->id)
                ->where('status', 'mendatang')
                ->whereNotIn('id', $pengingat->pluck('jadwal_id')->toArray())
                ->orderBy('tanggal')
                ->get();

            $this->processReminders($user->id);

            $notifikasi  = Notifikasi::where('user_id', $user->id)->latest()->take(5)->get();
            $unreadCount = Notifikasi::where('user_id', $user->id)->where('is_read', false)->count();
            $error       = null;

        } catch (\Exception $e) {
            $pengingat           = collect();
            $jadwalTanpaReminder = collect();
            $notifikasi          = collect();
            $unreadCount         = 0;
            $error               = 'Data reminder gagal dimuat.';
        }

        $pengingatJson = $pengingat->map(function ($p) {
            return [
                'id'        => $p->id,
                'is_active' => $p->is_active,
                'jadwal'    => [
                    'id'               => $p->jadwal?->id,
                    'nama'             => $p->jadwal?->jenis_pemeriksaan,
                    'tanggal'          => $p->jadwal?->tanggal?->format('Y-m-d'),
                    'waktu'            => substr($p->jadwal?->waktu ?? '00:00', 0, 5),
                    'fasilitas_klinik' => $p->jadwal?->fasilitas_klinik,
                ],
                'waktu' => $p->waktu->map(function ($w) {
                    return [
                        'id'           => $w->id,
                        'offset_menit' => $w->offset_menit,
                        'label'        => $w->offsetLabel(),
                        'chip'         => $w->chipLabel(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('pengingat.index', compact(
            'pengingat', 'jadwalTanpaReminder', 'notifikasi',
            'unreadCount', 'error', 'pengingatJson'
        ) + ['offsetOptions' => self::OFFSET_OPTIONS]);
    }

    public function store(Request $request)
    {
        $keys = implode(',', array_keys(self::OFFSET_OPTIONS));
        $request->validate([
            'jadwal_id'      => 'required|integer|exists:jadwal,id',
            'offset_menit'   => 'required|array|min:1',
            'offset_menit.*' => "integer|in:{$keys}",
        ]);

        $user   = Auth::user();
        $jadwal = Jadwal::where('user_id', $user->id)
            ->where('status', 'mendatang')
            ->findOrFail($request->jadwal_id);

        if (Pengingat::where('jadwal_id', $jadwal->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Reminder untuk jadwal ini sudah ada.');
        }

        $pengingat = Pengingat::create([
            'jadwal_id' => $jadwal->id,
            'user_id'   => $user->id,
            'is_active' => true,
        ]);

        $jadwalDt = Carbon::parse($jadwal->tanggal->format('Y-m-d') . ' ' . $jadwal->waktu);
        foreach (array_unique($request->offset_menit) as $offset) {
            if ((clone $jadwalDt)->subMinutes($offset)->gt(now())) {
                $pengingat->waktu()->create(['offset_menit' => $offset]);
            }
        }

        return back()->with('success', 'Reminder berhasil ditambahkan.');
    }

    public function toggle(Pengingat $pengingat)
    {
        abort_if($pengingat->user_id !== Auth::id(), 403);
        $pengingat->update(['is_active' => !$pengingat->is_active]);
        return response()->json(['is_active' => $pengingat->is_active]);
    }

    public function update(Request $request, Pengingat $pengingat)
    {
        abort_if($pengingat->user_id !== Auth::id(), 403);

        $keys = implode(',', array_keys(self::OFFSET_OPTIONS));
        $request->validate([
            'is_active'      => 'sometimes|boolean',
            'offset_menit'   => 'required|array|min:1',
            'offset_menit.*' => "integer|in:{$keys}",
        ]);

        $jadwal   = $pengingat->jadwal;
        $jadwalDt = Carbon::parse($jadwal->tanggal->format('Y-m-d') . ' ' . $jadwal->waktu);

        $valid = [];
        foreach (array_unique($request->offset_menit) as $offset) {
            if ((clone $jadwalDt)->subMinutes((int) $offset)->gt(now())) {
                $valid[] = (int) $offset;
            }
        }

        if (empty($valid)) {
            return response()->json(['error' => 'Waktu reminder tidak valid.'], 422);
        }

        $pengingat->waktu()->delete();
        foreach ($valid as $offset) {
            $pengingat->waktu()->create(['offset_menit' => $offset]);
        }

        if ($request->has('is_active')) {
            $pengingat->update(['is_active' => (bool) $request->is_active]);
        }

        return response()->json(['success' => 'Reminder berhasil diperbarui.']);
    }

    // ── Notifikasi ──

    public function checkNotifikasi()
    {
        $user = Auth::user();
        $this->processReminders($user->id);

        $notifikasi = Notifikasi::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'unread_count' => $notifikasi->count(),
            'notifikasi'   => $notifikasi->map(fn($n) => [
                'id'        => $n->id,
                'judul'     => $n->judul,
                'pesan'     => $n->pesan,
                'jadwal_id' => $n->jadwal_id,
                'waktu'     => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markRead($id)
    {
        Notifikasi::where('user_id', Auth::id())->where('id', $id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        Notifikasi::where('user_id', Auth::id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // ── Internal: auto-buat default reminder untuk jadwal tanpa reminder ──

    private function autoCreateDefaultReminders(string $userId): void
    {
        $jadwalIds = Pengingat::where('user_id', $userId)->pluck('jadwal_id')->toArray();

        $jadwalTanpa = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->when(!empty($jadwalIds), fn($q) => $q->whereNotIn('id', $jadwalIds))
            ->get();

        foreach ($jadwalTanpa as $jadwal) {
            $jadwalDt = Carbon::parse($jadwal->tanggal->format('Y-m-d') . ' ' . $jadwal->waktu);

            if (now()->gte($jadwalDt)) continue;

            // Pilih offset terbesar yang masih valid (dari H-2 sampai 30 menit)
            $defaultOffset = null;
            foreach ([2880, 1440, 720, 360, 180, 60, 30] as $offset) {
                if ((clone $jadwalDt)->subMinutes($offset)->gt(now())) {
                    $defaultOffset = $offset;
                    break;
                }
            }

            if ($defaultOffset === null) continue;

            $p = Pengingat::create([
                'jadwal_id' => $jadwal->id,
                'user_id'   => $userId,
                'is_active' => true,
            ]);
            $p->waktu()->create(['offset_menit' => $defaultOffset]);
        }
    }

    // ── Internal: proses reminder yang sudah waktunya ──

    private function processReminders(string $userId): void
    {
        $now = Carbon::now();

        $pengingat = Pengingat::with(['jadwal', 'waktu'])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereHas('jadwal', fn($q) => $q->where('status', 'mendatang'))
            ->get();

        foreach ($pengingat as $p) {
            $jadwal   = $p->jadwal;
            $jadwalDt = Carbon::parse($jadwal->tanggal->format('Y-m-d') . ' ' . $jadwal->waktu);

            if ($now->gte($jadwalDt)) continue;

            foreach ($p->waktu as $wt) {
                $reminderDt = (clone $jadwalDt)->subMinutes($wt->offset_menit);

                if ($now->gte($reminderDt) && !Notifikasi::where('pengingat_waktu_id', $wt->id)->exists()) {
                    $waktuLabel = substr($jadwal->waktu, 0, 5);
                    $hariLabel  = $wt->offset_menit >= 1440 ? 'besok' : 'segera';

                    Notifikasi::create([
                        'user_id'             => $userId,
                        'jadwal_id'           => $jadwal->id,
                        'pengingat_waktu_id'  => $wt->id,
                        'judul'               => 'Pengingat Jadwal ' . ($wt->offset_menit >= 1440 ? 'Besok' : 'Segera') . '!',
                        'pesan'               => "Jangan lupa jadwal {$jadwal->jenis_pemeriksaan} {$hariLabel} jam {$waktuLabel} WIB di {$jadwal->fasilitas_klinik}.",
                        'is_read'             => false,
                        'notified_at'         => $reminderDt,
                    ]);
                }
            }
        }
    }
}
