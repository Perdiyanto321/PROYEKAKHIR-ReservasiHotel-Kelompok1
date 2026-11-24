<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'kelola-master-kamar',
            'kelola-pengguna',
            'operasional-frontdesk',
            'buat-reservasi',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        $resepsionisRole = Role::firstOrCreate(['name' => 'resepsionis', 'guard_name' => 'web']);
        $resepsionisRole->syncPermissions(['operasional-frontdesk', 'buat-reservasi']);

        $tamuRole = Role::firstOrCreate(['name' => 'tamu', 'guard_name' => 'web']);
        $tamuRole->syncPermissions(['buat-reservasi']);

        // Akun demo siap presentasi (password semuanya: password)
        $accounts = [
            ['admin@svara.test',       'Operations Manager', '081234567890', $adminRole],
            ['resepsionis@svara.test', 'Front Desk Staff',   '081234567891', $resepsionisRole],
            ['tamu@svara.test',        'Raka Pratama',       '081234567892', $tamuRole],
            ['tamu2@svara.test',       'Nadia Kusuma',       '081234567893', $tamuRole],
            ['tamu3@svara.test',       'Dewi Anggraini',     '081234567894', $tamuRole],
            ['tamu4@svara.test',       'Budi Santoso',       '081234567895', $tamuRole],
            ['tamu5@svara.test',       'Maya Lestari',       '081234567896', $tamuRole],
        ];

        foreach ($accounts as [$email, $name, $phone, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'password' => Hash::make('password')]
            );
            $user->syncRoles([$role]);
        }
    }
}
