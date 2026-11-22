<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $allowedSort = ['room_number', 'floor', 'status'];
        $sort = in_array($request->query('urut'), $allowedSort, true)
            ? $request->query('urut')
            : 'room_number';

        $rooms = Room::with('roomType')
            ->when($request->filled('cari'), fn ($q) => $q->where('room_number', 'like', '%' . $request->cari . '%'))
            ->when($request->filled('tipe'), fn ($q) => $q->where('room_type_id', $request->tipe))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy($sort)
            ->paginate(10)
            ->withQueryString();

        $roomTypes = RoomType::orderBy('name')->get();

        return view('rooms.index', compact('rooms', 'roomTypes'));
    }

    public function create()
    {
        return view('rooms.create', [
            'room' => new Room(['status' => 'tersedia', 'floor' => 1]),
            'roomTypes' => RoomType::orderBy('name')->get(),
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        Room::create($request->validated());

        return redirect()->route('rooms.index')
            ->with('success', 'Unit kamar baru berhasil ditambahkan.');
    }

    public function show(Room $room)
    {
        $room->load(['roomType.facilities', 'reservations.user']);
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', [
            'room' => $room,
            'roomTypes' => RoomType::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $room->update($request->validated());

        return redirect()->route('rooms.index')
            ->with('success', 'Data unit kamar berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Unit kamar berhasil dihapus.');
    }
}