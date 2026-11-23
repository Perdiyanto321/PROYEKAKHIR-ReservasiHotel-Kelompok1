<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rawCounts = Room::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            'all' => $rawCounts->sum(),
            'tersedia' => $rawCounts->get('tersedia', 0),
            'terisi' => $rawCounts->get('terisi', 0),
            'maintenance' => $rawCounts->get('maintenance', 0),
        ];

        $rooms = Room::with([
                'roomType',
                'reservations' => fn ($q) => $q
                    ->whereIn('status', ['checked_in', 'confirmed'])
                    ->with('user')
                    ->orderBy('check_in_date'),
            ])
            ->when($request->filled('cari'), fn ($q) => $q->where('room_number', 'like', '%' . $request->cari . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('room_type_id'), fn ($q) => $q->where('room_type_id', $request->room_type_id))
            ->when($request->filled('floor'), fn ($q) => $q->where('floor', $request->floor))
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        $roomTypes = RoomType::orderBy('base_price')->get();
        $floors = Room::select('floor')->distinct()->orderBy('floor')->pluck('floor');

        return view('rooms.index', compact('rooms', 'statusCounts', 'roomTypes', 'floors'));
    }

    public function create()
    {
        $roomTypes = RoomType::orderBy('base_price')->get();

        return view('rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:20', 'unique:rooms,room_number'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'floor' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['required', 'in:tersedia,terisi,maintenance'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'room_number' => $validated['room_number'],
            'room_type_id' => $validated['room_type_id'],
            'floor' => $validated['floor'],
            'status' => $validated['status'],
        ];

        if (Schema::hasColumn('rooms', 'notes')) {
            $payload['notes'] = $validated['notes'] ?? null;
        }

        $room = Room::create($payload);

        return redirect()->route('rooms.index')
            ->with('success', 'Unit Kamar ' . $room->room_number . ' berhasil ditambahkan.');
    }

    public function show(Room $room)
    {
        $room->load([
            'roomType.facilities',
            'reservations' => fn ($q) => $q->with('user')->latest()->take(12),
        ]);

        $activeReservation = $room->reservations->firstWhere('status', 'checked_in');
        $upcomingReservation = $room->reservations
            ->where('status', 'confirmed')
            ->sortBy('check_in_date')
            ->first();

        return view('rooms.show', compact('room', 'activeReservation', 'upcomingReservation'));
    }

    public function edit(Room $room)
    {
        $roomTypes = RoomType::orderBy('base_price')->get();

        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:20', Rule::unique('rooms', 'room_number')->ignore($room->id)],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'floor' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['required', 'in:tersedia,terisi,maintenance'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'room_number' => $validated['room_number'],
            'room_type_id' => $validated['room_type_id'],
            'floor' => $validated['floor'],
            'status' => $validated['status'],
        ];

        if (Schema::hasColumn('rooms', 'notes')) {
            $payload['notes'] = $validated['notes'] ?? $room->notes;
        }

        $room->update($payload);

        if ($request->boolean('quick_status')) {
            return back()->with('success', 'Status Kamar ' . $room->room_number . ' diperbarui menjadi ' . strtoupper($room->status) . '.');
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Data Kamar ' . $room->room_number . ' berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->status === 'terisi' || $room->reservations()->whereIn('status', ['pending', 'confirmed', 'checked_in'])->exists()) {
            return back()->withErrors([
                'delete' => 'Kamar ' . $room->room_number . ' tidak dapat dihapus karena sedang terisi atau memiliki jadwal reservasi aktif.',
            ]);
        }

        $roomNumber = $room->room_number;
        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Unit Kamar ' . $roomNumber . ' berhasil dihapus.');
    }
}