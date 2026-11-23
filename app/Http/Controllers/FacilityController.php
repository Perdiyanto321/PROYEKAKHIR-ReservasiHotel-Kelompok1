<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    /**
     * Daftar master fasilitas + jumlah tipe kamar yang memakainya.
     */
    public function index(Request $request)
    {
        $facilities = Facility::query()
            ->with('roomTypes:id,name') // eager load: dipakai untuk menampilkan nama tipe kamar
            ->withCount('roomTypes')
            ->when($request->filled('cari'), fn ($q) => $q->where('name', 'like', '%' . $request->cari . '%'))
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Facility::count(),
            'used' => Facility::whereHas('roomTypes')->count(),
        ];
        $stats['unused'] = $stats['total'] - $stats['used'];

        return view('facilities.index', compact('facilities', 'stats'));
    }

    /**
     * Tambah cepat fasilitas baru (hanya nama; ikon memakai default database).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:facilities,name'],
        ], [
            'name.unique' => 'Fasilitas dengan nama tersebut sudah ada.',
        ], [
            'name' => 'nama fasilitas',
        ]);

        $facility = Facility::create(['name' => trim($validated['name'])]);

        return redirect()->route('facilities.index')
            ->with('success', 'Fasilitas "' . $facility->name . '" berhasil ditambahkan.');
    }

    /**
     * Ubah nama fasilitas.
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', Rule::unique('facilities', 'name')->ignore($facility->id)],
        ], [
            'name.unique' => 'Fasilitas dengan nama tersebut sudah ada.',
        ], [
            'name' => 'nama fasilitas',
        ]);

        $facility->update(['name' => trim($validated['name'])]);

        return redirect()->route('facilities.index')
            ->with('success', 'Nama fasilitas berhasil diperbarui menjadi "' . $facility->name . '".');
    }

    /**
     * Hapus fasilitas. Ditolak bila masih dipakai tipe kamar (relasi aktif),
     * supaya katalog dan data tipe kamar tidak kehilangan fasilitas secara diam-diam.
     */
    public function destroy(Facility $facility)
    {
        $usedBy = $facility->roomTypes()->count();

        if ($usedBy > 0) {
            return back()->withErrors([
                'delete' => 'Fasilitas "' . $facility->name . '" tidak dapat dihapus karena masih dipakai oleh ' . $usedBy . ' tipe kamar. Lepaskan dulu dari tipe kamar terkait.',
            ]);
        }

        $name = $facility->name;
        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Fasilitas "' . $name . '" berhasil dihapus.');
    }
}
