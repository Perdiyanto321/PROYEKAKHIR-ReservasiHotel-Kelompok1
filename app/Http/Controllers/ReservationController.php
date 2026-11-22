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
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reservations = Reservation::with(['user', 'room.roomType'])
            ->when($user->hasRole('tamu'), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('cari'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('booking_code', 'like', '%' . $request->cari . '%')
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->cari . '%'));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $rooms = Room::tersedia()->with('roomType')->orderBy('room_number')->get();
        $guests = $request->user()->hasAnyRole(['admin', 'resepsionis'])
            ? User::role('tamu')->orderBy('name')->get()
            : collect();

        return view('reservations.create', compact('rooms', 'guests'));
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();
        $room = Room::with('roomType')->findOrFail($validated['room_id']);

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = max(1, $checkIn->diffInDays($checkOut));
        $totalPrice = $nights * $room->roomType->base_price;

        $isStaff = $request->user()->hasAnyRole(['admin', 'resepsionis']);
        $ownerId = ($isStaff && ! empty($validated['guest_id']))
            ? $validated['guest_id']
            : $request->user()->id;

        $reservation = new Reservation([
            'booking_code' => 'RSV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'room_id' => $room->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_nights' => $nights,
            'total_price' => $totalPrice,
            'status' => $isStaff ? 'confirmed' : 'pending',
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Modul 6: Tetapkan pemilik dari user terautentikasi, bukan dari input mass assignment
        $reservation->user_id = $ownerId;
        $reservation->save();

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservasi berhasil dibuat dengan kode ' . $reservation->booking_code);
    }

    public function show(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);
        $reservation->load(['user', 'room.roomType.facilities']);

        return view('reservations.show', compact('reservation'));
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

        return back()->with('success', 'Status reservasi berhasil diperbarui menjadi ' . strtoupper($data['status']) . '.');
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