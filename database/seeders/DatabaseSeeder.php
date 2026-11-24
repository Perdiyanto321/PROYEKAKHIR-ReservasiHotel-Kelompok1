<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan penting: role & akun → master hotel (fasilitas, tipe, kamar) → reservasi demo.
     * Jalankan `php artisan migrate:fresh --seed` untuk memulai dari data demo yang bersih.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            HotelSeeder::class,
            DemoReservationSeeder::class,
        ]);
    }
}
