<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::with('facilities')
            ->withCount('rooms')
            ->orderBy('base_price')
            ->get();

        return view('room-types.index', compact('roomTypes'));
    }

    public function create()
    {
        $facilities = Facility::orderBy('name')->get();

        return view('room-types.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:room_types,name'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('room-types', 'public');
        }

        $roomType = RoomType::create([
            'name' => $validated['name'],
            'base_price' => $validated['base_price'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        $roomType->facilities()->sync($validated['facilities'] ?? []);

        return redirect()->route('room-types.index')
            ->with('success', 'Tipe kamar "' . $roomType->name . '" berhasil ditambahkan.');
    }

    public function show(RoomType $roomType)
    {
        return redirect()->route('room-types.edit', $roomType);
    }

    public function edit(RoomType $roomType)
    {
        $roomType->load('facilities');
        $facilities = Facility::orderBy('name')->get();

        return view('room-types.edit', compact('roomType', 'facilities'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('room_types', 'name')->ignore($roomType->id)],
            'base_price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
        ]);

        $imagePath = $roomType->image;

        if ($request->boolean('remove_image') && $imagePath) {
            if (! Str::startsWith($imagePath, ['http://', 'https://'])) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($imagePath && ! Str::startsWith($imagePath, ['http://', 'https://'])) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('room-types', 'public');
        }

        $roomType->update([
            'name' => $validated['name'],
            'base_price' => $validated['base_price'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        $roomType->facilities()->sync($validated['facilities'] ?? []);

        return redirect()->route('room-types.index')
            ->with('success', 'Perubahan tipe kamar "' . $roomType->name . '" berhasil disimpan.');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return back()->withErrors([
                'delete' => 'Tipe kamar "' . $roomType->name . '" tidak dapat dihapus karena masih memiliki unit kamar terdaftar.',
            ]);
        }

        if ($roomType->image && ! Str::startsWith($roomType->image, ['http://', 'https://'])) {
            Storage::disk('public')->delete($roomType->image);
        }

        $roomType->facilities()->detach();
        $roomType->delete();

        return redirect()->route('room-types.index')
            ->with('success', 'Tipe kamar berhasil dihapus.');
    }
}