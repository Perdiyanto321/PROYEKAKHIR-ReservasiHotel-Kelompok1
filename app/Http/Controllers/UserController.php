<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Role yang dikenal aplikasi. */
    private const ROLES = ['admin', 'resepsionis', 'tamu'];

    /** Role yang boleh dipilih saat membuat akun staf baru. */
    private const STAFF_ROLES = ['admin', 'resepsionis'];

    public function index(Request $request)
    {
        $filterRole = in_array($request->query('role'), [...self::ROLES, 'none'], true)
            ? $request->query('role')
            : null;

        $users = User::query()
            ->with('roles:id,name')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = '%' . $request->cari . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($filterRole === 'none', fn ($q) => $q->doesntHave('roles'))
            ->when($filterRole && $filterRole !== 'none', fn ($q) => $q->role($filterRole))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Hitung jumlah akun per role untuk tab & kartu ringkasan
        $roleCounts = Role::withCount('users')->pluck('users_count', 'name');
        $counts = [
            'all' => User::count(),
            'admin' => (int) $roleCounts->get('admin', 0),
            'resepsionis' => (int) $roleCounts->get('resepsionis', 0),
            'tamu' => (int) $roleCounts->get('tamu', 0),
            'none' => User::doesntHave('roles')->count(),
        ];

        return view('users.index', compact('users', 'counts', 'filterRole'));
    }

    /**
     * Buat akun staf baru (admin / resepsionis).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'role' => 'role',
            'password' => 'kata sandi',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Kolom password otomatis di-hash oleh cast 'hashed' pada model User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
            ]);
            $user->assignRole($validated['role']);

            return $user;
        });

        return redirect()->route('users.index')
            ->with('success', 'Akun ' . $validated['role'] . ' "' . $user->name . '" berhasil dibuat.');
    }

    /**
     * Ubah role pengguna.
     * Pengaman: admin tidak boleh mengubah role-nya sendiri (mencegah sistem kehilangan admin).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors([
                'role' => 'Anda tidak dapat mengubah role akun Anda sendiri.',
            ]);
        }

        $user->syncRoles([$validated['role']]);

        // back(): tetap di halaman, filter pencarian, dan nomor halaman yang sama
        return back()->with('success', 'Role "' . $user->name . '" berhasil diubah menjadi ' . $validated['role'] . '.');
    }
}
