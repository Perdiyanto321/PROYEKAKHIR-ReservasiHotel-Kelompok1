<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function reportTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function reportTestReservation(Room $room, User $guest, string $code, string $status, string $checkIn, int $nights, int $price): Reservation
{
    $rsv = new Reservation([
        'booking_code' => $code,
        'room_id' => $room->id,
        'check_in_date' => $checkIn,
        'check_out_date' => date('Y-m-d', strtotime($checkIn . " +{$nights} days")),
        'total_nights' => $nights,
        'total_price' => $price,
        'status' => $status,
    ]);
    $rsv->user_id = $guest->id;
    $rsv->save();

    return $rsv;
}

function reportTestRoom(): Room
{
    $type = RoomType::unguarded(fn () => RoomType::create([
        'name' => 'Tipe Laporan',
        'slug' => 'tipe-laporan',
        'base_price' => 500000,
        'capacity' => 2,
    ]));

    return Room::create(['room_type_id' => $type->id, 'room_number' => '901', 'floor' => 9, 'status' => 'tersedia']);
}

test('tamu tidak boleh membuka laporan, staf boleh', function () {
    $this->actingAs(reportTestUser('tamu'))->get(route('reports.index'))->assertForbidden();
    $this->actingAs(reportTestUser('tamu'))->get(route('reports.export'))->assertForbidden();
    $this->actingAs(reportTestUser('resepsionis'))->get(route('reports.index'))->assertOk();
    $this->actingAs(reportTestUser('admin'))->get(route('reports.index'))->assertOk();
});

test('kpi hanya menghitung reservasi checked_in dan checked_out dalam rentang tanggal', function () {
    $room = reportTestRoom();
    $guest = reportTestUser('tamu');

    reportTestReservation($room, $guest, 'RPT-0001', 'checked_out', '2026-06-10', 2, 1000000);
    reportTestReservation($room, $guest, 'RPT-0002', 'checked_in', '2026-06-12', 3, 1500000);
    reportTestReservation($room, $guest, 'RPT-0003', 'pending', '2026-06-14', 1, 500000);       // tidak dihitung
    reportTestReservation($room, $guest, 'RPT-0004', 'cancelled', '2026-06-15', 1, 500000);     // tidak dihitung
    reportTestReservation($room, $guest, 'RPT-0005', 'checked_out', '2026-05-01', 2, 9000000);  // di luar rentang

    $response = $this->actingAs(reportTestUser('admin'))
        ->get(route('reports.index', ['start_date' => '2026-06-01', 'end_date' => '2026-06-30']))
        ->assertOk();

    $response->assertSee('Rp 2.500.000');   // 1.000.000 + 1.500.000
    $response->assertSee('5 Malam');        // 2 + 3
    $response->assertSee('2 Reservasi');
    $response->assertDontSee('RPT-0005');
});

test('filter status mempersempit tabel rincian', function () {
    $room = reportTestRoom();
    $guest = reportTestUser('tamu');

    reportTestReservation($room, $guest, 'RPT-1001', 'pending', '2026-06-14', 1, 500000);
    reportTestReservation($room, $guest, 'RPT-1002', 'checked_out', '2026-06-10', 2, 1000000);

    $this->actingAs(reportTestUser('admin'))
        ->get(route('reports.index', ['start_date' => '2026-06-01', 'end_date' => '2026-06-30', 'status' => 'pending']))
        ->assertOk()
        ->assertSee('RPT-1001')
        ->assertDontSee('RPT-1002');
});

test('tanggal akhir sebelum tanggal mulai ditolak', function () {
    $this->actingAs(reportTestUser('admin'))
        ->get(route('reports.index', ['start_date' => '2026-06-30', 'end_date' => '2026-06-01']))
        ->assertSessionHasErrors('end_date');
});

test('export csv mengunduh baris sesuai filter', function () {
    $room = reportTestRoom();
    $guest = reportTestUser('tamu');

    reportTestReservation($room, $guest, 'RPT-2001', 'checked_out', '2026-06-10', 2, 1000000);
    reportTestReservation($room, $guest, 'RPT-2002', 'cancelled', '2026-06-11', 1, 500000);

    $response = $this->actingAs(reportTestUser('admin'))
        ->get(route('reports.export', ['start_date' => '2026-06-01', 'end_date' => '2026-06-30', 'status' => 'checked_out']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');

    $csv = $response->streamedContent();
    expect($csv)->toContain('Kode Booking')
        ->toContain('RPT-2001')
        ->not->toContain('RPT-2002');
});
