<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('seeder menyiapkan akun demo dan data presentasi', function () {
    Storage::fake('public');
    $this->seed();

    foreach ([
        ['admin@svara.test', 'admin'],
        ['resepsionis@svara.test', 'resepsionis'],
        ['tamu@svara.test', 'tamu'],
    ] as [$email, $role]) {
        $user = User::where('email', $email)->first();

        expect($user)->not->toBeNull();
        expect($user->hasRole($role))->toBeTrue();
        expect(Hash::check('password', $user->password))->toBeTrue();
    }

    // Setiap status reservasi punya contoh data
    expect(Reservation::pluck('status')->unique()->sort()->values()->all())
        ->toEqual(['cancelled', 'checked_in', 'checked_out', 'confirmed', 'pending']);

    // Unit kamar tersebar di Lantai 1–3
    expect(Room::pluck('floor')->unique()->sort()->values()->all())->toEqual([1, 2, 3]);

    expect(RoomType::count())->toBe(3);
    expect(Facility::count())->toBeGreaterThanOrEqual(6);
});

test('seeder aman dijalankan dua kali tanpa menggandakan data', function () {
    Storage::fake('public');
    $this->seed();

    $counts = [User::count(), Facility::count(), RoomType::count(), Room::count(), Reservation::count()];

    $this->seed();

    expect([User::count(), Facility::count(), RoomType::count(), Room::count(), Reservation::count()])
        ->toEqual($counts);
});
