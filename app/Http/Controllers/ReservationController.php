<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isGuest = $user->hasRole('tamu');

        $baseQuery = Reservation::query()
            ->when($isGuest, fn ($q) => $q->where('user_id', $user->id));

        // Hitung jumlah transaksi per status untuk tab navigasi
        $rawCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            'all' => $rawCounts->sum(),
            'pending' => $rawCounts->get('pending', 0),
            'confirmed' => $rawCounts->get('confirmed', 0),
            'checked_in' => $rawCounts->get('checked_in', 0),
            'checked_out' => $rawCounts->get('checked_out', 0),
            'cancelled' => $rawCounts->get('cancelled', 0),
        ];

        // Ringkasan aktivitas harian front desk
        $pulse = [
            'pending' => $statusCounts['pending'],
            'today_check_in' => (clone $baseQuery)
                ->whereDate('check_in_date', today())
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'in_house' => $statusCounts['checked_in'],
            'today_check_out' => (clone $baseQuery)
                ->whereDate('check_out_date', today())
                ->where('status', 'checked_in')
                ->count(),
        ];

        $reservations = (clone $baseQuery)
            ->with(['user', 'room.roomType', 'review'])
            ->when($request->filled('cari'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('booking_code', 'like', '%' . $request->cari . '%')
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->cari . '%'))
                      ->orWhereHas('room', fn ($r) => $r->where('room_number', 'like', '%' . $request->cari . '%'));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reservations.index', compact('reservations', 'statusCounts', 'pulse', 'isGuest'));
    }

    public function create(Request $request)
    {
        $rooms = Room::with(['roomType.facilities'])
            ->where('status', 'tersedia')
            ->orderBy('room_number')
            ->get();

        $today = Carbon::today();
        $defaultCheckIn = $today->toDateString();

        if ($request->filled('check_in_date')) {
            try {
                $parsedIn = Carbon::parse($request->query('check_in_date'))->startOfDay();
                if ($parsedIn->gte($today)) {
                    $defaultCheckIn = $parsedIn->toDateString();
                }
            } catch (\Exception $e) {
                // Gunakan default hari ini jika format tanggal tidak valid
            }
        }

        $minCheckOut = Carbon::parse($defaultCheckIn)->addDay()->toDateString();
        $defaultCheckOut = $minCheckOut;

        if ($request->filled('check_out_date')) {
            try {
                $parsedOut = Carbon::parse($request->query('check_out_date'))->startOfDay();
                if ($parsedOut->gt(Carbon::parse($defaultCheckIn))) {
                    $defaultCheckOut = $parsedOut->toDateString();
                }
            } catch (\Exception $e) {
                // Gunakan default H+1 jika format tanggal tidak valid
            }
        }

        $selectedRoomId = $request->query('room_id');
        if (!$selectedRoomId || !$rooms->contains('id', (int) $selectedRoomId)) {
            $selectedRoomId = $rooms->first()?->id;
        }

        $guests = in_array(Auth::user()->role ?? '', ['admin', 'resepsionis'])
            ? User::orderBy('name')->get(['id', 'name', 'email', 'role'])
            : collect();

        return view('reservations.create', [
            'rooms' => $rooms,
            'guests' => $guests,
            'selectedRoomId' => $selectedRoomId,
            'defaultCheckIn' => $defaultCheckIn,
            'defaultCheckOut' => $defaultCheckOut,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'room_id.required' => 'Silakan pilih unit kamar terlebih dahulu.',
            'check_in_date.after_or_equal' => 'Tanggal check-in tidak boleh sebelum hari ini.',
            'check_out_date.after' => 'Tanggal check-out harus minimal 1 hari setelah tanggal check-in.',
        ]);

        // Cek bentrok jadwal kamar pada rentang tanggal yang dipilih
        $isConflict = Reservation::where('room_id', $validated['room_id'])
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($validated) {
                $query->where('check_in_date', '<', $validated['check_out_date'])
                    ->where('check_out_date', '>', $validated['check_in_date']);
            })
            ->exists();

        if ($isConflict) {
            return back()
                ->withInput()
                ->withErrors([
                    'room_id' => 'Unit kamar ini sudah dipesan pada rentang tanggal tersebut. Silakan pilih tanggal atau kamar lain.',
                ]);
        }

        $room = Room::with('roomType')->findOrFail($validated['room_id']);
        $nights = Carbon::parse($validated['check_in_date'])
            ->diffInDays(Carbon::parse($validated['check_out_date']));
        $totalPrice = $nights * ($room->roomType->base_price ?? 0);

        $userId = in_array(Auth::user()->role ?? '', ['admin', 'resepsionis']) && !empty($validated['user_id'])
            ? $validated['user_id']
            : Auth::id();

        $data = [
            'user_id' => $userId,
            'room_id' => $room->id,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'status' => in_array(Auth::user()->role ?? '', ['admin', 'resepsionis']) ? 'confirmed' : 'pending',
        ];

        if (Schema::hasColumn('reservations', 'total_nights')) {
            $data['total_nights'] = $nights;
        }

        if (Schema::hasColumn('reservations', 'total_price')) {
            $data['total_price'] = $totalPrice;
        }

        if (Schema::hasColumn('reservations', 'booking_code')) {
            $data['booking_code'] = 'RSV-' . strtoupper(Str::random(6));
        }

        if (Schema::hasColumn('reservations', 'notes')) {
            $data['notes'] = $validated['notes'] ?? null;
        } elseif (Schema::hasColumn('reservations', 'special_requests')) {
            $data['special_requests'] = $validated['notes'] ?? null;
        }

        $reservation = Reservation::create($data);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Reservasi kamar berhasil dibuat.');
    }

    public function show(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);
        $reservation->load(['user', 'room.roomType.facilities']);

        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);

        // Hanya reservasi yang belum selesai/dibatalkan yang bisa diubah
        if (!in_array($reservation->status, ['pending', 'confirmed'], true)) {
            return redirect()
                ->route('reservations.show', $reservation)
                ->with('error', 'Reservasi yang sudah check-in, selesai, atau dibatalkan tidak dapat diubah.');
        }

        // Ambil kamar yang tersedia + kamar yang sedang dipakai reservasi ini
        $rooms = Room::with(['roomType.facilities'])
            ->where(function ($q) use ($reservation) {
                $q->where('status', 'tersedia')
                  ->orWhere('id', $reservation->room_id);
            })
            ->orderBy('room_number')
            ->get();

        $guests = Auth::user()->hasAnyRole(['admin', 'resepsionis'])
            ? User::orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        return view('reservations.edit', compact('reservation', 'rooms', 'guests'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        Gate::authorize('view', $reservation);

        if (!in_array($reservation->status, ['pending', 'confirmed'], true)) {
            return redirect()
                ->route('reservations.show', $reservation)
                ->with('error', 'Reservasi yang sudah check-in, selesai, atau dibatalkan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'room_id.required' => 'Silakan pilih unit kamar terlebih dahulu.',
            'check_out_date.after' => 'Tanggal check-out harus minimal 1 hari setelah tanggal check-in.',
        ]);

        // Cek bentrok jadwal kamar (kecualikan ID reservasi yang sedang diedit)
        $isConflict = Reservation::where('room_id', $validated['room_id'])
            ->where('id', '!=', $reservation->id)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($validated) {
                $query->where('check_in_date', '<', $validated['check_out_date'])
                    ->where('check_out_date', '>', $validated['check_in_date']);
            })
            ->exists();

        if ($isConflict) {
            return back()
                ->withInput()
                ->withErrors([
                    'room_id' => 'Unit kamar ini sudah dipesan pada rentang tanggal tersebut. Silakan pilih tanggal atau kamar lain.',
                ]);
        }

        $room = Room::with('roomType')->findOrFail($validated['room_id']);
        $nights = Carbon::parse($validated['check_in_date'])
            ->diffInDays(Carbon::parse($validated['check_out_date']));
        $totalPrice = $nights * ($room->roomType->base_price ?? 0);

        $data = [
            'room_id' => $room->id,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
        ];

        if (Auth::user()->hasAnyRole(['admin', 'resepsionis']) && !empty($validated['user_id'])) {
            $data['user_id'] = $validated['user_id'];
        }

        if (Schema::hasColumn('reservations', 'total_nights')) {
            $data['total_nights'] = $nights;
        }

        if (Schema::hasColumn('reservations', 'total_price')) {
            $data['total_price'] = $totalPrice;
        }

        if (Schema::hasColumn('reservations', 'notes')) {
            $data['notes'] = $validated['notes'] ?? null;
        } elseif (Schema::hasColumn('reservations', 'special_requests')) {
            $data['special_requests'] = $validated['notes'] ?? null;
        }

        $reservation->update($data);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Perubahan reservasi ' . $reservation->booking_code . ' berhasil disimpan.');
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        Gate::authorize('update', $reservation);

        $data = $request->validate([
            'status' => ['required', 'in:confirmed,checked_in,checked_out,cancelled'],
        ]);

        DB::transaction(function () use ($reservation, $data) {
            $reservation->update(['status' => $data['status']]);

            if ($data['status'] === 'checked_in') {
                $reservation->room->update(['status' => 'terisi']);
            } elseif (in_array($data['status'], ['checked_out', 'cancelled'], true)) {
                $reservation->room->update(['status' => 'tersedia']);
            }
        });

        return back()->with('success', 'Status reservasi ' . $reservation->booking_code . ' diperbarui menjadi ' . strtoupper($data['status']) . '.');
    }

    public function cancel(Reservation $reservation)
    {
        Gate::authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => 'cancelled']);
            if ($reservation->room->status === 'terisi') {
                $reservation->room->update(['status' => 'tersedia']);
            }
        });

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function destroy(Reservation $reservation)
    {
        Gate::authorize('delete', $reservation);
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Data reservasi berhasil dihapus.');
    }
}