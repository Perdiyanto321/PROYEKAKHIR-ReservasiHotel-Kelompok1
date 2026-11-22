<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomTypeRequest;
use App\Http\Requests\UpdateRoomTypeRequest;
use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index(Request $request)
    {
        $roomTypes = RoomType::with('facilities')
            ->withCount('rooms')
            ->when($request->filled('cari'), fn ($q) => $q->where('name', 'like', '%' . $request->cari . '%'))
            ->orderBy('base_price')
            ->paginate(10)
            ->withQueryString();

        return view('room-types.index', compact('roomTypes'));
    }

    public function create()
    {
        return view('room-types.create', [
            'roomType' => new RoomType(),
            'facilities' => Facility::orderBy('name')->get(),
            'selectedFacilities' => [],
        ]);
    }

    public function store(StoreRoomTypeRequest $request)
    {
        $validated = $request->validated();
        $roomType = RoomType::create($validated);
        $roomType->facilities()->sync($validated['facilities'] ?? []);

        return redirect()->route('room-types.index')
            ->with('success', 'Tipe kamar baru berhasil ditambahkan.');
    }

    public function show(RoomType $roomType)
    {
        $roomType->load(['facilities', 'rooms']);
        return view('room-types.show', compact('roomType'));
    }

    public function edit(RoomType $roomType)
    {
        $roomType->load('facilities');
        return view('room-types.edit', [
            'roomType' => $roomType,
            'facilities' => Facility::orderBy('name')->get(),
            'selectedFacilities' => $roomType->facilities->pluck('id')->toArray(),
        ]);
    }

    public function update(UpdateRoomTypeRequest $request, RoomType $roomType)
    {
        $validated = $request->validated();
        $roomType->update($validated);
        $roomType->facilities()->sync($validated['facilities'] ?? []);

        return redirect()->route('room-types.index')
            ->with('success', 'Tipe kamar berhasil diperbarui.');
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();

        return redirect()->route('room-types.index')
            ->with('success', 'Tipe kamar berhasil dihapus.');
    }
}