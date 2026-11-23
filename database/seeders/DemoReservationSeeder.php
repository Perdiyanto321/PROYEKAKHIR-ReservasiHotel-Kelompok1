<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Contoh reservasi untuk SETIAP status (pending, confirmed, checked_in, checked_out, cancelled)
 * agar dashboard, tabel reservasi, dan laporan langsung terisi data realistis.
 *
 * Semua tanggal relatif terhadap hari ini, sehingga data selalu terlihat "segar" saat presentasi.
 * Harga total = jumlah malam × tarif dasar tipe kamar (sama seperti perhitungan di aplikasi).
 */
class DemoReservationSeeder extends Seeder
{
    public function run(): void
    {
        $guests = [
            'raka'  => User::where('email', 'tamu@svara.test')->first(),
            'nadia' => User::where('email', 'tamu2@svara.test')->first(),
            'dewi'  => User::where('email', 'tamu3@svara.test')->first(),
            'budi'  => User::where('email', 'tamu4@svara.test')->first(),
            'maya'  => User::where('email', 'tamu5@svara.test')->first(),
        ];

        if (in_array(null, $guests, true)) {
            throw new \RuntimeException('Akun tamu demo belum ada. Jalankan RolePermissionSeeder lebih dulu (atau `php artisan migrate:fresh --seed`).');
        }

        $rooms = Room::with('roomType')->get()->keyBy('room_number');
        if ($rooms->isEmpty()) {
            throw new \RuntimeException('Data kamar belum ada. Jalankan HotelSeeder lebih dulu.');
        }

        $today = today();

        // [kamar, tamu, offset hari check-in dari hari ini, jumlah malam, status, permintaan khusus, [rating, komentar]]
        $rows = [
            // ----- checked_out (riwayat, jadi sumber pendapatan laporan) -----
            ['101', 'raka',  -44, 2, 'checked_out', null, [5, 'Kamar bersih dan sarapannya luar biasa. Pasti kembali lagi.']],
            ['201', 'nadia', -40, 3, 'checked_out', 'Kamar lantai tinggi', [4, 'Pemandangan taman indah, proses check-out cepat.']],
            ['301', 'dewi',  -38, 2, 'checked_out', 'Dekorasi ulang tahun', null],
            ['102', 'budi',  -33, 1, 'checked_out', null, null],
            ['202', 'maya',  -30, 4, 'checked_out', 'Extra bantal', null],
            ['103', 'raka',  -27, 2, 'checked_out', null, [5, 'Staf ramah, lokasi kamar tenang.']],
            ['302', 'nadia', -24, 3, 'checked_out', 'Late check-out jam 13.00', null],
            ['203', 'dewi',  -21, 2, 'checked_out', null, null],
            ['104', 'budi',  -18, 3, 'checked_out', null, null],
            ['101', 'maya',  -15, 2, 'checked_out', 'Check-in lebih awal', null],
            ['201', 'raka',  -12, 2, 'checked_out', null, null],
            ['303', 'dewi',   -9, 2, 'checked_out', 'Air mineral tambahan', null],
            ['102', 'nadia',  -7, 4, 'checked_out', null, null],
            ['202', 'budi',   -5, 2, 'checked_out', null, null],

            // ----- checked_in (sedang menginap; kamar berstatus "terisi") -----
            ['103', 'raka',   -1, 3, 'checked_in', 'Extra bantal 2 buah', null],
            ['301', 'nadia',  -2, 3, 'checked_in', 'Late check-out jam 13.00', null],
            ['204', 'dewi',   -3, 3, 'checked_in', null, null],        // check-out hari ini
            ['102', 'budi',   -1, 2, 'checked_in', null, null],

            // ----- confirmed (akan datang; satu tamu check-in hari ini) -----
            ['101', 'maya',    0, 2, 'confirmed', 'Kamar bebas asap rokok', null],
            ['202', 'raka',    2, 3, 'confirmed', null, null],
            ['302', 'dewi',    4, 2, 'confirmed', 'Antar-jemput bandara', null],
            ['203', 'nadia',   6, 4, 'confirmed', null, null],
            ['104', 'budi',    9, 2, 'confirmed', null, null],

            // ----- pending (menunggu konfirmasi staf) -----
            ['201', 'maya',    3, 2, 'pending', 'Tempat tidur tambahan', null],
            ['303', 'raka',    5, 3, 'pending', null, null],
            ['104', 'nadia',   1, 2, 'pending', null, null],

            // ----- cancelled -----
            ['101', 'dewi',    7, 2, 'cancelled', null, null],
            ['203', 'budi',  -10, 2, 'cancelled', null, null],
            ['301', 'maya',    8, 3, 'cancelled', 'Rencana perjalanan berubah', null],
        ];

        foreach ($rows as $i => [$roomNumber, $guestKey, $offset, $nights, $status, $request, $review]) {
            $n = $i + 1;
            $room = $rooms->get($roomNumber);
            $guest = $guests[$guestKey];

            $checkIn = $today->copy()->addDays($offset);
            $checkOut = $checkIn->copy()->addDays($nights);

            // Tanggal dibuatnya reservasi: sebelum check-in, tetapi tidak pernah di masa depan
            $createdAt = $checkIn->copy()->subDays(3)->min($today->copy()->subDay())->setTime(9 + ($n % 8), 15);

            $code = 'RSV-' . $createdAt->format('Ymd') . '-' . sprintf('%04d', $n);

            if (Reservation::where('booking_code', $code)->exists()) {
                continue; // seeder aman dijalankan ulang tanpa menggandakan data
            }

            $reservation = new Reservation([
                'booking_code' => $code,
                'room_id' => $room->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'total_nights' => $nights,
                'total_price' => $nights * (int) $room->roomType->base_price,
                'status' => $status,
                'special_requests' => $request,
            ]);
            // user_id & timestamp tidak mass-assignable; diisi langsung seperti di ReservationController
            $reservation->user_id = $guest->id;
            $reservation->created_at = $createdAt;
            $reservation->updated_at = $createdAt;
            $reservation->save();

            if ($review) {
                $item = new Review([
                    'reservation_id' => $reservation->id,
                    'rating' => $review[0],
                    'comment' => $review[1],
                ]);
                $item->user_id = $guest->id;
                $item->save();
            }
        }
    }
}
