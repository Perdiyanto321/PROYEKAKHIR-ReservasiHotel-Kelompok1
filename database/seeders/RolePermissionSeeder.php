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

        // Akun Uji Tiap Role
        $admin = User::firstOrCreate(
            ['email' => 'admin@hotel.test'],
            ['name' => 'Operations Manager', 'phone' => '081234567890', 'password' => Hash::make('password')]
        );
        $admin->assignRole($adminRole);

        $resepsionis = User::firstOrCreate(
            ['email' => 'resepsionis@hotel.test'],
            ['name' => 'Front Desk Staff', 'phone' => '081234567891', 'password' => Hash::make('password')]
        );
        $resepsionis->assignRole($resepsionisRole);

        $tamu1 = User::firstOrCreate(
            ['email' => 'tamu@hotel.test'],
            ['name' => 'Raka Pratama', 'phone' => '081234567892', 'password' => Hash::make('password')]
        );
        $tamu1->assignRole($tamuRole);

        $tamu2 = User::firstOrCreate(
            ['email' => 'tamu2@hotel.test'],
            ['name' => 'Nadia Kusuma', 'phone' => '081234567893', 'password' => Hash::make('password')]
        );
        $tamu2->assignRole($tamuRole);
    }
}